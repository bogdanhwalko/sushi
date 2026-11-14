<?php

namespace app\modules\admin\controllers;

use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

class AbstractController extends Controller
{
    /**
     * Доступ лише для користувачів з роллю admin.
     * Гостя відправляємо на вхід в адмінку, клієнта — 403.
     *
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => fn() => Yii::$app->user->identity instanceof User
                            && Yii::$app->user->identity->isAdmin(),
                    ],
                ],
                'denyCallback' => function () {
                    if (Yii::$app->user->isGuest) {
                        Yii::$app->user->setReturnUrl(Yii::$app->request->url);
                        return $this->redirect(['/admin-panel/auth/login']);
                    }

                    throw new ForbiddenHttpException('Доступ лише для адміністраторів.');
                },
            ],
        ];
    }
}
