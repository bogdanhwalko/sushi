<?php

/** @var yii\web\View $this */
/** @var app\forms\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Вхід';
?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <div class="card account-card">
            <div class="card-body p-4">
                <h1 class="h3 fw-bold mb-1">Вхід</h1>
                <p class="text-muted mb-4">Увійдіть, щоб бачити історію замовлень і швидше оформлювати нові.</p>

                <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'autocomplete' => 'username']) ?>
                <?= $form->field($model, 'password')->passwordInput(['autocomplete' => 'current-password']) ?>
                <?= $form->field($model, 'rememberMe')->checkbox() ?>

                <?= Html::submitButton('Увійти', ['class' => 'btn btn-dark w-100']) ?>

                <?php ActiveForm::end(); ?>

                <p class="text-center text-muted small mt-3 mb-0">
                    Ще немає акаунта? <?= Html::a('Зареєструватися', ['/account/signup']) ?>
                </p>
            </div>
        </div>
    </div>
</div>
