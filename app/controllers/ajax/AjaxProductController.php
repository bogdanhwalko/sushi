<?php

namespace app\controllers\ajax;

use app\models\Categorys;
use app\models\Products;
use yii\filters\ContentNegotiator;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AjaxProductController extends Controller
{

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => \yii\web\Response::FORMAT_HTML,
            ],
        ];

        return $behaviors;
    }


    public function actionGetByFilters($categoryId = null)
    {
        if (empty($categoryId)) {
            $categoryId = Categorys::getActiveFirstId();
        }

        return $this->renderPartial('get-by-filters', [
            'products' => Products::findActive($categoryId),
        ]);
    }


    public function actionDetail(int $productId)
    {
        // вимкнені й видалені товари відвідувачам не показуємо
        $product = Products::findOne(['id' => $productId, 'status' => 1]);
        if ($product === null) {
            throw new NotFoundHttpException('Товар не знайдено або він більше не доступний.');
        }

        return $this->renderPartial('detail', [
            'product' => $product,
        ]);
    }
}