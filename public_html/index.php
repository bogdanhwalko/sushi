<?php

// Режим обслуговування: прапорець ставить deploy.sh на час оновлення.
if (is_file(__DIR__ . '/../app/runtime/maintenance.flag')) {
    http_response_code(503);
    header('Retry-After: 60');
    header('Cache-Control: no-store');
    header('Content-Type: text/html; charset=utf-8');
    readfile(__DIR__ . '/maintenance.html');
    exit;
}

// Режим (dev/prod) визначається змінною оточення YII_ENV, за замовчуванням — prod.
require __DIR__ . '/../app/config/env.php';

require __DIR__ . '/../app/vendor/autoload.php';
require __DIR__ . '/../app/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../app/config/web.php';

(new yii\web\Application($config))->run();
