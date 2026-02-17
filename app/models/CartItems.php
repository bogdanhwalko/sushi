<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * @property int $id
 * @property int $cart_id
 * @property int $product_id
 * @property int $qty
 * @property float $price
 * @property string $created_at
 * @property string $updated_at
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
            [['cart_id', 'product_id', 'price'], 'required'],

            [['cart_id', 'product_id'], 'integer'],
            ['qty', 'default', 'value' => 1],
            ['qty', 'integer', 'min' => 1],

            ['price', 'number'],
            [['cart_id', 'product_id'], 'unique', 'targetAttribute' => ['cart_id', 'product_id']],
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
            'cart_id' => 'Корзина',
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
        return $this->hasOne(Carts::class, ['id' => 'cart_id']);
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
    public function incrementQty(int $by = 1)
    {
        $this->qty += $by;
    }
}
