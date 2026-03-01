<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\admin\models\City $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Міста', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

\yii\web\YiiAsset::register($this);
?>
<div class="city-view">

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
            'name',
            'full_name',
            'sort_order',
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    return $model->status ? 'Не активне' : 'Активне';
                }
            ],
            'telegramGroup.name',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
