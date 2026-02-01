<?php

namespace app\controllers\ajax;

use Yii;
use app\models\Carts;
use yii\web\Response;
use yii\rest\Controller;
use app\models\Products;
use app\models\CartItems;
use yii\filters\ContentNegotiator;


class AjaxCartController extends Controller
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];

        return $behaviors;
    }


    public function actionAddProduct(int $product_id, string $session_id)
    {
        $result = [];

        $product = Products::find()
            ->where(['id' => $product_id])
            ->andWhere(['status' => 1])
            ->one();

        $result['product_name'] = $product->name;

        if (empty($product)) {
            $result['status'] = false;
            return $result;
        }

        $cart = Carts::findOne(['session_id' => $session_id]);
        if (empty($cart)) {
            $cart = new Carts();
            $cart->session_id = $session_id;
            $cart->save();
        }

        $cartItem = CartItems::find()
            ->where(['cart_id' => $cart->id])
            ->where(['product_id' => $product_id])
            ->one();

        if (empty($cartItem)) {
            $cartItem = new CartItems();
            $cartItem->product_id = $product_id;
            $cartItem->cart_id = $cart->id;
        }
        else {
            $cartItem->incrementQty();
        }

        $cartItem->price = $product->price;

        $result['status'] = $cartItem->save();

        return $result;
    }
}