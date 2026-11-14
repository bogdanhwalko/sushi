<?php

namespace app\modules\admin\models;

use app\models\User;

/**
 * Клієнт у списку адмін-панелі (з агрегатами по замовленнях).
 */
class Customer extends User
{
    /** Кількість замовлень (заповнюється в CustomerSearch) */
    public $orders_count;

    /** Сума всіх замовлень, ₴ (заповнюється в CustomerSearch) */
    public $orders_total;

    /** Кошик: кількість позицій, штук, сума та час останньої зміни (заповнюється в CartController) */
    public $cart_positions;
    public $cart_qty;
    public $cart_total;
    public $cart_updated_at;


    public static function statusLabels(): array
    {
        return [
            self::STATUS_ACTIVE => 'Активний',
            self::STATUS_BLOCKED => 'Заблокований',
        ];
    }


    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'fullName' => 'ПІБ',
            'orders_count' => 'Замовлень',
            'orders_total' => 'Сума замовлень',
            'cart_positions' => 'Позицій',
            'cart_qty' => 'Штук',
            'cart_total' => 'Сума кошика',
            'cart_updated_at' => 'Остання зміна',
        ]);
    }


    /** Поточний кошик клієнта */
    public function getCartItems(): \yii\db\ActiveQuery
    {
        return $this->hasMany(\app\models\CartItems::class, ['user_id' => 'id'])
            ->orderBy(['updated_at' => SORT_DESC, 'id' => SORT_DESC]);
    }
}
