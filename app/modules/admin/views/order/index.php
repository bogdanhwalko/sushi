<?php

use app\models\Orders;
use app\modules\admin\controllers\OrderController;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string|null $status */
/** @var string|null $city id міста або OrderController::CITY_NONE */
/** @var array<int, string> $cities id → назва */
/** @var array<string, int> $cityCounts id міста (або CITY_NONE) → кількість замовлень з вибраним статусом */

$this->title = 'Замовлення';
$this->params['breadcrumbs'][] = $this->title;

$statusBadge = [
    Orders::STATUS_NEW => 'badge-warning',
    Orders::STATUS_DONE => 'badge-success',
    Orders::STATUS_CANCELED => 'badge-secondary',
];
$this->registerJs(<<<'JS'
document.getElementById('order-city-filter').addEventListener('change', function () {
    const url = new URL(this.dataset.url, location.href);
    if (this.value) url.searchParams.set('city', this.value);
    location.href = url.toString();
});
JS);
?>
<div class="order-index">

    <div class="d-flex flex-wrap align-items-center mb-3" style="gap: .5rem 1.5rem">
        <div>
            <?= Html::a('Всі', ['index', 'city' => $city], ['class' => 'btn btn-sm ' . ($status === null ? 'btn-info' : 'btn-outline-info')]) ?>
            <?php foreach (Orders::statusLabels() as $value => $label): ?>
                <?= Html::a(Html::encode($label), ['index', 'status' => $value, 'city' => $city], [
                    'class' => 'btn btn-sm ' . ($status === (string)$value ? 'btn-info' : 'btn-outline-info'),
                ]) ?>
            <?php endforeach; ?>
        </div>

        <?php
        // місто: усі міста з кількістю замовлень; «без міста» — лише якщо такі замовлення є
        $cityOptions = ['' => 'Усі міста (' . array_sum($cityCounts) . ')'];
        foreach ($cities as $id => $name) {
            $cityOptions[$id] = $name . ' (' . ($cityCounts[$id] ?? 0) . ')';
        }
        if (!empty($cityCounts[OrderController::CITY_NONE]) || $city === OrderController::CITY_NONE) {
            $cityOptions[OrderController::CITY_NONE] = 'Без міста / видалене (' . ($cityCounts[OrderController::CITY_NONE] ?? 0) . ')';
        }
        ?>
        <div class="d-flex align-items-center">
            <label for="order-city-filter" class="mb-0 mr-2 text-nowrap"><i class="fas fa-city text-muted mr-1"></i>Місто</label>
            <?= Html::dropDownList('city', $city ?? '', $cityOptions, [
                'id' => 'order-city-filter',
                'class' => 'custom-select custom-select-sm' . ($city !== null ? ' border-info' : ''),
                'style' => 'min-width: 200px',
                // перехід одразу при виборі; статус зберігається
                'data-url' => Url::to(['index', 'status' => $status]),
            ]) ?>
            <?php if ($city !== null): ?>
                <?= Html::a('<i class="fas fa-times"></i>', ['index', 'status' => $status], ['class' => 'btn btn-sm btn-link text-muted', 'title' => 'Усі міста']) ?>
            <?php endif; ?>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'pager' => ['class' => \yii\bootstrap4\LinkPager::class],
        'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
        'emptyText' => 'Замовлень за цими фільтрами немає.',
        'columns' => [
            [
                'attribute' => 'id',
                'value' => fn(Orders $m) => '№' . $m->id,
                'contentOptions' => ['class' => 'text-nowrap'],
            ],
            [
                'attribute' => 'created_at',
                'value' => fn(Orders $m) => date('d.m.Y H:i', strtotime($m->created_at)),
            ],
            'customer_name',
            [
                'attribute' => 'phone',
                'value' => fn(Orders $m) => '+' . $m->phone,
            ],
            'city_name',
            [
                'attribute' => 'user_id',
                'format' => 'raw',
                'value' => fn(Orders $m) => match (true) {
                    $m->user === null => 'Гість',
                    $m->user->isAdmin() => Html::encode($m->user->username),
                    default => Html::a(Html::encode($m->user->username), ['/admin-panel/customer/view', 'id' => $m->user_id]),
                },
            ],
            [
                'attribute' => 'total',
                'value' => fn(Orders $m) => $m->total . ' ₴',
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => fn(Orders $m) => Html::tag('span', Html::encode($m->getStatusLabel()), [
                    'class' => 'badge ' . ($statusBadge[$m->status] ?? 'badge-light'),
                ]),
            ],
            [
                'attribute' => 'telegram_sent',
                'format' => 'raw',
                'value' => fn(Orders $m) => $m->telegram_sent
                    ? '<span class="text-success"><i class="fas fa-check"></i></span>'
                    : '<span class="badge badge-danger" title="Сповіщення в Telegram не надіслано">не надіслано</span>',
            ],
            [
                'header' => '',
                'format' => 'raw',
                'contentOptions' => ['class' => 'text-right text-nowrap align-middle'],
                'value' => fn(Orders $m) => Html::a('<i class="fas fa-eye mr-1"></i>Деталі', ['view', 'id' => $m->id], [
                    'class' => 'btn btn-sm btn-info',
                    'title' => 'Замовлення №' . $m->id,
                ]),
            ],
        ],
    ]); ?>

</div>
