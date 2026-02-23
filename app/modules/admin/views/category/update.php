<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\admin\models\Category $model */

$this->title = 'Оновлення категорії: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Категорії', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="category-update">
    <?php $form = ActiveForm::begin(); ?>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'slug')->textInput(['maxlength' => true]) ?>
                            </div>

                            <div class="col-md-12">
                                <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
                            </div>

                            <div class="col-md-6">
                                <?= $form->field($model, 'sort_order')->textInput() ?>
                            </div>

                            <div class="col-md-6">
                                <?= $form->field($model, 'status')
                                    ->dropDownList(['Не активна', 'Активна'], ['class' => 'form-control']) ?>
                            </div>

                            <div class="col-md-12 text-right">
                                <div class="form-group">
                                    <?= Html::submitButton('Зберегти', ['class' => 'btn btn-success']) ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    <?php ActiveForm::end(); ?>
</div>
