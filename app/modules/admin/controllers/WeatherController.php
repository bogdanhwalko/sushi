<?php

namespace app\modules\admin\controllers;

use app\components\WeatherEffect;
use app\modules\admin\forms\WeatherForm;
use Yii;

/**
 * Сніг або дощ на сайті.
 */
class WeatherController extends AbstractController
{
    public function actionIndex()
    {
        $model = new WeatherForm();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', $model->mode === 'off'
                ? 'Ефект вимкнено.'
                : WeatherEffect::MODES[$model->mode] . ' увімкнено на сайті.');

            return $this->refresh();
        }

        return $this->render('index', ['model' => $model]);
    }
}
