<?php

namespace app\modules\admin\controllers;

use app\models\User;
use Yii;
use yii\web\Controller;
use yii\web\ErrorAction;

/**
 * Сторінка помилки для адмінки (у її оформленні, а не в оформленні сайту).
 * Підключається в Admin::init() як errorAction для всіх маршрутів admin-panel/*.
 *
 * Навмисно не наслідує AbstractController: сторінка помилки має відкриватись і для гостя
 * (інакше перевірка доступу сама кине помилку). Повну адмінку з меню бачить лише адмін,
 * решта — простий шаблон без меню.
 */
class ErrorController extends Controller
{
    public function actions(): array
    {
        return [
            'index' => [
                'class' => ErrorAction::class,
                'view' => 'index',
                'defaultName' => 'Помилка',
                'defaultMessage' => 'Під час обробки запиту сталася внутрішня помилка сервера.',
            ],
        ];
    }


    public function beforeAction($action): bool
    {
        $identity = Yii::$app->user->identity;
        $this->layout = $identity instanceof User && $identity->isAdmin() ? 'main' : 'main-login';

        return parent::beforeAction($action);
    }
}
