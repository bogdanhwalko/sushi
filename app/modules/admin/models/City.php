<?php

namespace app\modules\admin\models;

use app\models\Cities;
use Yii;


class City extends Cities
{
    public function rules():array
    {
        return parent::rules();
    }
}
