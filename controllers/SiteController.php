<?php

namespace app\controllers;

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

        $cityMap = [
            'all' => ['label' => 'Всі міста', 'address' => 'Всі міста'],
            'kyiv' => ['label' => 'Київ', 'address' => 'Київ, Київська 10'],
            'kyiv20' => ['label' => 'Київ', 'address' => 'Київ, Київська 20'],
            'kyiv30' => ['label' => 'Київ', 'address' => 'Київ, Київська 30'],
        ];

        $this->view->params['cityMap'] = $cityMap;
        $this->view->params['defaultCity'] = 'kyiv';

        return $this->render('index');
    }
}
