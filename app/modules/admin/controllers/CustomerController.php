<?php

namespace app\modules\admin\controllers;

use app\models\Orders;
use app\models\User;
use app\modules\admin\models\Customer;
use app\modules\admin\models\search\CustomerSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * Клієнти: список, картка клієнта з його замовленнями, блокування.
 */
class CustomerController extends AbstractController
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


    public function actionIndex()
    {
        $searchModel = new CustomerSearch();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search(Yii::$app->request->queryParams),
        ]);
    }


    public function actionView($id)
    {
        $model = $this->findModel($id);

        $ordersProvider = new ActiveDataProvider([
            'query' => Orders::find()
                ->where(['user_id' => $model->id])
                ->orderBy(['id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
            'sort' => false,
        ]);

        $stats = Orders::find()
            ->select(['cnt' => 'COUNT(*)', 'total' => 'COALESCE(SUM(total), 0)', 'last' => 'MAX(created_at)'])
            ->where(['user_id' => $model->id])
            ->asArray()
            ->one();

        return $this->render('view', [
            'model' => $model,
            'ordersProvider' => $ordersProvider,
            'stats' => $stats,
            'cartItems' => $model->getCartItems()->with('product')->all(),
        ]);
    }


    /**
     * Блокування / розблокування. Заблокований клієнт не може увійти,
     * а активна сесія завершується на наступному запиті.
     */
    public function actionStatus($id)
    {
        $model = $this->findModel($id);
        $status = (int)Yii::$app->request->post('status');

        if (isset(Customer::statusLabels()[$status])) {
            $model->updateAttributes(['status' => $status]);
            Yii::$app->session->setFlash('success', $status === User::STATUS_BLOCKED
                ? 'Клієнта заблоковано.'
                : 'Клієнта розблоковано.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }


    protected function findModel($id): Customer
    {
        $model = Customer::find()
            ->with('city')
            ->where(['id' => (int)$id, 'role' => User::ROLE_CUSTOMER])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Клієнта не знайдено.');
        }

        return $model;
    }
}
