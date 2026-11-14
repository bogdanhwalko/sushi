<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\admin\models\Product $model */
/** @var callable $inline fn(Product, string $field): string — значення, що змінюється подвійним кліком */
?>
<div class="card h-100 shadow-sm product-admin-card<?= $model->status ? '' : ' is-inactive' ?>">
    <a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>">
        <?php if ($model->image): ?>
            <?= Html::img('@products/' . $model->image, [
                'alt' => $model->name,
                'class' => 'card-img-top',
                'loading' => 'lazy',
            ]) ?>
        <?php else: ?>
            <div class="no-image"><i class="far fa-image"></i></div>
        <?php endif; ?>
    </a>

    <div class="card-body d-flex flex-column p-3">
        <div class="d-flex justify-content-between align-items-start small text-muted mb-1">
            <span>#<?= (int)$model->id ?> · <?= Html::encode($model->category->name ?? '—') ?></span>
            <?= $model->status
                ? '<span class="badge bg-success">Активний</span>'
                : '<span class="badge bg-danger">Не активний</span>' ?>
        </div>

        <h5 class="card-title font-weight-bold mb-2"><?= Html::encode($model->name) ?></h5>

        <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
            <span class="h5 mb-0 font-weight-bold"><?= $inline($model, 'price') ?></span>
            <span class="text-muted"><?= $inline($model, 'weight') ?></span>
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-between p-2">
        <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'Переглянути']) ?>
        <?= Html::a('<i class="fas fa-pencil-alt"></i>', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'Редагувати']) ?>
        <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-sm btn-outline-danger',
            'title' => 'Видалити',
            'data-method' => 'post',
            'data-confirm' => 'Видалити товар «' . $model->name . '»?',
        ]) ?>
    </div>
</div>
