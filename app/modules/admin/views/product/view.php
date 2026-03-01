<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\admin\models\Product $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Товари', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="product-view">

    <p>
        <?= Html::a('Оновити', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Видалити', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'category_id',
            'name',
            'slug',
            'description:ntext',
            'short_description',
            'price',
            'old_price',
            'currency',
            'weight',
            'image',
            'meta_title',
            'meta_description',
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    return $model->status ? 'Не активне' : 'Активне';
                }
            ],
            'sort_order',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
