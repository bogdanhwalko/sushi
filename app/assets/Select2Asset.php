<?php

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

class Select2Asset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte/plugins/select2';

    public $css = [
        'css/select2.min.css',
    ];

    public $js = [
        'js/select2.full.min.js',
    ];

    public $depends = [
        JqueryAsset::class
    ];
}