<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * @property int $id
 * @property string $session_id
 * @property int $product_id
 * @property int $qty
 * @property float $price
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Products $product
 */
class CartItems extends ActiveRecord
{

    public static function tableName(): string
    {
        return 'cart_item';
    }


    public function rules(): array
    {
        return [
            [['session_id', 'product_id', 'price'], 'required'],

            ['product_id', 'integer'],
            ['qty', 'default', 'value' => 1],
            ['qty', 'integer', 'min' => 1],

            ['session_id', 'string', 'max' => 64],

            ['price', 'number'],
            [
                ['session_id', 'product_id'], 'unique',
                'targetAttribute' => ['session_id', 'product_id'],
                'when' => fn($ml) => $ml->isNewRecord
            ],
        ];
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('CURRENT_TIMESTAMP'),
            ],
        ];
    }


    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'session_id' => 'Сесія користувача',
            'product_id' => 'Товар',
            'qty' => 'Кількість',
            'price' => 'Ціна за одиницю',
            'created_at' => 'Додано',
            'updated_at' => 'Оновлено',
        ];
    }

    /**
     * Зв’язок з корзиною
     */
    public function getCart()
    {
        return $this->hasOne(Carts::class, ['session_id' => 'session_id']);
    }

    /**
     * Зв’язок з товаром
     */
    public function getProduct()
    {
        return $this->hasOne(Products::class, ['id' => 'product_id']);
    }

    /**
     * Вартість позиції (qty * price)
     */
    public function getTotal(): float
    {
        return (float)$this->qty * (float)$this->price;
    }

    /**
     * Збільшити кількість
     */
    public function incrementQty(int $by = 1): void
    {
        $this->qty = min($this->qty + $by, 100);
    }


    public function decrementQty(int $by = 1): void
    {
        $this->qty = max($this->qty - $by, 1);
    }
}
