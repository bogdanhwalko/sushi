<?php

namespace app\modules\admin\controllers;

use app\models\Cities;
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


    /** значення фільтра «місто» для замовлень без міста (або місто з них видалили) */
    public const CITY_NONE = 'none';


    /**
     * @param string|null $status статус замовлення
     * @param string|null $city   id міста або CITY_NONE
     */
    public function actionIndex($status = null, $city = null)
    {
        $status = $status !== null && $status !== '' && isset(Orders::statusLabels()[(int)$status]) ? (string)(int)$status : null;
        $city = $city === self::CITY_NONE || ctype_digit((string)$city) ? (string)$city : null;

        $statusCondition = $status === null ? [] : ['status' => (int)$status];
        $cityCondition = match (true) {
            $city === null => [],
            $city === self::CITY_NONE => ['city_id' => null],
            default => ['city_id' => (int)$city],
        };

        $query = Orders::find()
            ->with(['user'])
            ->where($statusCondition)
            ->andWhere($cityCondition)
            ->orderBy(['id' => SORT_DESC]);

        // скільки замовлень у кожному місті (з урахуванням вибраного статусу) — для списку міст
        $cityCounts = Orders::find()
            ->select(['cnt' => 'COUNT(*)', 'city_id'])
            ->where($statusCondition)
            ->groupBy('city_id')
            ->indexBy(static fn ($row) => $row['city_id'] ?? self::CITY_NONE)
            ->asArray()
            ->column();

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => ['pageSize' => 30],
            ]),
            'status' => $status,
            'city' => $city,
            'cities' => Cities::find()->select(['name', 'id'])->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->indexBy('id')->column(),
            'cityCounts' => array_map('intval', $cityCounts),
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
