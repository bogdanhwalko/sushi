<?php

use app\models\Orders;
use app\modules\admin\models\Customer;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var Customer $model */
/** @var yii\data\ActiveDataProvider $ordersProvider */
/** @var array $stats */
/** @var app\models\CartItems[] $cartItems */

$cartTotal = array_sum(array_map(fn($item) => $item->getTotal(), $cartItems));
$cartUpdated = $cartItems ? max(array_map(fn($item) => strtotime($item->updated_at), $cartItems)) : null;

$this->title = $model->getFullName() ?: $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Клієнти', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$isActive = (int)$model->status === Customer::STATUS_ACTIVE;

?>
<div class="customer-view">
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-lightblue">
                <div class="card-header">
                    <h3 class="card-title">Дані клієнта</h3>
                </div>
                <div class="card-body p-0">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped mb-0 detail-view'],
                        'attributes' => [
                            'username',
                            'last_name',
                            'first_name',
                            'middle_name',
                            [
                                'attribute' => 'phone',
                                'format' => 'raw',
                                'value' => $model->phone
                                    ? Html::a('+' . Html::encode($model->phone), 'tel:+' . $model->phone)
                                    : null,
                            ],
                            [
                                'attribute' => 'email',
                                'format' => 'raw',
                                'value' => $model->email
                                    ? Html::mailto(Html::encode($model->email), $model->email)
                                    : null,
                            ],
                            [
                                'attribute' => 'city_id',
                                'value' => $model->city->name ?? null,
                            ],
                            [
                                'attribute' => 'created_at',
                                'value' => date('d.m.Y H:i', strtotime($model->created_at)),
                            ],
                            [
                                'attribute' => 'status',
                                'format' => 'raw',
                                'value' => $isActive
                                    ? '<span class="badge badge-success">Активний</span>'
                                    : '<span class="badge badge-danger">Заблокований</span>',
                            ],
                        ],
                    ]) ?>
                </div>
                <div class="card-footer">
                    <?= Html::beginForm(['status', 'id' => $model->id], 'post') ?>
                    <?= Html::hiddenInput('status', $isActive ? Customer::STATUS_BLOCKED : Customer::STATUS_ACTIVE) ?>
                    <?= Html::submitButton(
                        $isActive ? '<i class="fas fa-ban"></i> Заблокувати' : '<i class="fas fa-check"></i> Розблокувати',
                        [
                            'class' => 'btn btn-sm ' . ($isActive ? 'btn-outline-danger' : 'btn-success'),
                            'data-confirm' => $isActive
                                ? 'Заблокувати клієнта? Він не зможе увійти в кабінет.'
                                : null,
                        ]
                    ) ?>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row">
                <div class="col-sm-4">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= (int)$stats['cnt'] ?></h3>
                            <p>Замовлень</p>
                        </div>
                        <div class="icon"><i class="fas fa-receipt"></i></div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= (int)$stats['total'] ?> ₴</h3>
                            <p>Загальна сума</p>
                        </div>
                        <div class="icon"><i class="fas fa-wallet"></i></div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3><?= $stats['last'] ? date('d.m.Y', strtotime($stats['last'])) : '—' ?></h3>
                            <p>Останнє замовлення</p>
                        </div>
                        <div class="icon"><i class="fas fa-clock"></i></div>
                    </div>
                </div>
            </div>

            <div class="card card-lightblue" id="cart">
                <div class="card-header">
                    <h3 class="card-title">Кошик</h3>
                    <?php if ($cartUpdated): ?>
                        <div class="card-tools small">оновлено <?= date('d.m.Y H:i', $cartUpdated) ?></div>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($cartItems)): ?>
                        <p class="text-muted p-3 mb-0">Кошик порожній.</p>
                    <?php else: ?>
                        <table class="table table-striped mb-0">
                            <thead>
                            <tr>
                                <th>Товар</th>
                                <th class="text-right">Ціна</th>
                                <th class="text-right">К-сть</th>
                                <th class="text-right">Сума</th>
                                <th>Додано</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($cartItems as $item): ?>
                                <tr>
                                    <td>
                                        <?= Html::encode($item->product->name ?? ('Товар #' . $item->product_id)) ?>
                                        <?php if ($item->product === null || (int)$item->product->status !== 1): ?>
                                            <span class="badge badge-secondary ml-1" title="Товар вимкнено — у замовлення не потрапить">недоступний</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right text-nowrap"><?= (int)($item->product->price ?? $item->price) ?> ₴</td>
                                    <td class="text-right"><?= (int)$item->qty ?></td>
                                    <td class="text-right text-nowrap"><?= $item->getTotal() ?> ₴</td>
                                    <td class="text-nowrap text-muted small"><?= date('d.m.Y H:i', strtotime($item->created_at)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Разом</th>
                                <th class="text-right text-nowrap"><?= $cartTotal ?> ₴</th>
                                <th></th>
                            </tr>
                            </tfoot>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card card-lightblue">
                <div class="card-header">
                    <h3 class="card-title">Замовлення клієнта</h3>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $ordersProvider,
                        'pager' => ['class' => \yii\bootstrap4\LinkPager::class],
                        'layout' => "{items}\n<div class=\"px-3\">{pager}</div>",
                        'tableOptions' => ['class' => 'table table-striped mb-0'],
                        'emptyText' => 'Клієнт ще нічого не замовляв.',
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'format' => 'raw',
                                'value' => fn(Orders $o) => Html::a('№' . $o->id, ['/admin-panel/order/view', 'id' => $o->id]),
                            ],
                            [
                                'attribute' => 'created_at',
                                'value' => fn(Orders $o) => date('d.m.Y H:i', strtotime($o->created_at)),
                            ],
                            'city_name',
                            [
                                'attribute' => 'total',
                                'value' => fn(Orders $o) => $o->total . ' ₴',
                            ],
                            [
                                'attribute' => 'status',
                                'format' => 'raw',
                                'value' => fn(Orders $o) => Html::tag('span', Html::encode($o->getStatusLabel()), [
                                    'class' => 'badge badge-' . Orders::statusColor((int)$o->status),
                                ]),
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
