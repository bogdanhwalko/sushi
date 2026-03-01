<?php

namespace app\modules\api\controllers;

use yii\web\Controller;
use yii\web\Response;


class ApiController extends Controller
{
    public function beforeAction($action)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        return parent::beforeAction($action);
    }
}