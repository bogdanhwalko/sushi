<?php

namespace app\modules\admin\controllers;

use app\models\Orders;
use app\models\User;
use app\modules\admin\models\Customer;
use app\modules\admin\models\search\CustomerSearch;
use app\modules\admin\models\search\GuestCustomerSearch;
use app\validators\PhoneValidator;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * Клієнти: зареєстровані (акаунти) і гості без реєстрації (за номером телефону із замовлень),
 * картки з їхніми замовленнями, блокування акаунтів.
 */
class CustomerController extends AbstractController
{
    public const TABS = ['customers', 'guests'];

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


    public function actionIndex(string $tab = 'customers')
    {
        if (!in_array($tab, self::TABS, true)) {
            $tab = 'customers';
        }

        $searchModel = $tab === 'guests' ? new GuestCustomerSearch() : new CustomerSearch();

        return $this->render('index', [
            'tab' => $tab,
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search(Yii::$app->request->queryParams),
            'counts' => [
                'customers' => (int)Customer::find()->where(['role' => User::ROLE_CUSTOMER])->count(),
                'guests' => GuestCustomerSearch::countAll(),
            ],
        ]);
    }


    /**
     * Картка гостя: усі замовлення без входу в акаунт з цього номера.
     */
    public function actionGuest($phone)
    {
        $phone = PhoneValidator::normalize($phone);

        $guestOrders = Orders::find()->where(['user_id' => null, 'phone' => $phone]);

        $stats = (clone $guestOrders)
            ->select(['cnt' => 'COUNT(*)', 'total' => 'COALESCE(SUM(total), 0)', 'first' => 'MIN(created_at)', 'last' => 'MAX(created_at)'])
            ->asArray()
            ->one();

        if ($phone === '' || empty($stats['cnt'])) {
            throw new NotFoundHttpException('Замовлень без реєстрації з цього номера немає.');
        }

        // імена та міста, які гість вказував (найновіші — першими)
        $names = (clone $guestOrders)->select('customer_name')->groupBy('customer_name')
            ->orderBy(new Expression('MAX(id) DESC'))->column();
        $cities = (clone $guestOrders)->select('city_name')->andWhere(['not', ['city_name' => null]])
            ->groupBy('city_name')->orderBy(new Expression('MAX(id) DESC'))->column();

        return $this->render('guest', [
            'phone' => $phone,
            'names' => $names,
            'cities' => $cities,
            'stats' => $stats,
            'accounts' => Customer::find()->where(['role' => User::ROLE_CUSTOMER, 'phone' => $phone])->orderBy(['id' => SORT_ASC])->all(),
            'ordersProvider' => new ActiveDataProvider([
                'query' => (clone $guestOrders)->orderBy(['id' => SORT_DESC]),
                'pagination' => ['pageSize' => 20],
                'sort' => false,
            ]),
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

        // замовлення з номера клієнта, оформлені без входу в акаунт
        $guestStats = $model->phone
            ? Orders::find()
                ->select(['cnt' => 'COUNT(*)', 'total' => 'COALESCE(SUM(total), 0)'])
                ->where(['user_id' => null, 'phone' => $model->phone])
                ->asArray()
                ->one()
            : null;

        return $this->render('view', [
            'model' => $model,
            'ordersProvider' => $ordersProvider,
            'stats' => $stats,
            'guestStats' => !empty($guestStats['cnt']) ? $guestStats : null,
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
