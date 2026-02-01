<?php

use yii\helpers\Html;

/** @var \app\models\Products[] $products */

?>

<?php foreach ($products as $product): ?>
<div
    class="col-md-6 col-lg-4 product-card"
    data-product="<?= Html::encode($product->id) ?>"
    data-cities="<?= Html::encode('city') ?>"
    data-category="<?= Html::encode('cagegory') ?>">

    <div class="card h-100 shadow-sm border-0 rounded-4">
        <img src="<?= Html::encode($product->image) ?>" class="card-img-top" alt="<?= Html::encode($product->name) ?>">
        <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h5 class="card-title fw-bold mb-1"><?= Html::encode($product->name) ?></h5>
                    <?php if ($product->meta_title !== ''): ?>
                        <p class="text-muted small mb-0"><?= Html::encode($product->meta_title) ?></p>
                    <?php endif; ?>
                </div>
                <span class="badge bg-primary-soft text-primary"><?= Html::encode($product->getPriseAsText()) ?></span>
            </div>
            <?php if (! empty($product->description)): ?>
                <p class="card-text text-muted small flex-grow-1"><?= Html::encode($product->description) ?></p>
            <?php endif; ?>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-outline-dark btn-sm view-details">Детальніше</button>
                    <button class="btn btn-dark btn-sm add-to-cart">До кошика</button>
                </div>
                <span class="city-tag"><?= Html::encode('city') ?></span>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>