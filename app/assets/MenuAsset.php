<?php

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

class MenuAsset extends AssetBundle
{
    public $basePath = '@webroot';

    public $baseUrl = '@web';

    public $css = [
        'css/bootstrap.min.css',
        'css/site.css?v=1',
    ];

    public $cssOptions = [
        'crossorigin' => 'anonymous',
    ];

    public $js = [
        'js/jquery-3.7.1.min.js',
        'js/bootstrap.bundle.min.js',
        'js/img-error.js?v=2',
        'js/menu.js?v=2',
    ];

    public $jsOptions = [
        'position' => View::POS_END,
        'crossorigin' => 'anonymous',
    ];

    public $depends = [
        'yii\web\YiiAsset'
    ];
}