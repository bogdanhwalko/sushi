<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;

AppAsset::register($this);

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
<header class="gradient-hero text-white compact-menu-header">
    <div class="topbar">
        <div class="container d-flex flex-wrap align-items-center justify-content-between py-2 small text-white-50">
            <div class="d-flex gap-3 align-items-center">
                <span>Графік: <?= Yii::$app->params['schedule'] ?></span>
            </div>
            <span class="text-white-50 text-decoration-none small d-none d-md-inline">
                Обирайте страви. Про все інше подбає наша команда.
            </span>
        </div>
    </div>

    <nav class="navbar navbar-dark container py-3">
        <div class="d-flex align-items-center gap-2">
            <a class="navbar-brand fw-bold brand-mark" href="<?= Url::home() ?>" id="link-logo">
                <?= Html::img('@web/images/icons/logo.png', ['alt' => '107sushi', 'id' => 'logo']) ?>
            </a>
        </div>
    </nav>
    <div class="container pb-4">

    </div>
</header>

<main>
    <?= $content ?>
</main>

<?= $this->render('footer') ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
