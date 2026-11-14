<?php

/** @var yii\web\View $this */
/** @var app\forms\SignupForm $model */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Реєстрація';
?>

<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">
        <div class="card account-card">
            <div class="card-body p-4">
                <h1 class="h3 fw-bold mb-1">Реєстрація</h1>
                <p class="text-muted mb-4">Дані підставлятимуться в замовлення автоматично. Змінити їх можна будь-коли в профілі.</p>

                <?php $form = ActiveForm::begin(['id' => 'signup-form']); ?>

                <?= $this->render('_profile_fields', ['form' => $form, 'model' => $model]) ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <?= $form->field($model, 'password')
                            ->passwordInput(['autocomplete' => 'new-password'])
                            ->hint('Щонайменше 8 символів') ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'password_repeat')->passwordInput(['autocomplete' => 'new-password']) ?>
                    </div>
                </div>

                <?= Html::submitButton('Зареєструватися', ['class' => 'btn btn-dark px-4 mt-2']) ?>

                <?php ActiveForm::end(); ?>

                <p class="text-muted small mt-3 mb-0">
                    Вже є акаунт? <?= Html::a('Увійти', ['/account/login']) ?>
                </p>
            </div>
        </div>
    </div>
</div>
