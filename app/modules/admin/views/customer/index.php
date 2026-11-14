<?php

use app\models\Cities;
use app\modules\admin\models\Customer;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\admin\models\search\CustomerSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Клієнти';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-index">

    <div class="card card-lightblue">
        <div class="card-body pb-1">
            <?= Html::beginForm(['index'], 'get') ?>
            <div class="row">
                <div class="col-md-5 mb-2">
                    <?= Html::textInput('q', $searchModel->q, [
                        'class' => 'form-control',
                        'placeholder' => 'Логін, ПІБ, телефон або email',
                    ]) ?>
                </div>
                <div class="col-md-3 mb-2">
                    <?= Html::dropDownList('city_id', $searchModel->city_id, Cities::getCitiesInSelect(), [
                        'class' => 'form-control',
                        'prompt' => 'Всі міста',
                    ]) ?>
                </div>
                <div class="col-md-2 mb-2">
                    <?= Html::dropDownList('status', $searchModel->status, Customer::statusLabels(), [
                        'class' => 'form-control',
                        'prompt' => 'Всі статуси',
                    ]) ?>
                </div>
                <div class="col-md-2 mb-2 d-flex">
                    <?= Html::submitButton('<i class="fas fa-search"></i> Знайти', ['class' => 'btn btn-info flex-fill mr-1']) ?>
                    <?= Html::a('<i class="fas fa-times"></i>', ['index'], ['class' => 'btn btn-outline-secondary', 'title' => 'Скинути']) ?>
                </div>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'pager' => ['class' => \yii\bootstrap4\LinkPager::class],
        'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
        'summary' => 'Показано <b>{begin}-{end}</b> з <b>{totalCount}</b> клієнтів',
        'emptyText' => 'Клієнтів не знайдено.',
        'columns' => [
            'id',
            [
                'attribute' => 'username',
                'format' => 'raw',
                'value' => fn(Customer $m) => Html::a(Html::encode($m->username), ['view', 'id' => $m->id]),
            ],
            [
                'attribute' => 'fullName',
                'label' => 'ПІБ',
                'value' => fn(Customer $m) => $m->getFullName() ?: '—',
            ],
            [
                'attribute' => 'phone',
                'format' => 'raw',
                'value' => fn(Customer $m) => $m->phone
                    ? Html::a('+' . Html::encode($m->phone), 'tel:+' . $m->phone)
                    : '—',
            ],
            [
                'attribute' => 'email',
                'value' => fn(Customer $m) => $m->email ?: '—',
            ],
            [
                'attribute' => 'city_id',
                'value' => fn(Customer $m) => $m->city->name ?? '—',
            ],
            [
                'attribute' => 'orders_count',
                'contentOptions' => ['class' => 'text-right'],
            ],
            [
                'attribute' => 'orders_total',
                'contentOptions' => ['class' => 'text-right text-nowrap'],
                'value' => fn(Customer $m) => (int)$m->orders_total . ' ₴',
            ],
            [
                'attribute' => 'created_at',
                'value' => fn(Customer $m) => date('d.m.Y', strtotime($m->created_at)),
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => fn(Customer $m) => (int)$m->status === Customer::STATUS_ACTIVE
                    ? '<span class="badge badge-success">Активний</span>'
                    : '<span class="badge badge-danger">Заблокований</span>',
            ],
        ],
    ]); ?>

</div>
