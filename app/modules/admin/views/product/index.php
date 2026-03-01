<?php

use app\modules\admin\models\Product;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\modules\admin\models\search\ProductSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Товари';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-index">

    <p>
        <?= Html::a('Додати товар', ['create'], ['class' => 'btn btn-info']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => ['class' => \yii\bootstrap4\LinkPager::class],
        'columns' => [

            'id',
            'name',
            [
                'attribute' => 'category_id',
                'value' => fn ($ml) => $ml->category->name,
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'category_id',
                    \app\modules\admin\models\Category::getBySelect(),
                    ['class'=>'form-control', 'prompt' => 'Всі Категорії']
                ),
            ],
            'description:ntext',
            //'short_description',
            //'price',
            //'old_price',
            //'currency',
            //'weight',
            //'image',
            //'meta_title',
            //'meta_description',
            [
                'attribute' => 'status',
                'value' => fn ($ml) => $ml->status ? '<span class="badge bg-success">Активний</span>' : '<span class="badge bg-danger">Не активний</span>',
                'format' => 'html',
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'status',
                    ['Не активний', 'Активний'],
                    ['class'=>'form-control', 'prompt' => 'Всі статуси']
                ),
            ],
            //'sort_order',
            //'created_at',
            //'updated_at',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Product $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
