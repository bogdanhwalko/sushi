<?php

use app\models\Orders;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string|null $status */

$this->title = 'Замовлення';
$this->params['breadcrumbs'][] = $this->title;

$statusBadge = [
    Orders::STATUS_NEW => 'badge-warning',
    Orders::STATUS_DONE => 'badge-success',
    Orders::STATUS_CANCELED => 'badge-secondary',
];
?>
<div class="order-index">

    <div class="mb-3">
        <?= Html::a('Всі', ['index'], ['class' => 'btn btn-sm ' . ($status === null || $status === '' ? 'btn-info' : 'btn-outline-info')]) ?>
        <?php foreach (Orders::statusLabels() as $value => $label): ?>
            <?= Html::a(Html::encode($label), ['index', 'status' => $value], [
                'class' => 'btn btn-sm ' . ((string)$status === (string)$value ? 'btn-info' : 'btn-outline-info'),
            ]) ?>
        <?php endforeach; ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'pager' => ['class' => \yii\bootstrap4\LinkPager::class],
        'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
        'columns' => [
            [
                'attribute' => 'id',
                'format' => 'raw',
                'value' => fn(Orders $m) => Html::a('№' . $m->id, ['view', 'id' => $m->id]),
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
        ],
    ]); ?>

</div>
