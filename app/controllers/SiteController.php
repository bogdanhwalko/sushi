<?php

namespace app\controllers;

use app\models\CartItems;
use app\models\Carts;
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

        $cityMap = [
            'all' => ['label' => 'Всі міста', 'address' => 'Всі міста'],
            'kyiv' => ['label' => 'Київ', 'address' => 'Київ, Київська 10'],
            'kyiv20' => ['label' => 'Київ', 'address' => 'Київ, Київська 20'],
            'kyiv30' => ['label' => 'Київ', 'address' => 'Київ, Київська 30'],
        ];

        $this->view->params['cityMap'] = $cityMap;
        $this->view->params['defaultCity'] = 'kyiv';

        $this->view->params['cartItems'] = CartItems::findAll([
            'session_id' => Yii::$app->session->get('session_id'),
        ]);


        return $this->render('index');
    }
}
