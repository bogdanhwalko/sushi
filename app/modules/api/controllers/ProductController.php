<?php

namespace app\modules\api\controllers;

use app\modules\api\models\Product;

class ProductController extends ApiController
{
    public function actionBySelect($term = null)
    {
        $query = Product::find()
            ->select(['id', 'name as text'])
            ->where(['status' => 1])
            ->limit(20)
            ->orderBy(['id' => SORT_DESC])
            ->asArray();

        if (is_numeric($term)) {
            $query->andWhere(['id' => $term]);
        }
        elseif ($term && mb_strlen($term) > 2) {
            $query->andWhere(['like', 'name', $term]);
        }

        return ['results' => $query->all()];
    }
}