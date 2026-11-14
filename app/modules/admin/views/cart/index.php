<?php

use app\modules\admin\models\Customer;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Кошики клієнтів';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cart-index">

    <p class="text-muted">
        Товари, які зареєстровані клієнти поклали в кошик, але ще не замовили.
        Гостьові кошики (без акаунта) тут не показуються.
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'pager' => ['class' => \yii\bootstrap4\LinkPager::class],
        'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
        'summary' => 'Показано <b>{begin}-{end}</b> з <b>{totalCount}</b> кошиків',
        'emptyText' => 'Зараз у жодного клієнта немає товарів у кошику.',
        'columns' => [
            [
                'label' => 'Клієнт',
                'format' => 'raw',
                'value' => fn(Customer $m) => Html::a(
                    Html::encode($m->getFullName() ?: $m->username),
                    ['/admin-panel/customer/view', 'id' => $m->id, '#' => 'cart']
                ) . '<div class="text-muted small">' . Html::encode($m->username) . '</div>',
            ],
            [
                'attribute' => 'phone',
                'format' => 'raw',
                'value' => fn(Customer $m) => $m->phone
                    ? Html::a('+' . Html::encode($m->phone), 'tel:+' . $m->phone)
                    : '—',
            ],
            [
                'attribute' => 'city_id',
                'value' => fn(Customer $m) => $m->city->name ?? '—',
            ],
            [
                'attribute' => 'cart_positions',
                'contentOptions' => ['class' => 'text-right'],
            ],
            [
                'attribute' => 'cart_qty',
                'contentOptions' => ['class' => 'text-right'],
            ],
            [
                'attribute' => 'cart_total',
                'contentOptions' => ['class' => 'text-right text-nowrap font-weight-bold'],
                'value' => fn(Customer $m) => (int)$m->cart_total . ' ₴',
            ],
            [
                'attribute' => 'cart_updated_at',
                'value' => fn(Customer $m) => date('d.m.Y H:i', strtotime($m->cart_updated_at)),
            ],
        ],
    ]); ?>

</div>
