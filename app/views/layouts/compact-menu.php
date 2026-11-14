<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\MenuAsset;
use app\components\HeroSettings;
use yii\helpers\Html;
use yii\helpers\Url;

MenuAsset::register($this);
HeroSettings::registerPreload($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/images/icons/favicon.png')]);

?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div id="snowLayer" class="snow-layer" aria-hidden="true"></div>
<header class="gradient-hero text-white compact-menu-header" style="<?= Html::encode(HeroSettings::headerStyle()) ?>">
    <div class="topbar">
        <div class="container d-flex flex-wrap align-items-center justify-content-between py-2 small text-white-50">
            <div class="d-flex gap-3 align-items-center">
                <span>Графік: <?= Yii::$app->params['schedule'] ?></span>
            </div>
            <span class="text-white-50 text-decoration-none small d-none d-md-inline">
                <?= Html::encode(HeroSettings::get('subtitle')) ?>
            </span>
        </div>
    </div>

    <nav class="navbar navbar-dark container py-2 compact-menu-navbar">
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <a class="navbar-brand fw-bold brand-mark" href="<?= Url::home() ?>" id="link-logo">
                <?= Html::img('@web/images/icons/logo.png', ['alt' => '107sushi', 'id' => 'logo']) ?>
            </a>
        </div>

        <div class="compact-menu-hero-copy d-none d-lg-grid" aria-label="Переваги доставки">
            <div class="compact-menu-hero-item compact-menu-hero-item-eyebrow">
                <p class="compact-menu-hero-eyebrow mb-0"><?= Html::encode(HeroSettings::get('eyebrow')) ?></p>
            </div>
            <div class="compact-menu-hero-item compact-menu-hero-item-title">
                <p class="compact-menu-hero-title mb-0">
                    <?= HeroSettings::titleHtml('compact-menu-hero-brand') ?>
                </p>
            </div>
            <div class="compact-menu-hero-item compact-menu-hero-item-pill">
                <p class="compact-menu-hero-pill mb-0">
                    <span class="compact-menu-hero-pill-dot" aria-hidden="true"></span>
                    <?= Html::encode(HeroSettings::get('badge')) ?>
                </p>
            </div>
        </div>
    </nav>

    <div class="container compact-menu-mobile-hero d-lg-none">
        <div class="compact-menu-mobile-hero-card" aria-label="Переваги доставки">
            <p class="compact-menu-mobile-eyebrow mb-1"><?= Html::encode(HeroSettings::get('eyebrow')) ?></p>
            <p class="compact-menu-mobile-title mb-2">
                <?= HeroSettings::titleHtml('compact-menu-hero-brand') ?>
            </p>
            <p class="compact-menu-mobile-pill mb-0">
                <span class="compact-menu-hero-pill-dot" aria-hidden="true"></span>
                <?= Html::encode(HeroSettings::get('badge')) ?>
            </p>
        </div>
    </div>
</header>

<main>
    <?= $content ?>
</main>

<button class="compact-menu-scroll-top" id="menuScrollTop" type="button" aria-label="Догори" title="Догори">
    <span aria-hidden="true">↑</span>
</button>

<?= $this->render('footer') ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
