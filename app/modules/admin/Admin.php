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

        // помилки в адмінці показуємо в оформленні адмінки, а не сайту
        \Yii::$app->errorHandler->errorAction = $this->id . '/error/index';
    }
}
