<?php

namespace app\controllers\ajax;

use app\forms\CartUpdateForm;
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


    public function beforeAction($action): bool
    {

        if (! parent::beforeAction($action)) {
            return false;
        }

        if (Yii::$app->session->get('session_id')) {
            return true;
        }

        GENERATE_SESSION_ID: $session_id = Yii::$app->security->generateRandomString();
        if (Carts::findOne(['session_id' => $session_id])) {
            goto GENERATE_SESSION_ID;
        }

        Yii::$app->session->set('session_id', $session_id);

        return true;
    }


    public function actionCart()
    {
        Yii::$app->response->format = Response::FORMAT_HTML;

        $cartItems = CartItems::find()
            ->where(['session_id' => Yii::$app->session->get('session_id')])
            ->all();

        return $this->renderPartial('cart', [
            'cartItems' => $cartItems,
        ]);
    }



    public function actionAddProduct(int $product_id, bool $qtyStatus = true)
    {
        $form = new CartUpdateForm();
        $form->product_id = $product_id;
        $form->qtyStatus = $qtyStatus;

        $form->validate() && $form->cartItemUpdate();

        return ['errors' => $form->getErrorSummary(true)];
    }


    public function actionChangeQty(int $item_id, int $qty)
    {
        return CartItems::updateAll(['qty' => $qty], ['id' => $item_id]);
    }


    public function actionDeleteItem(int $item_id)
    {
        $result = ['status' => false];

        $cartItem = CartItems::findOne(['id' => $item_id, 'session_id' => Yii::$app->session->get('session_id')]);
        if ($cartItem !== null) {
            $result['status'] = $cartItem->delete();
        }

        return $result;
    }


    public function actionClearCart()
    {
        return CartItems::deleteAll(['session_id' => Yii::$app->session->get('session_id')]);
    }


    public function actionConfirm($phone, $name)
    {
        $items = CartItems::findAll(['session_id' => Yii::$app->session->get('session_id')]);

        $total = 0;
        $message = "\xe2\x98\x8e <b>$phone</b> ($name) \n\n";
        foreach ($items as $item) {
            $total += ($item->price * $item->qty);
            $message .= "\xf0\x9f\x8d\xa3 {$item->product->name} {$item->qty}шт. <b>{$item->price}</b>₴ \n";
        }

        $message .= "\n \xf0\x9f\x92\xb3 Загальна сума замовлення: <b>{$total}</b>₴";

        CartItems::deleteAll(['session_id' => Yii::$app->session->get('session_id')]);

        return Yii::$app->ts->sendMessage($message);
    }
}