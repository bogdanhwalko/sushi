<?php

namespace app\modules\api;

use yii\web\Response;

/**
 * admin-panel module definition class
 */
class Api extends \yii\base\Module
{
    public $layout = '@app/modules/api/views/layouts/main';

    public $defaultRoute = 'default/index';
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\api\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
