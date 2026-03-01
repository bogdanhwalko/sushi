<?php

use app\modules\admin\models\City;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\modules\admin\models\search\CitySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Міста';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="city-index">

    <p>
        <?= Html::a('Додати місто', ['create'], ['class' => 'btn btn-info']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => ['class' => \yii\bootstrap4\LinkPager::class],
        'columns' => [

            'id',
            'name',
            'full_name',
            'sort_order',
            [
                'attribute' => 'status',
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'status',
                    ['Не активне', 'Активне'],
                    ['class'=>'form-control', 'prompt' => 'Всі статуси']
                ),
                'format' => 'html',
                'value' => fn ($ml) => $ml->status ? '<span class="badge bg-success">Активне</span>' : '<span class="badge bg-danger">Не активне</span>',
            ],
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, City $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
