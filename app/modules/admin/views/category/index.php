<?php

use app\modules\admin\models\Category;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\modules\admin\models\search\CategorySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Категорії';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-index">
    <p>
        <?= Html::a('Додати категорію', ['create'], ['class' => 'btn btn-info']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => ['class' => \yii\bootstrap4\LinkPager::class],
        'columns' => [
            'id',
            'name',
            'sort_order',
            [
                'attribute' => 'status',
                'value' => fn ($ml) => $ml->status ? '<span class="badge bg-success">Активна</span>' : '<span class="badge bg-danger">Не активна</span>',
                'format' => 'html',
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'status',
                    ['Не активна', 'Активна'],
                    ['class'=>'form-control', 'prompt' => 'Всі категорії']
                ),
            ],
            'created_at',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Category $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
