<?php

namespace app\modules\admin\controllers;

use app\components\HeroSettings;
use app\models\HeroHistory;
use app\modules\admin\forms\HeroForm;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Шапка сайту: фото фону для ПК і телефона, тексти, кольори, затемнення, позиція фото,
 * історія збережень з відновленням.
 */
class HeroController extends AbstractController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'restore' => ['POST'],
                    'reset' => ['POST'],
                ],
            ],
        ]);
    }


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
            Yii::$app->session->setFlash('success', 'Шапку сайту збережено. Попередній варіант — в історії нижче.');
            return $this->refresh();
        }

        return $this->render('index', [
            'model' => $model,
            'history' => HeroHistory::find()->with('user')->orderBy(['id' => SORT_DESC])->all(),
        ]);
    }


    /** Повертає шапку до збереженого раніше варіанта */
    public function actionRestore(int $id): Response
    {
        $row = HeroHistory::findOne($id);
        if ($row === null) {
            throw new NotFoundHttpException('Такого запису в історії немає.');
        }

        $values = $row->getValues();

        // фото могли видалити вручну з сервера — тоді ставимо стандартне, а не «бите» посилання
        $missing = [];
        foreach (['image_desktop' => 'ПК', 'image_mobile' => 'телефона'] as $key => $label) {
            if ($values[$key] !== '' && !HeroSettings::imageExists($values[$key])) {
                $values[$key] = '';
                $missing[] = $label;
            }
        }

        HeroHistory::apply($values);

        $message = 'Відновлено варіант від ' . Yii::$app->formatter->asDatetime($row->created_at, 'php:d.m.Y H:i') . '.';
        if ($missing) {
            $message .= ' Фото для ' . implode(' і ', $missing) . ' не знайдено на сервері — поставлено стандартне.';
        }
        Yii::$app->session->setFlash('success', $message);

        return $this->redirect(['index']);
    }


    /** Усе як у дизайні сайту: стандартні фото, тексти й кольори */
    public function actionReset(): Response
    {
        HeroHistory::apply(array_map(static fn () => '', HeroSettings::DEFAULTS));
        Yii::$app->session->setFlash('success', 'Шапку повернуто до стандартного вигляду. Попередній варіант — в історії.');

        return $this->redirect(['index']);
    }
}
