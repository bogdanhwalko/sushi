<?php

/** @var yii\web\View $this */
/** @var app\models\Orders $order */

use app\models\Orders;
use yii\helpers\Html;

$statusClass = [
    Orders::STATUS_NEW => 'text-bg-warning',
    Orders::STATUS_DONE => 'text-bg-success',
    Orders::STATUS_CANCELED => 'text-bg-secondary',
];
?>
<article class="account-order">
    <div class="account-order-head">
        <div class="min-w-0">
            <div class="fw-bold">Замовлення №<?= (int)$order->id ?></div>
            <div class="text-muted small"><?= date('d.m.Y, H:i', strtotime($order->created_at)) ?></div>
        </div>
        <span class="badge rounded-pill <?= $statusClass[$order->status] ?? 'text-bg-light' ?>">
            <?= Html::encode($order->getStatusLabel()) ?>
        </span>
    </div>

    <ul class="account-order-items list-unstyled mb-0">
        <?php foreach ($order->items as $item): ?>
            <li>
                <span class="account-order-name"><?= Html::encode($item->product_name) ?></span>
                <span class="text-muted text-nowrap"><?= (int)$item->qty ?> × <?= (int)$item->price ?> ₴</span>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="account-order-foot">
        <span class="text-muted small"><?= Html::encode($order->city_name ?? '—') ?></span>
        <span class="fw-bold text-nowrap">Разом: <?= (int)$order->total ?> ₴</span>
    </div>
</article>
