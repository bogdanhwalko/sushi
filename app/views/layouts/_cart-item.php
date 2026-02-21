<?php

use yii\helpers\Html;

/** @var \app\models\CartItems $cartItem */
?>

<div
class="cart-line d-flex gap-3 align-items-center cart-item"
data-item_id="<?= $cartItem->id ?>">

    <?= Html::img('@web/images/products/' . $cartItem->product->image, [
        'alt' => $cartItem->product->name,
        'class' => 'cart-thumb align-self-start'
    ]) ?>
    <div class="flex-grow-1">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <h6 class="fw-semibold mb-1"><?= $cartItem->product->name ?></h6>
            <button class="btn btn-link text-danger text-decoration-none p-0 small remove-item" type="button">
                <?= Html::img('@web/images/icons/delete.png', ['alt' => 'Видалити', 'class' => 'cart-remove']) ?>
            </button>
        </div>
        <div class="text-muted small item-price"><?= $cartItem->product->price ?> ₴ • <?= $cartItem->product->weight ?> г</div>
        <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
            <div class="qty-control" aria-label="Кількість">
                <button class="qty-btn" type="button" aria-label="Зменшити" data-type="dec">-</button>
                <span class="qty-value"><?= $cartItem->qty ?></span>
                <button class="qty-btn" type="button" aria-label="Збільшити" data-type="inc">+</button>
            </div>
            <div class="price align-content-end ms-auto"><?= ($cartItem->product->price * $cartItem->qty) ?> ₴</div>
        </div>
    </div>

</div>