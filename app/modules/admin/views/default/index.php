<?php

use yii\helpers\Html;

$this->title = 'Головна';

\app\assets\Select2Asset::register($this);

$this->registerJsFile("@web/js/admin/productWeek.js", [\yii\web\View::POS_BEGIN]);

?>

<div class="admin-panel-default-index">
    <div class="row">
        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 clo-xxl-6">
            <div class="card card-lightblue">
                <div class="card-header">
                    <h3 class="card-title">Акція тижня</h3>
                    <div class="card-tools">
                        <button
                            type="button"
                            class="btn btn-tool"
                            id="refresh-product-week-button"
                            data-card-widget="card-refresh"
                            data-source="/admin-panel/ajax/product/product-week"
                            data-source-type="html"
                            data-load-on-init="false">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" id="card-refresh-content">

                </div>
                <div class="card-footer">
                    <div class="input-group">
                        <?= Html::dropDownList(
                            'product_week',
                            null,
                            [],
                            ['class' => 'form-control select2', 'id' => 'select2-product-week' ]
                        ) ?>
                        <span class="input-group-append">
                            <button type="button" class="btn btn-info btn-flat" id="product-week-button">
                                <span class="fa fa-arrow-right"></span>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-lightblue">
                <div class="card-header"></div>
                <div class="card-body">

                </div>
            </div>
        </div>
    </div>
</div>
