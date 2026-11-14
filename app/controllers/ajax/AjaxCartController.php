<?php

namespace app\controllers\ajax;

use app\forms\CartUpdateForm;
use app\forms\OrderForm;
use Yii;
use yii\web\Response;
use yii\rest\Controller;
use app\models\CartItems;
use yii\filters\ContentNegotiator;


class AjaxCartController extends Controller
{
    /** усі дії, що змінюють кошик, — лише POST з CSRF-токеном */
    public $enableCsrfValidation = true;


    protected function verbs(): array
    {
        return [
            'cart' => ['GET'],
            'add-product' => ['POST'],
            'change-qty' => ['POST'],
            'delete-item' => ['POST'],
            'clear-cart' => ['POST'],
            'confirm' => ['POST'],
        ];
    }


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

        CartItems::ensureSessionId();

        return true;
    }


    public function actionCart()
    {
        Yii::$app->response->format = Response::FORMAT_HTML;

        $cartItems = CartItems::findCurrent()
            ->with('product')
            ->orderBy(['id' => SORT_ASC])
            ->all();

        return $this->renderPartial('cart', [
            'cartItems' => $cartItems,
        ]);
    }



    public function actionAddProduct()
    {
        $request = Yii::$app->request;

        $form = new CartUpdateForm();
        $form->product_id = $this->postInt('product_id');
        $form->qtyStatus = filter_var($request->post('qtyStatus', true), FILTER_VALIDATE_BOOLEAN);

        $form->validate() && $form->cartItemUpdate();

        return ['errors' => $form->getErrorSummary(true)];
    }


    /**
     * Змінює кількість лише в кошику поточного відвідувача.
     * @return int кількість оновлених рядків (0 — позицію не знайдено або кількість поза межами)
     */
    public function actionChangeQty()
    {
        $itemId = $this->postInt('item_id');
        $qty = $this->postInt('qty');

        if ($itemId === null || $qty === null || $qty < 1 || $qty > CartItems::MAX_QTY) {
            return 0;
        }

        return CartItems::updateAll(
            ['qty' => $qty, 'updated_at' => new \yii\db\Expression('CURRENT_TIMESTAMP')],
            ['and', ['id' => $itemId], CartItems::ownerCondition()]
        );
    }


    public function actionDeleteItem()
    {
        $result = ['status' => false];

        $itemId = $this->postInt('item_id');
        if ($itemId === null) {
            return $result;
        }

        $cartItem = CartItems::findCurrent()->andWhere(['id' => $itemId])->one();
        if ($cartItem !== null) {
            $result['qty'] = $cartItem->qty;
            $result['status'] = $cartItem->delete();
        }

        return $result;
    }


    public function actionClearCart()
    {
        return CartItems::clearCurrent();
    }


    public function actionConfirm()
    {
        $form = new OrderForm();
        $form->load(Yii::$app->request->post(), '');

        $order = $form->place();
        if ($order === null) {
            return ['success' => false, 'errors' => $form->getErrorSummary(true)];
        }

        return ['success' => true, 'orderId' => $order->id];
    }


    /** Ціле число з тіла POST-запиту або null, якщо його немає чи це не число */
    private function postInt(string $name): ?int
    {
        $value = filter_var(Yii::$app->request->post($name), FILTER_VALIDATE_INT);

        return $value === false ? null : $value;
    }
}