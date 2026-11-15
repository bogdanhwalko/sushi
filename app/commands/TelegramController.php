<?php

namespace app\commands;

use app\components\OrderTelegram;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Telegram-боти замовлень.
 */
class TelegramController extends Controller
{
    /**
     * Підключає вебхук до всіх ботів (основний + боти груп), щоб у групах працювала кнопка «Прийняте».
     * Запускається з deploy.sh (якщо задано SITE_URL) або вручну:
     *     php app/yii telegram/webhook https://ваш-домен
     *
     * @param string $siteUrl адреса сайту з https://
     * @param bool $force перезаписати чужий вебхук, якщо бот уже підключений до іншої програми
     */
    public function actionWebhook(string $siteUrl, bool $force = false): int
    {
        $failed = false;
        foreach (OrderTelegram::setupWebhooks($siteUrl, $force) as $row) {
            $this->stdout(($row['ok'] ? '✓ ' : '✗ ') . $row['bot'] . ': ' . $row['message'] . "\n");
            $failed = $failed || !$row['ok'];
        }

        return $failed ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
    }
}
