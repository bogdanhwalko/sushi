<?php

namespace app\modules\admin\controllers;

use app\components\OrderTelegram;
use app\modules\admin\models\TelegramGroup;
use app\modules\admin\models\search\TelegramGroupSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\Response;

/**
 * TelegramGroupController implements the CRUD actions for TelegramGroup model.
 */
class TelegramGroupController extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                        'webhook' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Підключає ботів до сайту, щоб у групах працювала кнопка «Прийняте» під новими замовленнями.
     * Потрібен https: адреса сайту береться з поточного запиту.
     */
    public function actionWebhook(): Response
    {
        $results = OrderTelegram::setupWebhooks($this->request->hostInfo . $this->request->baseUrl);

        $failed = array_filter($results, static fn ($r) => !$r['ok']);
        $lines = array_map(static fn ($r) => ($r['ok'] ? '✓ ' : '✗ ') . Html::encode($r['bot'] . ': ' . $r['message']), $results);
        $this->session->setFlash(
            $failed ? 'error' : 'success',
            ($failed ? 'Кнопки підключено не до всіх ботів:' : 'Кнопку «Прийняте» підключено.') . '<br>' . implode('<br>', $lines)
        );

        return $this->redirect(['index']);
    }

    /**
     * Lists all TelegramGroup models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new TelegramGroupSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single TelegramGroup model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new TelegramGroup model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new TelegramGroup();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing TelegramGroup model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing TelegramGroup model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the TelegramGroup model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return TelegramGroup the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TelegramGroup::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
