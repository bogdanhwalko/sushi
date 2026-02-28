<?php

namespace app\controllers;

use app\models\CartItems;
use app\models\Carts;
use app\models\Cities;
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

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $this->view->params['totalCount'] = (int)CartItems::find()
            ->where(['session_id' => Yii::$app->session->get('session_id')])
            ->sum('qty');

        $this->view->params['productOfWeek'] = Products::findOne(1);
        $this->view->params['cities'] = Cities::getCitiesInSelect();

        return $this->render('index');
    }
}
