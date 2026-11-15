<?php

/**
 * Основний Telegram-бот (компонент 'ts'): замовлення й заявки, якщо в міста немає своєї групи.
 * Спільний для сайту (web.php) і консолі (console.php — підключення кнопок: php app/yii telegram/webhook).
 */
return [
    'class' => \app\components\TelegramSender::class,
    'token' => '8561039078:AAGfsid4_ACKpiCLMe5z86IzAubQUPEbelI',
    'chatId' => '-5169754991',
    'sending' => true
];
