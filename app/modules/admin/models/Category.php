<?php

namespace app\modules\admin\models;

use app\models\Categorys;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property int|null $parent_id Батьківська категорія
 * @property string $name Назва категорії
 * @property string $slug URL-ідентифікатор
 * @property string|null $description Опис
 * @property int $sort_order Порядок сортування
 * @property int $status 1 = активна, 0 = вимкнена
 * @property string $created_at
 * @property string $updated_at
 */
class Category extends Categorys
{

    public function rules():array
    {
        return parent::rules();
    }

    public static function getBySelect(): array
    {
        return ArrayHelper::map(
            self::find()->select(['id', 'name'])->all(),
            'id', 'name'
        );
    }
}
