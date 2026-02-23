<?php

namespace app\modules\admin;

/**
 * admin-panel module definition class
 */
class Admin extends \yii\base\Module
{
    public $layout = '@app/modules/admin/views/layouts/main';

    public $defaultRoute = 'default/index';
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\admin\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
