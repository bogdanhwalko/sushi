<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\admin\models\TelegramGroup $model */

$this->title = 'Нова група';
$this->params['breadcrumbs'][] = ['label' => 'Групи Telegram', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="telegram-group-create">

    <?php $form = ActiveForm::begin(); ?>
        <div class="row">
            <div class="col-md-6">
                <div class="card card-lightblue">
                    <div class="card-header">
                        <div class="card-tool text-right">
                            <?= Html::submitButton('Зберегти', ['class' => 'btn btn-sm btn-light']) ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 clo-xxl-4">
                                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                            </div>
                            <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 clo-xxl-4">
                                <?= $form->field($model, 'telegram_id')->textInput() ?>
                            </div>
                            <div class="col-sm-8 col-md-8 col-lg-8 col-xl-8 clo-xxl-8">
                                <?= $form->field($model, 'bot_id')->textInput(['maxlength' => true]) ?>
                            </div>
                            <div class="col-sm-4 col-md-4 col-lg-4 col-xl-3">
                                <?= $form->field($model, 'status')->dropDownList(['Не активна', 'Активна'], ['class' => 'form-control']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-lightblue">
                    <div class="card-header">

                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php ActiveForm::end(); ?>

</div>
