<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);

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
                    <span>+38 (093) 000 10 10</span>
                    <span>@umi.sushi</span>
                    <button class="btn btn-outline-light btn-sm snow-toggle" type="button" id="snowToggle" aria-pressed="true" title="Сніг увімкнено">
                        <span class="snow-icon" aria-hidden="true"></span>
                        <span class="snow-label">Сніг</span>
                    </button>
                </div>
            </div>
        </div>
        <nav class="navbar navbar-dark container py-3">
            <div class="d-flex align-items-center gap-2">
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sideMenu" aria-controls="sideMenu" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a class="navbar-brand fw-bold brand-mark" href="#">UMI</a>
            </div>
            <div class="d-flex d-lg-none ms-auto align-items-center gap-2">
                <button class="btn btn-outline-light position-relative d-flex align-items-center gap-2 cart-button" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartDrawer" aria-controls="cartDrawer" aria-label="Відкрити кошик">
                    <span class="icon-cart" aria-hidden="true"></span>
                    <span class="badge bg-accent cart-badge" id="cartCountMobile">0</span>
                </button>
            </div>
            <div class="d-none d-lg-flex ms-auto align-items-center gap-3">
                <a class="nav-link fw-semibold px-2" href="#menu">Меню</a>
                <a class="nav-link fw-semibold px-2" href="#about">Про нас</a>
                <a class="nav-link fw-semibold px-2" href="#contact">Контакти</a>
                <select id="citySelector" class="form-select form-select-sm shadow-none border-0 city-select">
                    <option value="all">Всі міста</option>
                    <option value="kyiv" selected>Київ</option>
                    <option value="lviv">Львів</option>
                    <option value="odesa">Одеса</option>
                </select>
                <button class="btn btn-outline-light position-relative d-flex align-items-center gap-2 cart-button" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartDrawer" aria-controls="cartDrawer">
                    <span class="icon-cart" aria-hidden="true"></span>
                    <span class="d-none d-xl-inline">Кошик</span>
                    <span class="badge bg-accent cart-badge" id="cartCount">0</span>
                </button>
                <a class="btn btn-light text-dark fw-semibold px-3" href="#contact">Замовити</a>
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
                    <select id="sideCitySelector" class="form-select form-select-sm side-city-select">
                        <option value="all">Всі міста</option>
                        <option value="kyiv" selected>Київ</option>
                        <option value="lviv">Львів</option>
                        <option value="odesa">Одеса</option>
                    </select>
                </div>
                <div class="border-top pt-3 text-white-50 small">
                    <div>Графік: 10:00 — 23:00</div>
                    <div>Телефон: +38 (093) 000 10 10</div>
                </div>
            </div>
        </div>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="cartDrawer" aria-labelledby="cartDrawerLabel">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title fw-bold" id="cartDrawerLabel">Ваш кошик</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column gap-3">
                <div id="cartItems" class="cart-items"></div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Разом</span>
                    <span class="fw-bold fs-5" id="cartTotal">0 ₴</span>
                </div>
                <div class="d-grid gap-2">
                    <button class="btn btn-dark" type="button" id="checkoutBtn">Оформити</button>
                    <button class="btn btn-outline-dark" type="button" id="clearCartBtn">Очистити кошик</button>
                </div>
                <p class="text-muted small mb-0">Соєвий соус, імбир та васабі додаємо безкоштовно.</p>
            </div>
        </div>
        <div class="container pt-4">
            <div class="row align-items-center gy-4">
                <div class="col-lg-6">
                    <p class="eyebrow mb-2">Доставка за 45 хвилин</p>
                    <h1 class="display-4 fw-bold lh-1 mb-3">Суші, що створені <span class="highlight">для моментів</span></h1>
                    <p class="lead mb-4">Авторські роли, ретельно відібрана риба та соуси, які збалансовані до останньої краплі. Оберіть місто та замовляйте без очікування.</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a class="btn btn-outline-light btn-lg px-4" href="#menu">Переглянути меню</a>
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
                            <span class="text-muted small">Доступно у: Київ, Львів</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <img src="assets/sushi-signature.svg" class="rounded-3 me-3 hero-img" alt="Sushi set">
                            <div>
                                <h4 class="fw-semibold mb-1">UMI Signature Set</h4>
                                <p class="text-muted small mb-2">24 шт • Лосось / тунець / вугор • Фірмовий соус</p>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="fs-5 fw-bold">649 ₴</span>
                                    <button class="btn btn-sm btn-dark view-details" data-product="signature-set">Детальніше</button>
                                    <button class="btn btn-sm btn-outline-dark add-to-cart" data-product="signature-set">До кошика</button>
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
                    <p class="text-muted mb-0">Фільтруємо доступність за містом: <span id="cityLabel" class="fw-semibold">Київ</span></p>
                </div>
                <div class="d-flex gap-2 flex-wrap justify-content-end align-items-center">
                    <div class="filter-scroll" id="categoryFilters" aria-label="Фільтр за категоріями"></div>
                    <span class="badge bg-dark-soft">Чисті інгредієнти</span>
                </div>
            </div>

            <div class="row g-4" id="productGrid">
                <div class="col-md-6 col-lg-4 product-card" data-cities="kyiv,lviv" data-category="rolls">
                    <div class="card h-100 shadow-sm border-0 rounded-4">
                        <img src="assets/sushi-philadelphia.svg" class="card-img-top" alt="Філадельфія">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="card-title fw-bold mb-1">Філадельфія</h5>
                                    <p class="text-muted small mb-0">8 шт • Лосось • Сир креметта</p>
                                </div>
                                <span class="badge bg-primary-soft text-primary">389 ₴</span>
                            </div>
                            <p class="card-text text-muted small flex-grow-1">Класика на вершковій ноті, балансована рисом кіме та ніжним лососем.</p>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="d-flex gap-2 flex-wrap">
                                    <button class="btn btn-outline-dark btn-sm view-details" data-product="philadelphia">Детальніше</button>
                                    <button class="btn btn-dark btn-sm add-to-cart" data-product="philadelphia">До кошика</button>
                                </div>
                                <span class="city-tag">Київ / Львів</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 product-card" data-cities="kyiv,odesa" data-category="rolls">
                    <div class="card h-100 shadow-sm border-0 rounded-4">
                        <img src="assets/sushi-dragon.svg" class="card-img-top" alt="Дракон">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="card-title fw-bold mb-1">Дракон</h5>
                                    <p class="text-muted small mb-0">8 шт • Вугор • Авокадо • Теріякі</p>
                                </div>
                                <span class="badge bg-primary-soft text-primary">429 ₴</span>
                            </div>
                            <p class="card-text text-muted small flex-grow-1">Солонувато-солодкий профіль з карамелізованим соусом та хрустким кунжутом.</p>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="d-flex gap-2 flex-wrap">
                                    <button class="btn btn-outline-dark btn-sm view-details" data-product="dragon">Детальніше</button>
                                    <button class="btn btn-dark btn-sm add-to-cart" data-product="dragon">До кошика</button>
                                </div>
                                <span class="city-tag">Київ / Одеса</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 product-card" data-cities="lviv,odesa" data-category="veggie">
                    <div class="card h-100 shadow-sm border-0 rounded-4">
                        <img src="assets/sushi-veggie.svg" class="card-img-top" alt="Веггі сет">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="card-title fw-bold mb-1">Веггі сет</h5>
                                    <p class="text-muted small mb-0">16 шт • Авокадо • Тофу • Огірок</p>
                                </div>
                                <span class="badge bg-primary-soft text-primary">329 ₴</span>
                            </div>
                            <p class="card-text text-muted small flex-grow-1">Легкий зелений сет з лаймовою нотою та горіховим соусом.</p>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="d-flex gap-2 flex-wrap">
                                    <button class="btn btn-outline-dark btn-sm view-details" data-product="veggie">Детальніше</button>
                                    <button class="btn btn-dark btn-sm add-to-cart" data-product="veggie">До кошика</button>
                                </div>
                                <span class="city-tag">Львів / Одеса</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 product-card" data-cities="kyiv" data-category="sets">
                    <div class="card h-100 shadow-sm border-0 rounded-4">
                        <img src="assets/sushi-haru.svg" class="card-img-top" alt="Сет Хару">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="card-title fw-bold mb-1">Сет Хару</h5>
                                    <p class="text-muted small mb-0">32 шт • Лосось • Сніжний краб • Сир</p>
                                </div>
                                <span class="badge bg-primary-soft text-primary">749 ₴</span>
                            </div>
                            <p class="card-text text-muted small flex-grow-1">Для компанії: хітові роли у збалансованих пропорціях.</p>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="d-flex gap-2 flex-wrap">
                                    <button class="btn btn-outline-dark btn-sm view-details" data-product="haru">Детальніше</button>
                                    <button class="btn btn-dark btn-sm add-to-cart" data-product="haru">До кошика</button>
                                </div>
                                <span class="city-tag">Київ</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 product-card" data-cities="all" data-category="special">
                    <div class="card h-100 shadow-sm border-0 rounded-4">
                        <img src="assets/sushi-spring.svg" class="card-img-top" alt="Спрінг роли">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="card-title fw-bold mb-1">Спрінг роли</h5>
                                    <p class="text-muted small mb-0">6 шт • Креветка • Манго • Чилі</p>
                                </div>
                                <span class="badge bg-primary-soft text-primary">279 ₴</span>
                            </div>
                            <p class="card-text text-muted small flex-grow-1">Свіжість та легка пікантність, подаємо зі спайсі-майо.</p>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="d-flex gap-2 flex-wrap">
                                    <button class="btn btn-outline-dark btn-sm view-details" data-product="spring">Детальніше</button>
                                    <button class="btn btn-dark btn-sm add-to-cart" data-product="spring">До кошика</button>
                                </div>
                                <span class="city-tag">Усі міста</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 product-card" data-cities="lviv" data-category="rolls">
                    <div class="card h-100 shadow-sm border-0 rounded-4">
                        <img src="assets/sushi-sake.svg" class="card-img-top" alt="Саке маві">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="card-title fw-bold mb-1">Саке маві</h5>
                                    <p class="text-muted small mb-0">8 шт • Лосось • Огірок • Такуан</p>
                                </div>
                                <span class="badge bg-primary-soft text-primary">309 ₴</span>
                            </div>
                            <p class="card-text text-muted small flex-grow-1">Ясний, чистий смак з легким цитрусом та маринованим дайконом.</p>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="d-flex gap-2 flex-wrap">
                                    <button class="btn btn-outline-dark btn-sm view-details" data-product="sake-maki">Детальніше</button>
                                    <button class="btn btn-dark btn-sm add-to-cart" data-product="sake-maki">До кошика</button>
                                </div>
                                <span class="city-tag">Львів</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="about" class="py-5 bg-light">
            <div class="container">
                <div class="row gy-4 align-items-center">
                    <div class="col-lg-6">
                        <p class="eyebrow text-muted mb-1">Чому UMI</p>
                        <h3 class="fw-bold">Чиста риба, відкриті кухні, прозорі рецепти</h3>
                        <p class="text-muted">Ми готуємо в оупен-кітченах, працюємо лише з охолодженою рибою з сертифікатами, а рис промиваємо до ідеальної прозорості. Немає прихованих соусів чи цукру там, де його не повинно бути.</p>
                        <div class="d-flex gap-3 flex-wrap">
                            <div class="pill-pill">Власне навчання майстрів</div>
                            <div class="pill-pill">Нічні поставки риби</div>
                            <div class="pill-pill">Сертифікат ISO22000</div>
                        </div>
                    </div>
                    <div class="col-lg-5 ms-auto">
                        <div class="about-card rounded-4 p-4 bg-white shadow-sm">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-semibold">Середній рейтинг</span>
                                <span class="badge bg-success-subtle text-success">4.9 ★</span>
                            </div>
                            <p class="text-muted small mb-4">Понад 12 000 відгуків у всіх містах присутності.</p>
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar bg-dark" role="progressbar" style="width: 92%;" aria-valuenow="92" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted small">
                                <span>Свіжість продуктів</span>
                                <span>92%</span>
                            </div>
                            <div class="progress my-3" style="height: 8px;">
                                <div class="progress-bar bg-dark" role="progressbar" style="width: 88%;" aria-valuenow="88" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted small">
                                <span>Доставка вчасно</span>
                                <span>88%</span>
                            </div>
                        </div>
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
                            <button class="btn btn-dark px-4">Відкрити меню</button>
                            <button class="btn btn-outline-dark px-4" type="button" id="consultBtn">Консультація</button>
                        </div>
                    </div>
                    <div class="col-lg-5 ms-auto">
                        <div class="contact-card bg-white rounded-4 p-4 shadow-sm">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-semibold">Графік</span>
                                <span class="text-muted small">10:00 — 23:00</span>
                            </div>
                            <p class="text-muted small mb-3">Однаковий для всіх міст, доставка починається за 45 хвилин.</p>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div>
                                    <div class="text-muted small">Телефон</div>
                                    <div class="fw-semibold">+38 (093) 000 10 10</div>
                                </div>
                                <div>
                                    <div class="text-muted small">Instagram</div>
                                    <div class="fw-semibold">@umi.sushi</div>
                                </div>
                                <div>
                                    <div class="text-muted small">Адреса</div>
                                    <div class="fw-semibold" id="addressLabel">Київ, вул. Антоновича, 90</div>
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
            <div class="text-muted small">© 2025 UMI Sushi. Чиста риба, чесні роли.</div>
            <div class="d-flex gap-3 small">
                <a href="#" class="text-decoration-none text-muted">Політика</a>
                <a href="#" class="text-decoration-none text-muted">Публічна оферта</a>
                <a href="#" class="text-decoration-none text-muted">Контакти</a>
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
                    <div class="row g-4">
                        <div class="col-md-5">
                            <img id="modalImage" src="" class="img-fluid rounded-3" alt="Зображення ролу">
                        </div>
                        <div class="col-md-7">
                            <p class="text-muted small mb-2" id="modalAvailability"></p>
                            <p class="mb-3" id="modalDescription"></p>
                            <div class="d-flex gap-3 flex-wrap align-items-center mb-3">
                                <span class="badge bg-dark-soft text-dark" id="modalWeight"></span>
                                <span class="badge bg-dark-soft text-dark" id="modalPieces"></span>
                            </div>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <span class="fs-4 fw-bold" id="modalPrice"></span>
                                <button class="btn btn-dark px-4" id="addToCartBtn" type="button">Додати до кошика</button>
                                <span class="text-success small" id="cartFeedback" style="display:none;">Додано!</span>
                            </div>
                        </div>
                    </div>
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
                                <option value="" disabled selected>Оберіть місто</option>
                                <option value="kyiv">Київ</option>
                                <option value="lviv">Львів</option>
                                <option value="odesa">Одеса</option>
                                <option value="all">Всі міста</option>
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
