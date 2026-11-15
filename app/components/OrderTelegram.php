<?php

namespace app\components;

use app\models\Orders;
use app\models\TelegramGroups;
use Yii;
use yii\helpers\Html;

/**
 * Замовлення в Telegram-групах: надсилання, статус у повідомленні, кнопка «Прийняте».
 *
 *  - send()  — нове замовлення йде в групу міста (або в основний чат) зі статусом і кнопкою «Прийняте»;
 *              запам'ятовуємо чат і ID повідомлення.
 *  - sync()  — після зміни статусу (в адмінці чи кнопкою) повідомлення переписується:
 *              новий статус, а кнопка лишається лише в нових замовлень.
 *  - handleUpdate() — натискання кнопки: Telegram надсилає його на вебхук сайту
 *              (TelegramController::actionWebhook), замовлення стає «Прийняте».
 *
 * Вебхук налаштовується для кожного бота окремо (основний з config + боти груп):
 * адмінка → «Групи Telegram» → «Підключити кнопки», або деплой (php app/yii telegram/webhook <адреса сайту>).
 * Адреса й секрет вебхука виводяться з токена бота, тож окремих налаштувань не потрібно.
 */
final class OrderTelegram
{
    /** callback_data кнопки: "accept:<id замовлення>" */
    private const ACCEPT = 'accept';

    /** шлях вебхука; повна адреса — webhookUrl() */
    public const WEBHOOK_PATH = 'telegram/webhook/';

    private const STATUS_ICONS = [
        Orders::STATUS_NEW => "\u{1F195}",       // 🆕
        Orders::STATUS_ACCEPTED => "\u{2705}",   // ✅
        Orders::STATUS_DONE => "\u{1F3C1}",      // 🏁
        Orders::STATUS_CANCELED => "\u{274C}",   // ❌
    ];


    /* ===================== Повідомлення ===================== */

    /** Надсилає нове замовлення. Збій Telegram замовлення не губить — воно лишається «не надіслано» в адмінці. */
    public static function send(Orders $order): bool
    {
        $group = $order->city?->telegramGroup;
        $chatId = $group->telegram_id ?? Yii::$app->ts->chatId;
        $sender = self::sender($group);

        try {
            if (!$sender->sendMessage(self::text($order, $group), $chatId, self::keyboard($order))) {
                Yii::warning('Telegram: замовлення №' . $order->id . ' не надіслано: ' . json_encode($sender->getLastResponse()), __METHOD__);
                return false;
            }
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            return false;
        }

        $order->updateAttributes([
            'telegram_sent' => 1,
            'telegram_chat_id' => (string)$chatId,
            'telegram_message_id' => $sender->getLastResponse()->result->message_id ?? null,
            'telegram_group_id' => $group->id ?? null,
        ]);

        return true;
    }


    /**
     * Переписує вже надіслане повідомлення під поточний статус замовлення.
     *
     * @return bool|null null — повідомлення немає (не надсилалось), false — Telegram не прийняв зміну
     */
    public static function sync(Orders $order, ?TelegramSender $sender = null): ?bool
    {
        if (empty($order->telegram_chat_id) || empty($order->telegram_message_id)) {
            return null;
        }

        $group = $order->telegram_group_id ? TelegramGroups::findOne($order->telegram_group_id) : null;
        $sender ??= self::sender($group);

        try {
            if ($sender->updateMessage(self::text($order, $group), $order->telegram_message_id, $order->telegram_chat_id, self::keyboard($order))) {
                return true;
            }
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            return false;
        }

        // той самий текст і кнопки (наприклад, повторне натискання) — це не помилка
        $error = (string)($sender->getLastResponse()->description ?? '');
        if (str_contains($error, 'message is not modified')) {
            return true;
        }

        Yii::warning('Telegram: не вдалося оновити повідомлення замовлення №' . $order->id . ': ' . $error, __METHOD__);

        return false;
    }


    /** Текст повідомлення (HTML-розмітка Telegram) */
    public static function text(Orders $order, ?TelegramGroups $group): string
    {
        $status = (int)$order->status;

        $text = "\u{1F4DD} Замовлення <b>№{$order->id}</b>\n";
        $text .= (self::STATUS_ICONS[$status] ?? "\u{2022}") . ' Статус: <b>' . Html::encode($order->getStatusLabel()) . '</b>';
        if ($status === Orders::STATUS_ACCEPTED && $order->accepted_by) {
            $text .= ' — ' . Html::encode($order->accepted_by);
            if ($order->accepted_at) {
                $text .= ', ' . date('H:i', strtotime($order->accepted_at));
            }
        }
        $text .= "\n\n\u{260E} <b>+" . Html::encode($order->phone) . '</b> (' . Html::encode($order->customer_name) . ')';
        if ($order->user_id) {
            $text .= "\n\u{1F464} Зареєстрований клієнт";
        }
        // в основний чат приходять замовлення з різних міст — показуємо місто
        if ($order->city_name && empty($group->telegram_id)) {
            $text .= "\n\u{1F5FA} <b>" . Html::encode($order->city_name) . '</b>';
        }
        $text .= "\n\n";

        foreach ($order->items as $item) {
            $text .= "\u{1F363} " . Html::encode($item->product_name) . " {$item->qty}шт. <b>{$item->price}</b>₴\n";
        }

        $text .= "\n\u{1F4B3} Загальна сума замовлення: <b>{$order->total}</b>₴";

        return $text;
    }


    /** Кнопка «Прийняте» — лише в нових замовлень; порожня клавіатура прибирає кнопку з повідомлення */
    public static function keyboard(Orders $order): array
    {
        if ((int)$order->status !== Orders::STATUS_NEW) {
            return ['inline_keyboard' => []];
        }

        return ['inline_keyboard' => [[
            ['text' => "\u{2705} Прийняте", 'callback_data' => self::ACCEPT . ':' . $order->id],
        ]]];
    }


    /* ===================== Натискання кнопки (вебхук) ===================== */

    /**
     * Обробляє оновлення від Telegram, що прийшло на вебхук бота з токеном $token.
     * Приймаємо лише натискання нашої кнопки під тим самим повідомленням, яке надіслав сайт.
     */
    public static function handleUpdate(array $update, string $token): void
    {
        $callback = $update['callback_query'] ?? null;
        if (!is_array($callback) || empty($callback['id'])) {
            return;
        }

        $sender = new TelegramSender(['token' => $token, 'sending' => Yii::$app->ts->sending]);
        $answer = static fn (string $text) => $sender->call('answerCallbackQuery', [
            'callback_query_id' => $callback['id'],
            'text' => $text,
        ]);

        if (!preg_match('/^' . self::ACCEPT . ':(\d+)$/', (string)($callback['data'] ?? ''), $m)) {
            $answer('Невідома дія.');
            return;
        }

        $order = Orders::findOne((int)$m[1]);
        $chatId = (string)($callback['message']['chat']['id'] ?? '');
        $messageId = (int)($callback['message']['message_id'] ?? 0);

        // кнопка має бути саме під повідомленням цього замовлення (інакше — підробка чи чужий чат)
        if ($order === null || $order->telegram_chat_id !== $chatId || (int)$order->telegram_message_id !== $messageId) {
            $answer('Замовлення не знайдено.');
            return;
        }

        $from = $callback['from'] ?? [];
        $who = trim(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? ''));
        if ($who === '') {
            $who = !empty($from['username']) ? '@' . $from['username'] : 'Telegram';
        }

        // лише з «Нове»: двоє натиснули одночасно — прийме перший, другий побачить актуальний статус
        $accepted = false;
        if ((int)$order->status === Orders::STATUS_NEW) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $locked = Orders::findBySql('SELECT * FROM ' . Orders::tableName() . ' WHERE id = :id FOR UPDATE', [':id' => $order->id])->one();
                if ($locked !== null && (int)$locked->status === Orders::STATUS_NEW) {
                    $locked->changeStatus(Orders::STATUS_ACCEPTED, $who);
                    $order = $locked;
                    $accepted = true;
                }
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                Yii::error($e, __METHOD__);
                $answer('Не вдалося змінити статус. Спробуйте ще раз або змініть в адмінці.');
                return;
            }
        }

        $order->refresh();
        self::sync($order, $sender);

        if ($accepted) {
            Yii::info("Замовлення №{$order->id} прийнято в Telegram: $who", __METHOD__);
            $answer("Замовлення №{$order->id} прийнято.");
        } else {
            $answer("Замовлення №{$order->id} вже має статус «{$order->getStatusLabel()}».");
        }
    }


    /* ===================== Боти й вебхуки ===================== */

    /** Відправник від імені бота групи (якщо в групи свій бот) або основного бота з config */
    public static function sender(?TelegramGroups $group): TelegramSender
    {
        return new TelegramSender([
            'token' => !empty($group->bot_id) ? $group->bot_id : Yii::$app->ts->token,
            'chatId' => Yii::$app->ts->chatId,
            'sending' => Yii::$app->ts->sending,
        ]);
    }


    /** @return string[] токени всіх ботів, якими надсилаються замовлення */
    public static function botTokens(): array
    {
        $tokens = array_merge(
            [(string)Yii::$app->ts->token],
            TelegramGroups::find()->select('bot_id')->where(['not', ['bot_id' => null]])->andWhere(['<>', 'bot_id', ''])->column()
        );

        return array_values(array_unique(array_filter(array_map('trim', $tokens))));
    }


    /** Частина адреси вебхука, за якою впізнаємо бота (сам токен в адресі не світимо) */
    public static function webhookKey(string $token): string
    {
        return substr(hash('sha256', 'order-webhook-url|' . $token), 0, 24);
    }


    /** Секрет, який Telegram додає до кожного запиту (заголовок X-Telegram-Bot-Api-Secret-Token) */
    public static function webhookSecret(string $token): string
    {
        return substr(hash('sha256', 'order-webhook-secret|' . $token), 0, 48);
    }


    public static function tokenByKey(string $key): ?string
    {
        foreach (self::botTokens() as $token) {
            if (hash_equals(self::webhookKey($token), $key)) {
                return $token;
            }
        }

        return null;
    }


    public static function webhookUrl(string $siteUrl, string $token): string
    {
        return rtrim($siteUrl, '/') . '/' . self::WEBHOOK_PATH . self::webhookKey($token);
    }


    /**
     * Підключає вебхук до кожного бота, щоб кнопка «Прийняте» працювала.
     * Чужий вебхук (інша програма на тому самому боті) без $force не перезаписуємо.
     *
     * @param string $siteUrl адреса сайту з https://
     * @return array<int, array{bot: string, ok: bool, message: string}>
     */
    public static function setupWebhooks(string $siteUrl, bool $force = false): array
    {
        if (!str_starts_with(strtolower($siteUrl), 'https://')) {
            return [['bot' => '—', 'ok' => false, 'message' => 'Telegram приймає лише адреси з https:// (зараз: ' . $siteUrl . ').']];
        }

        $results = [];
        foreach (self::botTokens() as $token) {
            $bot = 'бот ' . strstr($token . ':', ':', true);
            $sender = new TelegramSender(['token' => $token]);
            $url = self::webhookUrl($siteUrl, $token);

            if (!$sender->call('getWebhookInfo')) {
                $results[] = ['bot' => $bot, 'ok' => false, 'message' => 'Telegram не відповів або токен недійсний: ' . ($sender->getLastResponse()->description ?? 'немає відповіді')];
                continue;
            }

            $current = (string)($sender->getLastResponse()->result->url ?? '');
            if ($current !== '' && !str_contains($current, '/' . self::WEBHOOK_PATH) && !$force) {
                $results[] = ['bot' => $bot, 'ok' => false, 'message' => 'У бота вже є інший вебхук (' . parse_url($current, PHP_URL_HOST) . ') — не змінюю, щоб не зламати іншу програму.'];
                continue;
            }

            $ok = $sender->call('setWebhook', [
                'url' => $url,
                'secret_token' => self::webhookSecret($token),
                'allowed_updates' => ['callback_query'],
                'max_connections' => 10,
            ]);
            $results[] = [
                'bot' => $bot,
                'ok' => $ok,
                'message' => $ok
                    ? ($current === $url ? 'вже підключено' : 'підключено')
                    : 'помилка: ' . ($sender->getLastResponse()->description ?? 'немає відповіді'),
            ];
        }

        return $results;
    }
}
