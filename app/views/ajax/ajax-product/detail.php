<?php

use yii\helpers\Html;
/** @var \app\models\Products $product */

?>

<div class="row g-4 product-card" data-product="<?= $product->id ?>">
    <div class="col-md-5">
        <?= Html::img('@products/' . $product->image, ['alt' => $product->name, 'class' => 'img-fluid rounded-3']) ?>
    </div>
    <div class="col-md-7 mt-0">
        <h3><?= Html::encode($product->name) ?></h3>
        <p class="text-muted small mb-2"></p>
        <p class="mb-3" id="modalDescription"><?= nl2br(Html::encode($product->description)) ?></p>
        <div class="d-flex gap-3 flex-wrap align-items-center mb-3">
            <?php if ($product->weight): ?>
                <span class="badge bg-dark-soft text-dark"><?= Html::encode($product->weight) ?>гр.</span>
            <?php endif; ?>
            <?php if ($product->category !== null): ?>
                <span class="badge bg-dark-soft text-dark"><?= Html::encode($product->category->name) ?></span>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-3 flex-wrap">
            <span class="fs-4 fw-bold"><?= Html::encode($product->price . ' ' . $product->currency) ?></span>
            <button class="btn btn-dark px-4 add-to-cart" type="button">Додати до кошика</button>
        </div>
    </div>
</div>