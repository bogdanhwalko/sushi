<?php

/**
 * Зразок перевизначень конфігурації для сервера (необов'язково).
 * Скопіюйте у web-local.php — він зливається поверх config/web.php:
 *     cp app/config/web-local.example.php app/config/web-local.php
 *
 * Сюди варто винести секрети, щоб вони не лежали в репозиторії.
 */

return [
    'components' => [
        'request' => [
            // згенерувати: php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
            'cookieValidationKey' => 'СЮДИ_ВИПАДКОВИЙ_РЯДОК',
        ],
        'ts' => [
            'token' => 'СЮДИ_ТОКЕН_БОТА',
            'chatId' => 'СЮДИ_ID_ЧАТУ',
        ],
    ],
];
