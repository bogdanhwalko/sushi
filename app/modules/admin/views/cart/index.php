<?php

use app\models\CartItems;
use app\modules\admin\controllers\CartController;
use app\modules\admin\models\Customer;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $tab customers|guests */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array<string|int, CartItems[]> $items товари кошиків поточної сторінки: власник → позиції */
/** @var array{customers: int, guests: int} $counts */

$this->title = 'Кошики клієнтів';
$this->params['breadcrumbs'][] = $this->title;

$guests = $tab === 'guests';

/** Список товарів кошика: «Назва × 2», недоступні позначені */
$itemsList = static function (array $cartItems): string {
    if (!$cartItems) {
        return '<span class="text-muted">—</span>';
    }

    $lines = [];
    foreach ($cartItems as $item) {
        /** @var CartItems $item */
        $line = Html::encode($item->product->name ?? ('Товар #' . $item->product_id))
            . ' <span class="text-muted">× ' . (int)$item->qty . '</span>';
        if ($item->product === null || (int)$item->product->status !== 1) {
            $line .= ' <span class="badge badge-secondary" title="Товар вимкнено — у замовлення не потрапить">недоступний</span>';
        }
        $lines[] = '<li>' . $line . '</li>';
    }

    return '<ul class="list-unstyled small mb-0 cart-items">' . implode('', $lines) . '</ul>';
};

$columns = [
    $guests
        ? [
            'label' => 'Гість',
            'format' => 'raw',
            'value' => static fn (array $row) => '<i class="fas fa-user-secret text-muted mr-1"></i>Гість '
                . Html::tag('code', Html::encode(substr($row['session_id'], 0, 8)), ['title' => 'Ідентифікатор кошика в браузері відвідувача'])
                . '<div class="text-muted small">перший товар ' . date('d.m.Y H:i', strtotime($row['cart_created_at'])) . '</div>',
        ]
        : [
            'label' => 'Клієнт',
            'format' => 'raw',
            'value' => static fn (Customer $m) => Html::a(
                Html::encode($m->getFullName() ?: $m->username),
                ['/admin-panel/customer/view', 'id' => $m->id, '#' => 'cart']
            ) . '<div class="text-muted small">' . Html::encode($m->username) . '</div>'
                . '<div class="small">' . ($m->phone ? Html::a('+' . Html::encode($m->phone), 'tel:+' . $m->phone) : '') . '</div>'
                . '<div class="text-muted small">' . Html::encode($m->city->name ?? '') . '</div>',
        ],
    [
        'label' => 'Товари',
        'format' => 'raw',
        'value' => static fn ($row) => $itemsList($items[$guests ? $row['session_id'] : $row->id] ?? []),
    ],
    [
        'attribute' => 'cart_qty',
        'label' => 'Штук',
        'contentOptions' => ['class' => 'text-right'],
        'value' => static fn ($row) => (int)$row['cart_qty'],
    ],
    [
        'attribute' => 'cart_total',
        'label' => 'Сума',
        'contentOptions' => ['class' => 'text-right text-nowrap font-weight-bold'],
        'value' => static fn ($row) => (int)$row['cart_total'] . ' ₴',
    ],
    [
        'attribute' => 'cart_updated_at',
        'label' => 'Остання зміна',
        'contentOptions' => ['class' => 'text-nowrap'],
        'value' => static fn ($row) => date('d.m.Y H:i', strtotime($row['cart_updated_at'])),
    ],
];
?>
<div class="cart-index">

    <ul class="nav nav-tabs mb-3">
        <?php foreach (['customers' => ['fas fa-user', 'Клієнти'], 'guests' => ['fas fa-user-secret', 'Гості (без реєстрації)']] as $key => [$icon, $label]): ?>
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

    <p class="text-muted">
        <?php if ($guests): ?>
            Товари, які відвідувачі без акаунта поклали в кошик, але ще не замовили.
            Контактів гостя ми не знаємо, доки він не оформить замовлення; якщо він увійде або зареєструється —
            кошик перейде в його акаунт (вкладка «Клієнти»).
            Кошики без змін понад <?= CartController::GUEST_CART_DAYS ?> днів з часом очищаються.
        <?php else: ?>
            Товари, які зареєстровані клієнти поклали в кошик, але ще не замовили.
        <?php endif; ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'pager' => ['class' => \yii\bootstrap4\LinkPager::class],
        'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
        'summary' => 'Показано <b>{begin}-{end}</b> з <b>{totalCount}</b> кошиків',
        'emptyText' => $guests ? 'Зараз у гостей немає товарів у кошику.' : 'Зараз у жодного клієнта немає товарів у кошику.',
        'columns' => $columns,
    ]); ?>

</div>
