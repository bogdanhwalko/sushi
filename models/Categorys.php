<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

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
 *
 * @property Categorys|null $parent
 * @property Categorys[] $children
 */
class Categorys extends ActiveRecord
{

    public static function tableName(): string
    {
        return 'category';
    }



    public function rules(): array
    {
        return [
            [['name', 'slug'], 'required'],
            [['parent_id', 'sort_order', 'status'], 'integer'],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],

            [['name', 'slug'], 'string', 'max' => 100],

            // slug повинен бути унікальним
            [['slug'], 'unique'],

            // parent_id має посилатися на існуючу категорію
            [
                ['parent_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => self::class,
                'targetAttribute' => ['parent_id' => 'id'],
            ],

            // (опційно) slug у форматі "rolls", "sets", "veggie"
            [['slug'], 'match', 'pattern' => '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'message' => 'Slug має містити лише латиницю, цифри та дефіси.'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Батьківська категорія',
            'name' => 'Назва категорії',
            'slug' => 'Slug (URL-ідентифікатор)',
            'description' => 'Опис',
            'sort_order' => 'Порядок сортування',
            'status' => 'Статус',
            'created_at' => 'Створено',
            'updated_at' => 'Оновлено',
        ];
    }

    public function getParent()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    public function getChildren()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }


    public static function getActive()
    {
        return static::find()
            ->select(['slug', 'name'])
            ->andWhere(['status' => 1])
            ->asArray()
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();
    }
}