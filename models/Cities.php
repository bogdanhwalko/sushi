<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "cities".
 *
 * @property int $id
 * @property string $alias
 * @property string $name
 * @property string $full_name
 * @property int $sort_order Порядок сортування
 * @property int $status 1 = активна, 0 = вимкнена
 * @property string $created_at
 * @property string $updated_at
 */
class Cities extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'cities';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['alias', 'name', 'full_name'], 'required'],
            [['sort_order', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],

            [['alias', 'name', 'full_name'], 'string', 'max' => 100],

            ['sort_order', 'default', 'value' => 100],
            ['status', 'default', 'value' => 1],

            ['alias', 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'alias' => 'Аліас',
            'name' => 'Назва',
            'full_name' => 'Повна назва',
            'sort_order' => 'Порядок сортування',
            'status' => 'Статус',
            'created_at' => 'Створено',
            'updated_at' => 'Оновлено',
        ];
    }


    public static function getCitiesInSelect(): array
    {
        $query = self::find()
            ->select(['alias', 'full_name'])
            ->where(['status' => 1])
            ->orderBy(['sort_order' => SORT_ASC])
            ->asArray();

        return ['all' => 'Усі міста'] + ArrayHelper::map($query->all(), 'alias', 'full_name');
    }
}
