<?php

/** @var yii\web\View $this */
/** @var string $content */
/** @var array $products */

use app\assets\AppAsset;
use yii\bootstrap5\Html;
use app\models\Categorys;


AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);

$cartTotal = 0;

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
    <header class="gradient-hero text-white pb-5">
        <div class="topbar">
            <div class="container d-flex flex-wrap align-items-center justify-content-between py-2 small text-white-50">
                <div class="d-flex gap-3 align-items-center">
                    <span class="d-flex align-items-center gap-1"><span class="dot"></span>Доставка 45 хв</span>
                    <span>Графік: 10:00 — 23:00</span>
                </div>
                <div class="d-flex gap-3 align-items-center">
                    <span><?= Yii::$app->params['phone'] ?></span>
                    <span><?= Yii::$app->params['senderEmail'] ?></span>
                    <button class="btn btn-outline-light btn-sm snow-toggle" type="button" id="snowToggle" aria-pressed="true" title="Сніг увімкнено">
                        <span class="snow-icon" aria-hidden="true"></span>
                    </button>
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



                <button class="cart-button btn btn-outline-light position-relative d-flex align-items-center gap-2" id="cart-button" type="button" aria-label="Open cart">
                    <span class="icon-cart" aria-hidden="true"></span>
                    <span class="bg-accent cart-badge" id="cartCount">1000 ₴</span>
                </button>
                <a class="order-button btn btn-light text-dark fw-semibold px-3 d-none d-lg-inline-flex" href="#menu">Замовити</a>
            </div>
        </nav>

        <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="sideMenu" aria-labelledby="sideMenuLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="sideMenuLabel">Навігація</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column gap-3">
                <a class="text-white text-decoration-none fw-semibold" href="#menu" data-bs-dismiss="offcanvas">Меню</a>
                <a class="text-white text-decoration-none fw-semibold" href="#about" data-bs-dismiss="offcanvas">Про нас</a>
                <a class="text-white text-decoration-none fw-semibold" href="#contact" data-bs-dismiss="offcanvas">Контакти</a>
                <div class="border-top pt-3">
                    <div class="city-select-label">Місто</div>
                    <?= Html::dropDownList(
                        'cities_in_menu',
                        'all',
                        \app\models\Cities::getCitiesInSelect(),
                        [
                            'class' => 'form-select form-select-sm side-city-select',
                            'id' => 'sideCitySelector'
                        ]
                    )?>
                </div>
                <div class="border-top pt-3 text-white-50 small">
                    <div>Графік: 10:00 — 23:00</div>
                    <div>Телефон: +38 (093) 000 10 10</div>
                </div>
            </div>
        </div>

        <!--КОРЗИНА-->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="cartDrawer" aria-labelledby="cartDrawerLabel">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title fw-bold" id="cartDrawerLabel">Ваш кошик</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column gap-3" id="cart-content">

            </div>
        </div>

        <div class="container pt-4">
            <div class="row align-items-center gy-4">
                <div class="col-lg-6">
                    <p class="eyebrow mb-2">Доставка за 45 хвилин</p>
                    <h1 class="display-4 fw-bold lh-1 mb-3">
                        Доставка японської кухні у твоєму місті
                        <span class="highlight text-sushi">"SUSHI 107"</span>
                    </h1>
                    <p class="lead mb-4 fs-6 fw-medium">Обирайте страви. Про все інше подбає наша команда.</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <div class="d-flex align-items-center gap-2 flex-wrap" id="happyHours">
                            <div class="pill-pill d-inline-flex align-items-center gap-2">
                                <span class="happy-dot" id="happyDot" aria-hidden="true"></span>
                                <span>Щасливі години</span>
                            </div>
                            <div class="pill-pill" id="happyStatus">Сьогодні 12:00–15:00 та 19:00–22:00</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 ms-auto">
                    <div class="hero-card rounded-4 p-4 bg-white text-dark shadow-lg">
                        <div class="d-flex align-items-center mb-3">
                            <div class="hero-badge me-3">Шеф рекомендує</div>

                                <span class="text-muted small">???????? ?: <?= Html::encode(1) ?></span>

                        </div>
                        <div class="d-flex align-items-center">
                            <img src="" class="rounded-3 me-3 hero-img" alt="<?= Html::encode('dasdad') ?>">
                            <div class="">
                                <h4 class="fw-semibold mb-1"><?= Html::encode('$heroTitle') ?></h4>
                                <p class="text-muted small mb-2"><?= Html::encode('$heroMeta') ?></p>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="fs-5 fw-bold"><?= Html::encode('$heroPriceText') ?></span>
                                    <button class="btn btn-sm btn-dark">Детальніше</button>
                                    <button class="btn btn-sm btn-outline-dark">До кошика</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section id="menu" class="container py-5">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <p class="eyebrow text-muted mb-1">Меню</p>
                    <h2 class="fw-bold">Роли та сети</h2>
                    <p class="text-muted mb-0">
                        Товари по категоріях:
                    </p>
                </div>

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

                <!--Продукти-->
                <div class="row g-4 mt-0" id="productGrid"></div>
            </div>
        </section>

        <section id="about" class="py-5 bg-light">
            <div class="container">
                <div class="row gy-4 align-items-center justify-content-center">
                    <div class="col-lg-10 px-4 px-md-3 advertising-text ms-center">
                        <h1 class="fw-bold text-sushi">107 Суші</h1>
                        <p class="fw-medium">107 Суші це більше, ніж просто доставка у твоєму місті. Це команда людей, для яких суші — не тренд і не бізнес «на потоці». Це справа, яку ми будуємо з характером і системою.</p>
                        <p class="fw-medium">Ми створили 107 Суші з однією чіткою метою — підняти рівень доставки до стандартів ресторанної якості. Без компромісів. Без випадковостей. Без «і так зійде».</p>
                        <p class="fw-medium">Кожного дня ми вдосконалюємо рецептури, відточуємо технології, тестуємо нові поєднання смаків та контролюємо кожен процес — від вибору інгредієнтів до моменту, коли замовлення потрапляє до вас.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="contact" class="py-5">
            <div class="container">
                <div class="row gy-4 align-items-center">
                    <div class="col-lg-6">
                        <h3 class="fw-bold mb-2">Замовляйте в один клік</h3>
                        <p class="text-muted mb-4">Обирайте місто, рол або сет — ми покажемо актуальну наявність і доставимо без запізнень.</p>
                        <div class="d-flex flex-wrap gap-3">
                            <a class="btn btn-dark px-4" href="#menu">Відкрити меню</a>
                            <button class="btn btn-outline-dark px-4" type="button" id="consultBtn">Консультація</button>
                        </div>
                    </div>
                    <div class="col-lg-5 ms-auto">
                        <div class="contact-card bg-white rounded-4 p-4 shadow-lg">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-semibold">Графік</span>
                                <span class="text-muted small">10:00 — 23:00</span>
                            </div>
                            <p class="text-muted small mb-3">Однаковий для всіх міст, доставка починається за 45 хвилин.</p>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div>
                                    <div class="text-muted small">Телефон</div>
                                    <div class="fw-semibold"><?= Yii::$app->params['phone'] ?></div>
                                </div>
                                <div>
                                    <div class="text-muted small">Instagram</div>
                                    <div class="fw-semibold"><?= Yii::$app->params['instagram'] ?></div>
                                </div>
                                <div>
                                    <div class="text-muted small">Адреса</div>
                                    <div class="fw-semibold" id="addressLabel"><?= Yii::$app->params['address'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="py-4 border-top">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <div class="small text-white">© <?= date('Y')?> <span class="text-sushi">Sushi 107</span> Обирайте страви. Про все інше подбає наша команда.</div>
            <div class="d-flex gap-3 small">
                <a href="#" class="footer-link">Політика</a>
                <a href="#" class="footer-link">Публічна оферта</a>
                <a href="#" class="footer-link">Контакти</a>
            </div>
        </div>
    </footer>

    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Деталі ролу</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer border-0">
                    <div class="small text-muted">Соєвий соус, імбир та васабі входять у комплект. Ми не використовуємо підсилювачі смаку.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Оформлення замовлення</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="checkoutForm" novalidate>
                        <div class="mb-3">
                            <label for="checkoutName" class="form-label">Прізвище та ім'я</label>
                            <input type="text" class="form-control" id="checkoutName" placeholder="Напр. Іваненко Іван" autocomplete="name" required>
                            <div class="invalid-feedback">Вкажіть прізвище та ім'я.</div>
                        </div>
                        <div class="mb-3">
                            <label for="checkoutPhone" class="form-label">Номер телефону</label>
                            <input type="tel" class="form-control" id="checkoutPhone" placeholder="+38 (0XX) XXX-XX-XX" autocomplete="tel" required>
                            <div class="invalid-feedback">Вкажіть коректний номер телефону.</div>
                        </div>
                        <button class="btn btn-dark w-100" type="submit">Підтвердити замовлення</button>
                        <p class="text-muted small mt-2 mb-0">Менеджер зателефонує для підтвердження деталей.</p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="consultModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Консультація</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="consultForm" novalidate>
                        <div class="mb-3">
                            <label for="consultPhone" class="form-label">Номер телефону</label>
                            <input type="tel" class="form-control" id="consultPhone" placeholder="+38 (0XX) XXX-XX-XX" autocomplete="tel" required>
                            <div class="invalid-feedback">Вкажіть коректний номер телефону.</div>
                        </div>
                        <button class="btn btn-dark w-100" type="submit">Замовити дзвінок</button>
                        <p class="text-muted small mt-2 mb-0">Передзвонимо протягом 15 хвилин.</p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cityWelcomeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Оберіть місто</h5>
                </div>
                <div class="modal-body">
                    <form id="cityWelcomeForm" novalidate>
                        <div class="mb-3">
                            <label for="cityWelcomeSelect" class="form-label">Місто доставки</label>
                            <select id="cityWelcomeSelect" class="form-select" required>
                        <option value="" disabled selected>???????? ?????</option>
<!--                        --><?php //foreach ($cityMap as $cityKey => $city): ?>
<!--                            --><?php //if (!is_array($city)) { continue; } ?>
<!--                            --><?php //$label = $city['label'] ?? $cityKey; ?>
<!--                            <option value="--><?php //= Html::encode($cityKey) ?><!--">--><?php //= Html::encode($label) ?><!--</option>-->
<!--                        --><?php //endforeach; ?>
                    </select>
                            <div class="invalid-feedback">Оберіть місто для продовження.</div>
                        </div>
                        <button class="btn btn-dark w-100" type="submit" id="cityWelcomeBtn">Продовжити</button>
                        <p class="text-muted small mt-2 mb-0">Ваш вибір збережемо для наступного візиту.</p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="cartToast" class="toast align-items-center text-bg-dark border-0" role="alert" aria-live="polite" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="cartToastMessage">Товар додано у кошик.</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
