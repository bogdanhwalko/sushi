<?php

namespace app\controllers;

use app\models\CartItems;
use app\models\Categorys;
use app\models\Cities;
use app\models\Configs;
use app\models\Products;
use Yii;
use yii\web\Controller;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }


    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $this->prepareCommonViewParams(true);

        return $this->render('index');
    }


    public function actionMenu()
    {
        $this->layout = 'compact-menu';
        $this->prepareCommonViewParams();

        $categories = Categorys::getActive(1);

        return $this->render('menu', ['categories' => $categories]);
    }


    private function prepareCommonViewParams(bool $withProductOfWeek = false): void
    {
        $this->view->params['totalCount'] = CartItems::getTotalCount();

        // вимкнений товар тижня не показуємо — його все одно не можна додати в кошик
        $productWeekId = $withProductOfWeek ? Configs::get('product_week_id') : null;
        if ($productWeekId) {
            $this->view->params['productOfWeek'] = Products::findOne(['id' => $productWeekId, 'status' => 1]);
        }

        $this->view->params['cities'] = Cities::getCitiesInSelect();
    }
}
