<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

// По HTTPS cookies позначаються Secure — браузер не передасть їх незашифрованим з'єднанням.
// X-Forwarded-Proto — для сайту за проксі; підробка заголовка шкодить лише самому підробнику.
$isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
    || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https'
    || (string)($_SERVER['SERVER_PORT'] ?? '') === '443';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'uk',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@products' => '/uploads/products',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'XqR-SdgmM24Fi7p2TRvnX0muACDOeVGm',
            'csrfCookie' => [
                'httpOnly' => true,
                'secure' => $isHttps,
                'sameSite' => \yii\web\Cookie::SAME_SITE_LAX,
            ],
        ],
        'response' => [
            // базові заголовки безпеки для всіх сторінок
            'on beforeSend' => static function (\yii\base\Event $event) {
                $headers = $event->sender->headers;
                // сторінки сайту й адмінки не можна вбудувати в чужий iframe (clickjacking)
                $headers->set('X-Frame-Options', 'SAMEORIGIN');
                $headers->set('X-Content-Type-Options', 'nosniff');
                $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            },
        ],
        'session' => [
            'cookieParams' => [
                'httponly' => true,
                'secure' => $isHttps,
                'samesite' => \yii\web\Cookie::SAME_SITE_LAX,
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => [
                'name' => '_identity',
                'httpOnly' => true,
                'secure' => $isHttps,
                'sameSite' => \yii\web\Cookie::SAME_SITE_LAX,
            ],
            // адмін-панель має власну сторінку входу (див. admin AbstractController)
            'loginUrl' => ['/account/login'],
            // товари, додані до входу, переносяться в кошик акаунта
            'on afterLogin' => [\app\models\CartItems::class, 'mergeGuestCart'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                'menu' => 'site/menu',
                'POST telegram/webhook/<key:[0-9a-f]{24}>' => 'telegram/webhook',
                'admin-panel/<controller>/<action>' => 'admin-panel/<controller>/<action>',
                'admin-panel/<controller>' => 'admin-panel/<controller>/index',
                'admin-panel' => 'admin-panel/default/index',
            ],
        ],
        'ts' => require __DIR__ . '/telegram.php',
    ],
    'modules' => [
        'admin-panel' => [
            'class' => \app\modules\admin\Admin::class,
        ],
        'api' => [
            'class' => \app\modules\api\Api::class,
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // Лише локальна машина та приватні мережі (Docker). Ніколи не '*':
    // Gii генерує PHP-код, тож доступ до нього = виконання коду на сервері.
    $devAllowedIPs = ['127.0.0.1', '::1', '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16'];

    // пакети з require-dev можуть бути не встановлені (composer install --no-dev)
    if (class_exists(\yii\debug\Module::class)) {
        $config['bootstrap'][] = 'debug';
        $config['modules']['debug'] = [
            'class' => \yii\debug\Module::class,
            'allowedIPs' => $devAllowedIPs,
        ];
    }

    if (class_exists(\yii\gii\Module::class)) {
        $config['bootstrap'][] = 'gii';
        $config['modules']['gii'] = [
            'class' => \yii\gii\Module::class,
            'allowedIPs' => $devAllowedIPs,
        ];
    }
}

// Налаштування конкретного сервера (секрети тощо) — app/config/web-local.php, у git не потрапляє.
if (is_file(__DIR__ . '/web-local.php')) {
    $config = \yii\helpers\ArrayHelper::merge($config, require __DIR__ . '/web-local.php');
}

return $config;
