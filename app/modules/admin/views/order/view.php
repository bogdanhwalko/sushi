<?php

use app\models\Orders;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var Orders $model */

$this->title = 'Замовлення №' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Замовлення', 'url' => ['index']];
$this->params['breadcrumbs'][] = '№' . $model->id;
?>
<div class="order-view">
    <div class="row">
        <div class="col-lg-5">
            <div class="card card-lightblue">
                <div class="card-header">
                    <h3 class="card-title">Дані замовлення</h3>
                </div>
                <div class="card-body p-0">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped mb-0 detail-view'],
                        'attributes' => [
                            [
                                'attribute' => 'created_at',
                                'value' => date('d.m.Y H:i', strtotime($model->created_at)),
                            ],
                            'customer_name',
                            [
                                'attribute' => 'phone',
                                'format' => 'raw',
                                'value' => Html::a('+' . Html::encode($model->phone), 'tel:+' . $model->phone),
                            ],
                            'city_name',
                            [
                                'attribute' => 'user_id',
                                'format' => 'raw',
                                'value' => $model->user
                                    ? Html::a(
                                        Html::encode($model->user->getFullName() ?: $model->user->username),
                                        $model->user->isAdmin() ? null : ['/admin-panel/customer/view', 'id' => $model->user_id]
                                    )
                                    : Html::a('Гість (без реєстрації)', ['/admin-panel/customer/guest', 'phone' => $model->phone]),
                            ],
                            [
                                'attribute' => 'telegram_sent',
                                'value' => match (true) {
                                    !$model->telegram_sent => 'Ні',
                                    !empty($model->telegram_message_id) => 'Так — статус у групі оновлюється разом із сайтом',
                                    default => 'Так',
                                },
                            ],
                            [
                                'attribute' => 'status',
                                'format' => 'raw',
                                'value' => Html::tag('span', Html::encode($model->getStatusLabel()), [
                                    'class' => 'badge badge-' . Orders::statusColor((int)$model->status),
                                ]),
                            ],
                            [
                                'label' => 'Прийняв',
                                'visible' => (bool)$model->accepted_by,
                                'value' => $model->accepted_by
                                    . ($model->accepted_at ? ', ' . date('d.m.Y H:i', strtotime($model->accepted_at)) : ''),
                            ],
                        ],
                    ]) ?>
                </div>
                <div class="card-footer">
                    <?= Html::beginForm(['status', 'id' => $model->id], 'post', ['class' => 'form-inline']) ?>
                    <?= Html::dropDownList('status', $model->status, Orders::statusLabels(), ['class' => 'form-control form-control-sm mr-2']) ?>
                    <?= Html::submitButton('Змінити статус', ['class' => 'btn btn-sm btn-info']) ?>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-lightblue">
                <div class="card-header">
                    <h3 class="card-title">Склад замовлення</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                        <tr>
                            <th>Товар</th>
                            <th class="text-right">Ціна</th>
                            <th class="text-right">К-сть</th>
                            <th class="text-right">Сума</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($model->items as $item): ?>
                            <tr>
                                <td><?= Html::encode($item->product_name) ?></td>
                                <td class="text-right text-nowrap"><?= (int)$item->price ?> ₴</td>
                                <td class="text-right"><?= (int)$item->qty ?></td>
                                <td class="text-right text-nowrap"><?= $item->getTotal() ?> ₴</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">Разом</th>
                            <th class="text-right text-nowrap"><?= (int)$model->total ?> ₴</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
