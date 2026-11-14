<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Позиція замовлення. Назва та ціна зберігаються на момент замовлення,
 * тож історія не зміниться, якщо товар пізніше відредагують або видалять.
 *
 * @property int $id
 * @property int $order_id
 * @property int|null $product_id
 * @property string $product_name
 * @property int $price
 * @property int $qty
 *
 * @property Orders $order
 * @property Products|null $product
 */
class OrderItems extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%order_item}}';
    }


    public function rules(): array
    {
        return [
            [['order_id', 'product_name', 'price', 'qty'], 'required'],
            [['order_id', 'product_id', 'price'], 'integer'],
            ['qty', 'integer', 'min' => 1],
            ['product_name', 'string', 'max' => 255],
        ];
    }


    public function attributeLabels(): array
    {
        return [
            'product_name' => 'Товар',
            'price' => 'Ціна',
            'qty' => 'Кількість',
        ];
    }


    public function getOrder(): ActiveQuery
    {
        return $this->hasOne(Orders::class, ['id' => 'order_id']);
    }


    public function getProduct(): ActiveQuery
    {
        return $this->hasOne(Products::class, ['id' => 'product_id']);
    }


    public function getTotal(): int
    {
        return (int)$this->price * (int)$this->qty;
    }
}
