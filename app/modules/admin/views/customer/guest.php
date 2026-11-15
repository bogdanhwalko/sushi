<?php

use app\models\Orders;
use app\modules\admin\models\Customer;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $phone */
/** @var string[] $names імена із замовлень, найновіше — першим */
/** @var string[] $cities міста із замовлень, найновіше — першим */
/** @var array{cnt: int, total: int, first: string, last: string} $stats */
/** @var Customer[] $accounts зареєстровані клієнти з таким самим номером */
/** @var yii\data\ActiveDataProvider $ordersProvider */

$this->title = $names[0] ?? ('+' . $phone);
$this->params['breadcrumbs'][] = ['label' => 'Клієнти', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Без реєстрації', 'url' => ['index', 'tab' => 'guests']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="customer-guest">
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-lightblue">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-secret mr-1"></i> Гість без реєстрації</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0 detail-view">
                        <tr>
                            <th>Телефон</th>
                            <td><?= Html::a('+' . Html::encode($phone), 'tel:+' . $phone) ?></td>
                        </tr>
                        <tr>
                            <th><?= count($names) > 1 ? 'Імена в замовленнях' : "Ім'я" ?></th>
                            <td><?= implode('<br>', array_map([Html::class, 'encode'], $names)) ?></td>
                        </tr>
                        <tr>
                            <th><?= count($cities) > 1 ? 'Міста' : 'Місто' ?></th>
                            <td><?= $cities ? implode('<br>', array_map([Html::class, 'encode'], $cities)) : '<span class="not-set">(не задано)</span>' ?></td>
                        </tr>
                        <tr>
                            <th>Перше замовлення</th>
                            <td><?= date('d.m.Y H:i', strtotime($stats['first'])) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer small text-muted">
                    <?php if ($accounts): ?>
                        З цим номером зареєстровано:
                        <?php foreach ($accounts as $i => $account): ?>
                            <?= $i ? ', ' : '' ?><?= Html::a(
                                Html::encode($account->getFullName() ?: $account->username),
                                ['view', 'id' => $account->id]
                            ) ?>
                        <?php endforeach; ?>.
                        Ці замовлення оформлені без входу в акаунт.
                    <?php else: ?>
                        Акаунта на сайті немає — гостя визначено за номером телефону із замовлень.
                    <?php endif; ?>
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
                            <h3><?= date('d.m.Y', strtotime($stats['last'])) ?></h3>
                            <p>Останнє замовлення</p>
                        </div>
                        <div class="icon"><i class="fas fa-clock"></i></div>
                    </div>
                </div>
            </div>

            <div class="card card-lightblue">
                <div class="card-header">
                    <h3 class="card-title">Замовлення гостя</h3>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $ordersProvider,
                        'pager' => ['class' => \yii\bootstrap4\LinkPager::class],
                        'layout' => "{items}\n<div class=\"px-3\">{pager}</div>",
                        'tableOptions' => ['class' => 'table table-striped mb-0'],
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
                            'customer_name',
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
