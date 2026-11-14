<?php

namespace app\modules\admin\controllers;

use app\models\Orders;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * Перегляд замовлень і зміна їх статусу.
 */
class OrderController extends AbstractController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'status' => ['POST'],
                ],
            ],
        ]);
    }


    public function actionIndex($status = null)
    {
        $query = Orders::find()
            ->with(['user'])
            ->orderBy(['id' => SORT_DESC]);

        if ($status !== null && $status !== '' && isset(Orders::statusLabels()[(int)$status])) {
            $query->andWhere(['status' => (int)$status]);
        }

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => ['pageSize' => 30],
            ]),
            'status' => $status,
        ]);
    }


    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }


    public function actionStatus($id)
    {
        $model = $this->findModel($id);
        $status = (int)Yii::$app->request->post('status');

        if (isset(Orders::statusLabels()[$status])) {
            $model->updateAttributes(['status' => $status]);
            Yii::$app->session->setFlash('success', 'Статус замовлення оновлено.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }


    protected function findModel($id): Orders
    {
        $model = Orders::find()->with(['items', 'user'])->where(['id' => (int)$id])->one();
        if ($model === null) {
            throw new NotFoundHttpException('Замовлення не знайдено.');
        }

        return $model;
    }
}
