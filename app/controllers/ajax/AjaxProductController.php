<?php

namespace app\controllers\ajax;

use app\models\Products;
use yii\filters\ContentNegotiator;
use yii\rest\Controller;
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


    public function actionGetByFilters(string $categoryId = null)
    {
        return $this->renderPartial('get-by-filters', [
            'products' => Products::findActive($categoryId),
        ]);
    }


    public function actionDetail(int $productId)
    {
        return $this->renderPartial('detail', [
            'product' => Products::findOne($productId),
        ]);
    }
}