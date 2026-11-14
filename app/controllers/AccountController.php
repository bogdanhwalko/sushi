<?php

namespace app\controllers;

use app\forms\ChangePasswordForm;
use app\forms\LoginForm;
use app\forms\ProfileForm;
use app\forms\SignupForm;
use app\models\Cities;
use app\models\Orders;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Особистий кабінет клієнта: вхід, реєстрація, профіль, історія замовлень.
 */
class AccountController extends Controller
{
    public $layout = 'account';


    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['profile', 'orders', 'logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
                'denyCallback' => function () {
                    // вже залогінений користувач на сторінці входу/реєстрації
                    return Yii::$app->user->isGuest
                        ? Yii::$app->user->loginRequired()
                        : $this->redirect(['profile']);
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['POST'],
                ],
            ],
        ];
    }


    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->view->params['cities'] = Cities::getCitiesInSelect();

        return true;
    }


    public function actionLogin()
    {
        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirectAfterLogin();
        }

        $model->password = '';

        return $this->render('login', ['model' => $model]);
    }


    public function actionSignup()
    {
        $model = new SignupForm();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->user->login($model->getUser(), LoginForm::REMEMBER_DURATION);
            Yii::$app->session->setFlash('success', 'Реєстрація успішна! Ласкаво просимо.');

            return $this->redirect(['profile']);
        }

        $model->password = $model->password_repeat = null;

        return $this->render('signup', ['model' => $model]);
    }


    public function actionProfile()
    {
        // адмін теж бачить кабінет таким, яким його бачить клієнт (зі своїми даними)
        $user = $this->getCurrentUser();

        $profile = new ProfileForm($user);
        $password = new ChangePasswordForm($user);
        $request = Yii::$app->request;

        if ($request->isPost) {
            if ($profile->load($request->post()) && $profile->save()) {
                Yii::$app->session->setFlash('success', 'Дані профілю збережено.');
                return $this->refresh();
            }

            if ($password->load($request->post()) && $password->save()) {
                Yii::$app->session->setFlash('success', 'Пароль змінено.');
                return $this->refresh();
            }
        }

        $ordersProvider = new ActiveDataProvider([
            'query' => Orders::find()
                ->with('items')
                ->where(['user_id' => $user->id])
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]),
            'pagination' => ['pageSize' => 5],
            'sort' => false,
        ]);

        return $this->render('profile', [
            'user' => $user,
            'profile' => $profile,
            'password' => $password,
            'ordersProvider' => $ordersProvider,
        ]);
    }


    /**
     * Історія замовлень тепер на сторінці профілю — старе посилання веде туди.
     */
    public function actionOrders(): Response
    {
        return $this->redirect(['profile', '#' => 'orders']);
    }


    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }


    private function getCurrentUser(): User
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        return $user;
    }


    private function redirectAfterLogin(): Response
    {
        if ($this->getCurrentUser()->isAdmin()) {
            return $this->redirect(['/admin-panel/profile/index']);
        }

        return $this->goBack(['/account/profile']);
    }
}
