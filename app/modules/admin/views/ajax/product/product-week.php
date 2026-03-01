<?php
use yii\helpers\Html;
?>

<?php if (! empty($productWeek)): ?>
    <hr>
    <div class="d-flex align-items-center">
        <?= Html::img('@products/'. $productWeek->image, [
            'alt' => $productWeek->name,
            'class' => 'img-thumbnail mr-3', 'style' => 'width: 72px; height: 72px; object-fit: cover;',
            'all' => 'Product'
        ]) ?>

        <div class="flex-grow-1">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="font-weight-bold"><?= $productWeek->name ?></div>
                    <div class="text-muted small"><?= $productWeek->description ?></div>
                </div>
                <div class="text-right">
                    <div class="font-weight-bold text-nowrap"><?= $productWeek->price ?> ₴</div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
