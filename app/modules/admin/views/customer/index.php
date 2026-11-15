<?php

use app\models\Cities;
use app\modules\admin\models\Customer;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $tab customers|guests */
/** @var app\modules\admin\models\search\CustomerSearch|app\modules\admin\models\search\GuestCustomerSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array{customers: int, guests: int} $counts */

$this->title = 'Клієнти';
$this->params['breadcrumbs'][] = $this->title;

$guests = $tab === 'guests';

$phoneLink = static fn (?string $phone) => $phone
    ? Html::a('+' . Html::encode($phone), 'tel:+' . $phone, ['class' => 'text-nowrap'])
    : '—';

$columns = $guests
    ? [
        [
            'attribute' => 'customer_name',
            'label' => "Ім'я",
            'format' => 'raw',
            'value' => static fn (array $row) => Html::a(Html::encode($row['customer_name']), ['guest', 'phone' => $row['phone']])
                . ($row['accounts'] ? ' <span class="badge badge-info" title="З таким номером є зареєстрований клієнт">є акаунт</span>' : ''),
        ],
        [
            'attribute' => 'phone',
            'label' => 'Телефон',
            'format' => 'raw',
            'value' => static fn (array $row) => $phoneLink($row['phone']),
        ],
        [
            'label' => 'Місто',
            'value' => static fn (array $row) => $row['city_name'] ?: '—',
        ],
        [
            'attribute' => 'orders_count',
            'label' => 'Замовлень',
            'contentOptions' => ['class' => 'text-right'],
        ],
        [
            'attribute' => 'orders_total',
            'label' => 'Сума замовлень',
            'contentOptions' => ['class' => 'text-right text-nowrap'],
            'value' => static fn (array $row) => (int)$row['orders_total'] . ' ₴',
        ],
        [
            'attribute' => 'first_order_at',
            'label' => 'Перше замовлення',
            'value' => static fn (array $row) => date('d.m.Y', strtotime($row['first_order_at'])),
        ],
        [
            'attribute' => 'last_order_at',
            'label' => 'Останнє замовлення',
            'value' => static fn (array $row) => date('d.m.Y H:i', strtotime($row['last_order_at'])),
        ],
    ]
    : [
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
            'value' => fn(Customer $m) => $phoneLink($m->phone),
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
    ];
?>
<div class="customer-index">

    <ul class="nav nav-tabs mb-3">
        <?php foreach (['customers' => ['fas fa-user', 'Зареєстровані'], 'guests' => ['fas fa-user-secret', 'Без реєстрації']] as $key => [$icon, $label]): ?>
            <li class="nav-item">
                <?= Html::a(
                    Html::tag('i', '', ['class' => $icon . ' mr-1']) . $label
                    . ' <span class="badge ' . ($tab === $key ? 'badge-primary' : 'badge-secondary') . '">' . $counts[$key] . '</span>',
                    ['index', 'tab' => $key],
                    ['class' => 'nav-link' . ($tab === $key ? ' active' : '')]
                ) ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($guests): ?>
        <p class="text-muted">
            Ті, хто оформлював замовлення без входу в акаунт. Гостя визначаємо за номером телефону:
            усі його замовлення з цього номера зібрані в одній картці, ім'я та місто — з останнього замовлення.
            Позначка «є акаунт» — з таким номером є й зареєстрований клієнт (можливо, він просто не увійшов).
        </p>
    <?php endif; ?>

    <div class="card card-lightblue">
        <div class="card-body pb-1">
            <?= Html::beginForm(['index'], 'get') ?>
            <?= $guests ? Html::hiddenInput('tab', 'guests') : '' ?>
            <div class="row">
                <div class="<?= $guests ? 'col-md-7' : 'col-md-5' ?> mb-2">
                    <?= Html::textInput('q', $searchModel->q, [
                        'class' => 'form-control',
                        'placeholder' => $guests ? "Ім'я або телефон" : 'Логін, ПІБ, телефон або email',
                    ]) ?>
                </div>
                <div class="col-md-3 mb-2">
                    <?= Html::dropDownList('city_id', $searchModel->city_id, Cities::getCitiesInSelect(), [
                        'class' => 'form-control',
                        'prompt' => 'Всі міста',
                    ]) ?>
                </div>
                <?php if (!$guests): ?>
                    <div class="col-md-2 mb-2">
                        <?= Html::dropDownList('status', $searchModel->status, Customer::statusLabels(), [
                            'class' => 'form-control',
                            'prompt' => 'Всі статуси',
                        ]) ?>
                    </div>
                <?php endif; ?>
                <div class="col-md-2 mb-2 d-flex">
                    <?= Html::submitButton('<i class="fas fa-search"></i> Знайти', ['class' => 'btn btn-info flex-fill mr-1']) ?>
                    <?= Html::a('<i class="fas fa-times"></i>', ['index', 'tab' => $guests ? 'guests' : null], ['class' => 'btn btn-outline-secondary', 'title' => 'Скинути']) ?>
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
        'emptyText' => $guests ? 'Замовлень без реєстрації не знайдено.' : 'Клієнтів не знайдено.',
        'columns' => $columns,
    ]); ?>

</div>
