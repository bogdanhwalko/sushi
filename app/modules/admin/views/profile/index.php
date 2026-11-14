<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\User $user */
/** @var app\forms\ProfileForm $profile */
/** @var app\forms\ChangePasswordForm $password */

$this->title = 'Мій профіль';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="profile-index">
    <div class="row">
        <div class="col-md-6">
            <div class="card card-lightblue">
                <div class="card-header">
                    <h3 class="card-title">Обліковий запис</h3>
                </div>
                <?php $form = ActiveForm::begin(['id' => 'admin-profile-form']); ?>
                <div class="card-body">
                    <?= $form->field($profile, 'username')
                        ->textInput(['maxlength' => 64, 'autocomplete' => 'username'])
                        ->hint('Латинські літери, цифри та символи _ . -') ?>

                    <p class="text-muted small mb-0">
                        Роль: адміністратор · Створено: <?= Html::encode(date('d.m.Y', strtotime($user->created_at))) ?>
                    </p>
                </div>
                <div class="card-footer text-right">
                    <?= Html::submitButton('Зберегти', ['class' => 'btn btn-info']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-lightblue">
                <div class="card-header">
                    <h3 class="card-title">Зміна пароля</h3>
                </div>
                <?php $form = ActiveForm::begin(['id' => 'admin-password-form']); ?>
                <div class="card-body">
                    <?= $form->field($password, 'current_password')->passwordInput(['autocomplete' => 'current-password']) ?>
                    <?= $form->field($password, 'new_password')
                        ->passwordInput(['autocomplete' => 'new-password'])
                        ->hint('Щонайменше 8 символів') ?>
                    <?= $form->field($password, 'new_password_repeat')->passwordInput(['autocomplete' => 'new-password']) ?>
                </div>
                <div class="card-footer text-right">
                    <?= Html::submitButton('Змінити пароль', ['class' => 'btn btn-info']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
