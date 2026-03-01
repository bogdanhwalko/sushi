<?php

namespace app\modules\admin\controllers\ajax;

use app\modules\admin\controllers\AbstractController;
use app\modules\admin\models\Config;
use app\modules\admin\models\Product;

class ProductController extends AbstractController
{
    public function actionProductWeek($product_id = null)
    {
        if (empty($product_id)) {
            $productWeekId = Config::get('product_week_id');
        }
        else {
            $productWeekId = $product_id;
            Config::set('product_week_id', $productWeekId);
        }

        $productWeek = $productWeekId ? Product::findOne($productWeekId) : null;

        return $this->renderPartial('product-week', [
            'productWeek' => $productWeek,
        ]);
    }
}