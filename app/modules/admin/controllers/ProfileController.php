<?php

namespace app\modules\admin\controllers;

use app\forms\ChangePasswordForm;
use app\forms\ProfileForm;
use app\models\User;
use Yii;

/**
 * Профіль адміністратора: логін і пароль.
 */
class ProfileController extends AbstractController
{
    public function actionIndex()
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $profile = new ProfileForm($user);
        $password = new ChangePasswordForm($user);
        $request = Yii::$app->request;

        if ($request->isPost) {
            if ($profile->load($request->post()) && $profile->save()) {
                Yii::$app->session->setFlash('success', 'Логін збережено.');
                return $this->refresh();
            }

            if ($password->load($request->post()) && $password->save()) {
                Yii::$app->session->setFlash('success', 'Пароль змінено.');
                return $this->refresh();
            }
        }

        return $this->render('index', [
            'user' => $user,
            'profile' => $profile,
            'password' => $password,
        ]);
    }
}
