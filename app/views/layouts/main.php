<?php

/** @var yii\web\View $this */
/** @var string $content */
/** @var array $products */

use app\assets\AppAsset;
use app\components\HeroSettings;
use app\models\Categorys;
use yii\bootstrap5\Html;
use yii\helpers\Url;


AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/images/icons/favicon.png')]);

$this->title = '107 Sushi';

HeroSettings::registerPreload($this);

$cartTotal = 0;

/** @var \app\models\User|null $currentUser */
$currentUser = Yii::$app->user->identity;

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
    <?= Html::beginTag('body', [
        // місто з профілю — якщо відвідувач ще не обирав місто на цьому пристрої
        'data-user-city' => $currentUser->city_id ?? null,
    ]) ?>
    <?php $this->beginBody() ?>

    <div id="snowLayer" class="snow-layer" aria-hidden="true"></div>
    <?= HeroSettings::headerTag('gradient-hero text-white pb-5') ?>
        <div class="topbar">
            <div class="container d-flex flex-wrap align-items-center justify-content-between py-2 small text-white-50">
                <div class="d-flex gap-3 align-items-center">
                    <span>Графік: <?= Yii::$app->params['schedule'] ?></span>
                </div>
                <div class="d-flex gap-3 align-items-center">
<!--                    <span>--><?php //= Yii::$app->params['phone'] ?><!--</span>-->
<!--                    <span>--><?php //= Yii::$app->params['senderEmail'] ?><!--</span>-->
<!--                    <button class="btn btn-outline-light btn-sm snow-toggle" type="button" id="snowToggle" aria-pressed="true" title="Сніг увімкнено">-->
<!--                        <span class="snow-icon" aria-hidden="true"></span>-->
<!--                    </button>-->
                </div>
            </div>
        </div>
        <nav class="navbar navbar-dark container py-3">
            <div class="d-flex align-items-center gap-2">
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sideMenu" aria-controls="sideMenu" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a class="navbar-brand fw-bold brand-mark" href="/" id="link-logo">
                    <?= Html::img('@web/images/icons/logo.png', ['alt' => '107sushi', 'id' => 'logo']) ?>
                </a>
            </div>
            <div class="ms-auto d-flex align-items-center gap-3">
                <a class="nav-link fw-semibold px-2 d-none d-lg-inline" href="#menu">Меню</a>
                <a class="nav-link fw-semibold px-2 d-none d-lg-inline" href="#about">Про нас</a>
                <a class="nav-link fw-semibold px-2 d-none d-lg-inline" href="#contact">Контакти</a>

                <?= Html::a(
                    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>'
                    . '<span class="d-none d-md-inline">' . ($currentUser ? 'Кабінет' : 'Увійти') . '</span>',
                    [$currentUser ? '/account/profile' : '/account/login'],
                    [
                        'class' => 'account-button btn btn-outline-light d-flex align-items-center gap-2',
                        'aria-label' => $currentUser ? 'Особистий кабінет' : 'Увійти',
                        'title' => $currentUser ? 'Особистий кабінет' : 'Увійти',
                    ]
                ) ?>

                <button class="cart-button btn btn-outline-light position-relative d-flex align-items-center gap-2" id="cart-button" type="button" aria-label="Open cart">
                    <span class="bg-accent cart-badge">
                        <span id="cartCount"><?= $this->params['totalCount'] ?> </span>
                    </span>
                    <span class="icon-cart" aria-hidden="true"></span>
                </button>
                <a class="order-button btn btn-light text-dark fw-semibold px-3 d-none d-lg-inline-flex" href="#menu">Замовити</a>
            </div>
        </nav>

        <?= $this->render('side-menu') ?>

        <?= $this->render('cart') ?>

        <?= $this->render('detail') ?>


    </header>

    <main>
        <section id="menu" class="container py-5">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                <div>
                    <p class="eyebrow text-muted mb-1">Меню</p>
                    <h2 class="fw-bold mb-0">Роли та сети</h2>
                </div>
            </div>

            <div class="menu-sticky-bar mb-4" id="menuStickyBar">
                <p class="text-muted mb-1">
                    Товари по категоріях:
                </p>

                <div class="d-flex gap-2 flex-wrap justify-content-end align-items-center menu-filters">
                    <div class="category-nav" aria-label="Category navigation">
                        <button class="category-nav-btn category-prev" type="button" aria-label="Previous categories">
                            &#8249;
                        </button>
                        <div class="filter-scroll" id="categoryFilters" aria-label="Фільтр за категоріями">
                            <?php foreach (Categorys::getActive() as $index => $category): ?>
                                <button
                                    class="btn btn-outline-dark btn-sm<?= ($index == 0) ? ' active' : '' ?>"
                                    type="button"
                                    data-category="<?= Html::encode($category->slug) ?>"
                                    data-id="<?= $category->id ?>">

                                    <?= Html::encode($category->name) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <button class="category-nav-btn category-next" type="button" aria-label="Next categories">
                            &#8250;
                        </button>
                    </div>
                </div>
            </div>

            <!--Продукти-->
            <div class="row g-4" id="productGrid"></div>
        </section>


        <?= $this->render('about') ?>

        <?= $this->render('contact') ?>
    </main>

    <?= $this->render('footer') ?>

    <?= $this->render('_detail_product_modal') ?>

    <?= $this->render('_order_confirm_modal') ?>

    <?= $this->render('_consultation_modal') ?>

    <?= $this->render('_city_select_modal') ?>

    <?= $this->render('toast') ?>

    <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
