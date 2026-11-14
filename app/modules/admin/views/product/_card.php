<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\admin\models\Product $model */
/** @var callable $inline fn(Product, string $field): string — значення, що змінюється подвійним кліком */

// частина опису: короткий опис, а якщо його немає — початок повного (решту обріже CSS до 3 рядків)
$excerpt = trim(strip_tags((string)($model->short_description ?: $model->description)));
$excerpt = \yii\helpers\StringHelper::truncate(preg_replace('/\s+/u', ' ', $excerpt), 160);
?>
<div class="card h-100 shadow-sm product-admin-card<?= $model->status ? '' : ' is-inactive' ?>">
    <!-- квадрат фіксованого розміру: навіть якщо файлу фото немає, картки не «з'їжджають» -->
    <a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>" class="product-card-img">
        <i class="far fa-image"></i>
        <?php if ($model->image): ?>
            <?= Html::img('@products/' . $model->image, [
                'alt' => '',
                'loading' => 'lazy',
                'onerror' => "this.remove()",
            ]) ?>
        <?php endif; ?>
    </a>

    <div class="card-body d-flex flex-column p-3">
        <div class="d-flex justify-content-between align-items-start small text-muted mb-1">
            <span>#<?= (int)$model->id ?> · <?= Html::encode($model->category->name ?? '—') ?></span>
            <?= $model->status
                ? '<span class="badge bg-success">Активний</span>'
                : '<span class="badge bg-danger">Не активний</span>' ?>
        </div>

        <h5 class="card-title font-weight-bold mb-1"><?= Html::encode($model->name) ?></h5>

        <?php if ($excerpt !== ''): ?>
            <p class="product-excerpt text-muted small mb-2" title="<?= Html::encode($excerpt) ?>"><?= Html::encode($excerpt) ?></p>
        <?php endif; ?>

        <div class="d-flex flex-wrap justify-content-between align-items-center mt-auto pt-2">
            <span class="h5 mb-0 font-weight-bold mr-2"><?= $inline($model, 'price') ?></span>
            <span class="text-muted"><?= $inline($model, 'weight') ?></span>
        </div>
    </div>

    <div class="card-footer product-card-actions">
        <?= Html::a('<i class="fas fa-pencil-alt mr-1"></i>Редагувати', ['update', 'id' => $model->id], [
            'class' => 'btn btn-sm btn-primary flex-grow-1',
        ]) ?>
        <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $model->id], [
            'class' => 'btn btn-sm btn-light',
            'title' => 'Переглянути',
        ]) ?>
        <?= Html::a('<i class="fas fa-trash-alt"></i>', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-sm btn-light btn-delete',
            'title' => 'Видалити',
            'data-method' => 'post',
            'data-confirm' => 'Видалити товар «' . $model->name . '»?',
        ]) ?>
    </div>
</div>
