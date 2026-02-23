<?php

use yii\helpers\Html;
/** @var \app\models\Products $product */

?>

<div class="row g-4 product-card" data-product="<?= $product->id ?>">
    <div class="col-md-5">
        <?= Html::img('@products/' . $product->image, ['alt' => $product->name, 'class' => 'img-fluid rounded-3']) ?>
    </div>
    <div class="col-md-7 mt-0">
        <h3><?= $product->name; ?></h3>
        <p class="text-muted small mb-2"></p>
        <p class="mb-3" id="modalDescription"><?= $product->description; ?></p>
        <div class="d-flex gap-3 flex-wrap align-items-center mb-3">
            <span class="badge bg-dark-soft text-dark"><?= $product->weight; ?>гр.</span>
            <span class="badge bg-dark-soft text-dark"><?= $product->category->name; ?></span>
        </div>
        <div class="d-flex gap-3 flex-wrap">
            <span class="fs-4 fw-bold"><?= $product->price . $product->currency; ?></span>
            <button class="btn btn-dark px-4 add-to-cart" type="button">Додати до кошика</button>
        </div>
    </div>
</div>