<?php

namespace app\modules\admin\controllers;


use app\modules\admin\models\Config;
use app\modules\admin\models\Product;

class DefaultController extends AbstractController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $productWeekId = Config::get('product_week_id');

        return $this->render('index', [
            'productWeekId' => $productWeekId,
        ]);
    }
}
