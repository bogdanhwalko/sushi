<?php

use app\modules\admin\models\TelegramGroup;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\modules\admin\models\search\TelegramGroupSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Групи Telegram';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="telegram-group-index">

    <p>
        <?= Html::a('Додати групу', ['create'], ['class' => 'btn btn-info']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [

            'id',
            'name',
            //'telegram_id',
            'description:ntext',
            [
                'attribute' => 'status',
                'value' => fn ($ml) => $ml->status ? '<span class="badge bg-success">Активна</span>' : '<span class="badge bg-danger">Не активна</span>',
                'format' => 'html',
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'status',
                    ['Не активна', 'Активна'],
                    ['class'=>'form-control', 'prompt' => 'Всі статуси']
                ),
            ],
            //'bot_id',
            'created_at',
            //'updated_at',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, TelegramGroup $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
