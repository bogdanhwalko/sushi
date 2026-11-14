<?php

namespace app\forms;

use app\components\RateLimiter;
use app\models\CartItems;
use app\models\Cities;
use app\models\OrderItems;
use app\models\Orders;
use app\validators\PhoneValidator;
use Yii;
use yii\base\Model;
use yii\helpers\Html;

/**
 * Оформлення замовлення з поточного кошика:
 * зберігає замовлення в БД, очищає кошик і надсилає сповіщення в Telegram.
 */
class OrderForm extends Model
{
    /** Скільки замовлень дозволено з однієї IP за RATE_WINDOW секунд (захист від спаму в Telegram) */
    private const RATE_LIMIT = 3;

    private const RATE_WINDOW = 10 * 60;

    public $name;
    public $phone;
    public $city;


    public function rules(): array
    {
        return [
            ['name', 'trim'],
            [['name', 'phone', 'city'], 'required'],
            ['name', 'string', 'min' => 2, 'max' => 150],
            ['phone', 'filter', 'filter' => [PhoneValidator::class, 'normalize']],
            ['phone', PhoneValidator::class, 'message' => 'Вкажіть коректний номер телефону.'],
            ['city', 'integer'],
            ['city', 'exist', 'targetClass' => Cities::class, 'targetAttribute' => ['city' => 'id'],
                'filter' => ['status' => 1], 'message' => 'Оберіть місто зі списку.'],
        ];
    }


    public function attributeLabels(): array
    {
        return [
            'name' => "Ім'я",
            'phone' => 'Телефон',
            'city' => 'Місто',
        ];
    }


    /**
     * @return Orders|null збережене замовлення або null (помилки — у getErrors())
     */
    public function place(): ?Orders
    {
        if (!$this->validate()) {
            return null;
        }

        if (RateLimiter::tooMany('order', self::RATE_LIMIT, self::RATE_WINDOW)) {
            $this->addError('cart', 'Забагато замовлень за короткий час. Спробуйте за кілька хвилин або зателефонуйте нам.');
            return null;
        }

        /** @var CartItems[] $cartItems */
        $cartItems = CartItems::findCurrent()
            ->with('product')
            ->all();

        // товари, які встигли вимкнути чи видалити, не потрапляють у замовлення
        $cartItems = array_filter($cartItems, fn(CartItems $item) => $item->product !== null && (int)$item->product->status === 1);

        if (empty($cartItems)) {
            $this->addError('cart', 'Кошик порожній.');
            return null;
        }

        $city = Cities::findOne((int)$this->city);

        $order = new Orders([
            'user_id' => Yii::$app->user->isGuest ? null : Yii::$app->user->id,
            'city_id' => $city->id,
            'city_name' => $city->name,
            'customer_name' => $this->name,
            'phone' => $this->phone,
            'status' => Orders::STATUS_NEW,
            'total' => 0,
        ]);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $order->save(false);

            $total = 0;
            $items = [];
            foreach ($cartItems as $cartItem) {
                $item = new OrderItems([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product->id,
                    'product_name' => $cartItem->product->name,
                    'price' => (int)$cartItem->product->price,
                    'qty' => (int)$cartItem->qty,
                ]);
                $item->save(false);

                $items[] = $item;
                $total += $item->getTotal();
            }

            $order->updateAttributes(['total' => $total]);
            CartItems::clearCurrent();

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e, __METHOD__);
            $this->addError('cart', 'Не вдалося зберегти замовлення. Спробуйте ще раз.');
            return null;
        }

        $order->populateRelation('items', $items);
        $order->populateRelation('city', $city);

        RateLimiter::hit('order', self::RATE_WINDOW);
        $this->notifyTelegram($order);

        return $order;
    }


    /**
     * Замовлення вже збережене в БД, тож збій Telegram його не губить —
     * воно лишається в адмін-панелі з позначкою "не надіслано".
     */
    private function notifyTelegram(Orders $order): void
    {
        $city = $order->city;
        $group = $city?->telegramGroup;

        $message = "\xf0\x9f\x93\x9d Замовлення <b>№{$order->id}</b>\n";
        $message .= "\xe2\x98\x8e <b>+" . Html::encode($order->phone) . '</b> (' . Html::encode($order->customer_name) . ')';
        if ($order->user_id) {
            $message .= "\n\xf0\x9f\x91\xa4 Зареєстрований клієнт";
        }
        if ($city !== null && empty($group->telegram_id)) {
            $message .= "\n \xf0\x9f\x97\xba <b>" . Html::encode($city->name) . '</b>';
        }
        $message .= "\n\n";

        foreach ($order->items as $item) {
            $message .= "\xf0\x9f\x8d\xa3 " . Html::encode($item->product_name) . " {$item->qty}шт. <b>{$item->price}</b>₴ \n";
        }

        $message .= "\n \xf0\x9f\x92\xb3 Загальна сума замовлення: <b>{$order->total}</b>₴";

        try {
            $sender = Yii::$app->ts;
            if (!empty($group->bot_id)) {
                $sender->changeToken($group->bot_id);
            }

            if ($sender->sendMessage($message, $group->telegram_id ?? null)) {
                $order->updateAttributes(['telegram_sent' => 1]);
            } else {
                Yii::warning('Telegram: замовлення №' . $order->id . ' не надіслано: ' . json_encode($sender->getLastResponse()), __METHOD__);
            }
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
        }
    }
}
