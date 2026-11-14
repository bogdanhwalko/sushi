<?php

/**
 * Зразок налаштувань БД для сервера.
 * Скопіюйте у db-local.php (поруч) і вкажіть реальні дані:
 *     cp app/config/db-local.example.php app/config/db-local.php
 */

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=sushi',
    'username' => 'sushi_user',
    'password' => 'СЮДИ_ПАРОЛЬ',
    'charset' => 'utf8mb4',

    // кеш схеми БД — менше запитів на проді
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
];
