<?php

/** @var yii\web\View $this */
/** @var \app\models\Categorys[] $categories */
/** @var array<int, \app\models\Products[]> $productsByCategory */

use yii\helpers\Html;
use app\models\Categorys;

$this->title = 'Повне меню | 107 Sushi';

?>

<section id="menu" class="compact-menu-page py-4 py-lg-5">
    <div class="container">
        <div class="compact-menu-directory">
            <div class="compact-menu-directory-head">
                <div>
                    <p class="eyebrow text-muted mb-1">Категорії</p>
                    <p class="text-muted mb-0">Швидкий перехід по всьому меню.</p>
                </div>
                <span class="compact-menu-directory-count"><?= count($categories) ?> розділів</span>
            </div>

            <div class="category-nav" aria-label="Category navigation">
                <button class="category-nav-btn category-prev" type="button" aria-label="Previous categories">
                    &#8249;
                </button>
                <div class="filter-scroll" id="categoryFilters" aria-label="Фільтр за категоріями">
                    <?php foreach ($categories as $index => $category): ?>
                        <a class="btn btn-outline-dark btn-sm" href="#category-<?= $category->id ?>">
                            <?= $category->name ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <button class="category-nav-btn category-next" type="button" aria-label="Next categories">
                    &#8250;
                </button>
            </div>
        </div>

        <?php foreach ($categories as $category): ?>


            <section class="compact-menu-section" id="category-<?= $category->id ?>">
                <div class="compact-menu-section-head">
                    <div>
                        <p class="eyebrow text-muted mb-1">Категорія</p>
                        <h2 class="compact-menu-section-title mb-0"><?= $category->name ?></h2>
                    </div>
                    <span class="compact-menu-section-count"><?= count($category->products) ?> позицій</span>
                </div>
                <?php if ($category->products): ?>
                    <div class="compact-menu-grid">
                        <?php foreach ($category->products as $product): ?>
                            <article class="compact-menu-card product-card" data-product="<?= $product->id ?>">
                                <div class="compact-menu-card-image-wrap">
                                    <?= Html::img('@products/' . $product->image, [
                                        'alt' => $product->name,
                                        'class' => 'compact-menu-card-image',
                                    ]) ?>
                                </div>

                                <div class="compact-menu-card-body">
                                    <div class="compact-menu-card-top">
                                        <div class="compact-menu-card-title-wrap">
                                            <h3 class="compact-menu-card-title"><?= Html::encode($product->name) ?></h3>
                                            <?php if (is_numeric($product->weight)): ?>
                                                <p class="compact-menu-card-lead">Загальна вага: <?= $product->weight ?>гр.</p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="compact-menu-card-price">
                                            <strong><?= $product->price ?></strong>
                                            <span><?= $product->currency ?></span>
                                        </div>
                                    </div>

                                    <p class="compact-menu-card-description"><?= $product->description ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>

    </div>
</section>
