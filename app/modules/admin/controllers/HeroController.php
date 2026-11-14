<?php

namespace app\modules\admin\controllers;

use app\modules\admin\forms\HeroForm;
use Yii;

/**
 * Шапка сайту: фото фону для ПК і телефона, тексти, затемнення, позиція фото.
 */
class HeroController extends AbstractController
{
    public function actionIndex()
    {
        $model = new HeroForm();
        $request = Yii::$app->request;

        // запит більший за post_max_size: PHP відкидає всю форму, і $_POST порожній
        if ($request->isPost && empty($request->post()) && empty($_FILES)) {
            Yii::$app->session->setFlash('error', 'Файл завеликий для сервера (ліміт ' . ini_get('post_max_size') . '). Зменшіть фото і спробуйте ще раз.');
            return $this->refresh();
        }

        if ($model->load($request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Шапку сайту збережено.');
            return $this->refresh();
        }

        return $this->render('index', ['model' => $model]);
    }
}
