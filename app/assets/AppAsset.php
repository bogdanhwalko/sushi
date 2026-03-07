<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Main application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';

    public $baseUrl = '@web';

    public $css = [
        'css/bootstrap.min.css',
        'css/site.css?v=2',
    ];

    public $cssOptions = [
        'crossorigin' => 'anonymous',
    ];

    public $js = [
        'js/jquery-3.7.1.min.js',
        'js/bootstrap.bundle.min.js',
        'js/city.js?v=3',
        'js/menu.js?v=3',
        'js/script.js?v=3',
        'js/img-error.js?v=3'
    ];

    public $jsOptions = [
        'position' => View::POS_END,
        'crossorigin' => 'anonymous',
    ];

    public $depends = [
        'yii\web\YiiAsset'
    ];
}
