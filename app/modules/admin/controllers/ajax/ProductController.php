<?php

namespace app\modules\admin\controllers\ajax;

use app\modules\admin\controllers\AbstractController;
use app\modules\admin\models\Config;
use app\modules\admin\models\Product;
use Yii;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;

class ProductController extends AbstractController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'product-week' => ['GET'],
                    // зміна лише POST-запитом: для нього Yii перевіряє CSRF-токен
                    'set-product-week' => ['POST'],
                ],
            ],
        ]);
    }


    /** Картка поточного товару тижня (лише читання) */
    public function actionProductWeek()
    {
        $productWeekId = Config::get('product_week_id');

        return $this->renderProductWeek($productWeekId ? Product::findOne($productWeekId) : null);
    }


    public function actionSetProductWeek()
    {
        $productId = filter_var(Yii::$app->request->post('product_id'), FILTER_VALIDATE_INT);

        $product = $productId ? Product::findOne(['id' => $productId, 'status' => 1]) : null;
        if ($product === null) {
            throw new BadRequestHttpException('Оберіть активний товар.');
        }

        Config::set('product_week_id', (string)$product->id);

        return $this->renderProductWeek($product);
    }


    private function renderProductWeek(?Product $productWeek): string
    {
        return $this->renderPartial('product-week', [
            'productWeek' => $productWeek,
        ]);
    }
}
