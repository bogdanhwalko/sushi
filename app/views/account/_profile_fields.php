<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\forms\ProfileForm $model */

$cities = $this->params['cities'] ?? [];
$phoneValue = $model->phone !== null && $model->phone !== '' && ctype_digit((string)$model->phone)
    ? '+' . $model->phone
    : $model->phone;
?>

<div class="row g-3">
    <div class="col-md-4">
        <?= $form->field($model, 'last_name')->textInput(['maxlength' => 100, 'autocomplete' => 'family-name']) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'first_name')->textInput(['maxlength' => 100, 'autocomplete' => 'given-name']) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'middle_name')->textInput(['maxlength' => 100, 'autocomplete' => 'additional-name']) ?>
    </div>

    <div class="col-md-6">
        <?= $form->field($model, 'phone')->input('tel', [
            'value' => $phoneValue,
            'placeholder' => '+380XXXXXXXXX',
            'autocomplete' => 'tel',
        ]) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'email')->input('email', ['maxlength' => 255, 'autocomplete' => 'email']) ?>
    </div>

    <div class="col-md-6">
        <?= $form->field($model, 'city_id')->dropDownList($cities, ['prompt' => 'Оберіть місто']) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'username')
            ->textInput(['maxlength' => 64, 'autocomplete' => 'username'])
            ->hint('Латинські літери, цифри та символи _ . -') ?>
    </div>
</div>
