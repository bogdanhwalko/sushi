<?php

namespace app\forms;

use app\models\CartItems;
use app\models\Products;
use yii\base\Model;

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
        $this->cartItem = CartItems::findCurrent()
            ->andWhere(['product_id' => $this->product->id])
            ->one();

        if (empty($this->cartItem)) {
            $this->cartItem = CartItems::newForCurrent();
            $this->cartItem->product_id = $this->product->id;
        }

        $this->cartItem->price = $this->product->price;
        $this->qtyStatus ? $this->cartItem->incrementQty() : $this->cartItem->decrementQty();

        $this->cartItem->populateRelation('product', $this->product);

        return $this->cartItem->save();
    }


    public function getProduct(): Products
    {
        return $this->product;
    }


}