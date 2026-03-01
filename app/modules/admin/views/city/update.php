<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\admin\models\City $model */

$this->title = 'Оновлення інформації: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Міста', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Оновлення';
?>
<div class="city-update">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-lightblue">
                <div class="card-header">
                    <div class="card-tool text-right">
                        <?= Html::submitButton('Оновити', ['class' => 'btn btn-sm btn-light']) ?>
                    </div>
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-sm-8 col-md-8 col-lg-8 col-xl-8 clo-xxl-8">
                            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-sm-4 col-md-4 col-lg-4 col-xl-4">
                            <?= $form->field($model, 'status')
                                ->dropDownList(['Не активне', 'Активне'], ['class' => 'form-control']) ?>
                        </div>
                        <div class="col-sm-8 col-md-8 col-lg-8 col-xl-8 clo-xxl-8">
                            <?= $form->field($model, 'full_name')->textInput(['maxlength' => true]) ?>
                        </div>

                        <div class="col-sm-4 col-md-4 col-lg-4 col-xl-4">
                            <?= $form->field($model, 'sort_order')->textInput()->label('Сортування') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
