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
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';

    public $baseUrl = '@web';

    public $css = [
        'css/bootstrap.min.css',
        'css/site.css',
    ];

    public $cssOptions = [
        'crossorigin' => 'anonymous',
    ];

    public $js = [
        'js/jquery-3.7.1.min.js',
        'js/bootstrap.bundle.min.js',
        'js/city.js',
        'js/script.js',
    ];

    public $jsOptions = [
        'position' => View::POS_END,
        'crossorigin' => 'anonymous',
    ];

    public $depends = [
        'yii\web\YiiAsset'
    ];
}
