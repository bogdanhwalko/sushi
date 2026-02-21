<?php

namespace app\forms;

use app\models\CartItems;
use app\models\Products;
use yii\base\Model;
use yii\db\Expression;
use yii\db\Query;

class CartUpdateForm extends Model
{
    public ?int $product_id = null;

    public bool $qtyStatus = true;



    public ?CartItems $cartItem = null;

    private ?Products $product = null;

    private bool $isUpdate = true;


    public function rules(): array
    {
        return [
            ['product_id', 'required'],
            ['product_id', 'integer'],
            ['qtyStatus', 'boolean'],
            ['product_id', 'productExistsValidator'],
        ];
    }


    public function productExistsValidator(): bool
    {
        $this->product = Products::find()
            ->where(['id' => $this->product_id])
            ->andWhere(['status' => 1])
            ->one();

        if (empty($this->product)) {
            $this->addError('product_id', 'Товар з вказаним ID не існує.');
            return false;
        }

        return true;
    }


    public function cartItemUpdate(): bool
    {
        $this->cartItem = CartItems::findOne([
            'session_id' => \Yii::$app->session->get('session_id'),
            'product_id' => $this->product->id,
        ]);

        if (empty($this->cartItem)) {
            $this->cartItem = new CartItems();
            $this->cartItem->product_id = $this->product->id;
            $this->cartItem->session_id = \Yii::$app->session->get('session_id');
        }

        $this->cartItem->price = $this->product->price;
        $this->qtyStatus ? $this->cartItem->incrementQty() : $this->cartItem->decrementQty();

        $this->cartItem->populateRelation('product', $this->product);

        return $this->cartItem->save();
    }


    public static function getTotalPrice(): int
    {
        return (new Query())
            ->from(CartItems::tableName())
            ->where(['session_id' => \Yii::$app->session->get('session_id')])
            ->sum(new Expression('price * qty'));
    }


    public function getProduct(): Products
    {
        return $this->product;
    }


}