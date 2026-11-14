<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AccountAsset;
use yii\helpers\Html;
use yii\helpers\Url;

AccountAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['name' => 'robots', 'content' => 'noindex, nofollow']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/images/icons/favicon.png')]);

$isGuest = Yii::$app->user->isGuest;
$route = Yii::$app->controller->route;

$icons = [
    'back' => '<path d="M15 18l-6-6 6-6"/>',
    'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
    'login' => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/>',
    'signup' => '<circle cx="9" cy="8" r="4"/><path d="M2 21c0-3.5 3-6 7-6s7 2.5 7 6"/><path d="M19 8v6M16 11h6"/>',
];

/** Кнопка шапки: на телефоні лише іконка, з ширини sm — іконка + текст */
$headerButton = function (string $label, string $icon, $url, array $options = []) use ($icons) {
    $svg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"'
        . ' stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[$icon] . '</svg>';

    return Html::a($svg . '<span class="d-none d-sm-inline">' . Html::encode($label) . '</span>', $url, array_merge([
        'class' => 'account-header-btn',
        'title' => $label,
        'aria-label' => $label,
    ], $options));
};
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode(($this->title ? $this->title . ' — ' : '') . '107 Sushi') ?></title>
    <?php $this->head() ?>
</head>
<body class="account-page">
<?php $this->beginBody() ?>

<header class="gradient-hero text-white account-header">
    <div class="container d-flex align-items-center justify-content-between gap-3">
        <a class="account-logo" href="<?= Url::home() ?>" aria-label="107 Sushi — на головну">
            <?= Html::img('@web/images/icons/logo.png', ['alt' => '107sushi']) ?>
        </a>

        <nav class="d-flex align-items-center gap-2" aria-label="Навігація кабінету">
            <?= $headerButton('До меню', 'back', Url::home()) ?>

            <?php if (!$isGuest): ?>
                <?= $headerButton('Вийти', 'logout', ['/account/logout'], ['data-method' => 'post']) ?>
            <?php elseif ($route === 'account/signup'): ?>
                <?= $headerButton('Вхід', 'login', ['/account/login']) ?>
            <?php else: ?>
                <?= $headerButton('Реєстрація', 'signup', ['/account/signup']) ?>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container py-4 py-lg-5 account-main">
    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $messages): ?>
        <?php foreach ((array)$messages as $message): ?>
            <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?> alert-dismissible fade show" role="alert">
                <?= Html::encode($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрити"></button>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <?= $content ?>
</main>

<?= $this->render('footer') ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
