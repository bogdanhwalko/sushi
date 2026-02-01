<?php

/** @var \app\models\Products $product */

?>

<div class="row g-4">
    <div class="col-md-5">
        <img id="modalImage" src="<?= $product->image; ?>" class="img-fluid rounded-3" alt="Зображення ролу">
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