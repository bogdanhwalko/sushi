<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\modules\admin\models\Category;

/** @var yii\web\View $this */
/** @var app\modules\admin\models\Product $model */

$this->title = 'Оновлення товару: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Товари', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Оновлення';
?>
<div class="product-update">

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>
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
                        <div class="col-md-8">
                            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'slug')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'price')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'old_price')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-2">
                            <?= $form->field($model, 'weight')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'category_id')
                                ->dropDownList(Category::getBySelect(), [
                                    'prompt' => 'Виберіть категорію...', 'class' => 'form-control'
                                ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'status')->dropDownList(['Не активний', 'Активний'], ['class' => 'form-control']) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'sort_order')->textInput() ?>
                        </div>
                        <div class="col-md-12">
                            <hr>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'meta_title')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'meta_description')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-lightblue">
                <div class="card-header"></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
                        </div>
                        <div class="col-md-12">
                            <?= $form->field($model, 'short_description')->textarea(['rows' => 2]) ?>
                        </div>
                        <div class="col-md-12">
                            <?php if ($model->image): ?>
                                <?= Html::img('@products/'. $model->image, [
                                    'alt' => $model->name, 'class' => 'img-fluid', 'width' => '300px'
                                ]) ?>
                            <?php endif; ?>
                            <?= $form->field($model, 'imageFile')
                                ->fileInput(['accept' => 'image/*', 'class' => 'form-control']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
