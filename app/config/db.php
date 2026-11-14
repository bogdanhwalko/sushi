<?php

/**
 * Підключення до БД.
 *
 * Значення нижче — для локального Docker (docker-compose.yml).
 * На сервері створіть app/config/db-local.php (він у .gitignore) за зразком
 * db-local.example.php — тоді git pull не конфліктуватиме з налаштуваннями сервера.
 */

$local = __DIR__ . '/db-local.php';
if (is_file($local)) {
    return require $local;
}

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=mariadb-s;dbname=sushi',
    'username' => 'user',
    'password' => 'nopass',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
