<?php

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Сторінки особистого кабінету (без скриптів меню та кошика).
 */
class AccountAsset extends AssetBundle
{
    public $basePath = '@webroot';

    public $baseUrl = '@web';

    public $css = [
        'css/bootstrap.min.css',
        'css/site.css?v=7',
    ];

    public $js = [
        'js/bootstrap.bundle.min.js',
    ];

    public $jsOptions = [
        'position' => View::POS_END,
    ];

    public $depends = [
        // yii.js — для посилань з data-method="post" (вихід)
        'yii\web\YiiAsset',
    ];
}
