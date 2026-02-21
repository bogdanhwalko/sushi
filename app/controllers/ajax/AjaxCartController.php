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
}