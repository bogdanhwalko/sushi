<?php

namespace app\modules\admin\controllers;

use app\components\WeatherEffect;
use app\modules\admin\forms\WeatherForm;
use Yii;

/**
 * Погодні та святкові ефекти на сайті: сніг, сніжинки, дощ, листя, сакура, конфеті, сердечка.
 */
class WeatherController extends AbstractController
{
    public function actionIndex()
    {
        $model = new WeatherForm();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', $model->mode === 'off'
                ? 'Ефект вимкнено.'
                : 'Ефект «' . WeatherEffect::MODES[$model->mode] . '» увімкнено на сайті.');

            return $this->refresh();
        }

        return $this->render('index', ['model' => $model]);
    }
}
