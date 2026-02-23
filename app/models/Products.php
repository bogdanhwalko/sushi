<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $short_description
 * @property float $price
 * @property float|null $old_price
 * @property string $currency
 * @property float|null $weight
 * @property string|null $image
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property int $status
 * @property int $sort_order
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Categorys $category
 */
class Products extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'product';
    }

    public function rules(): array
    {
        return [
            [['category_id', 'name', 'price'], 'required'],

            [['category_id', 'status', 'sort_order'], 'integer'],

            [['price', 'old_price', 'weight'], 'number'],

            [['description'], 'string'],
            [['short_description', 'meta_description'], 'string', 'max' => 500],

            [['name', 'slug', 'image', 'meta_title'], 'string', 'max' => 255],
            [['currency'], 'string', 'max' => 3],

            [['slug'], 'unique'],

            [['status'], 'default', 'value' => 1],
            [['sort_order'], 'default', 'value' => 100],
            [['currency'], 'default', 'value' => 'UAH'],
        ];
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('CURRENT_TIMESTAMP'),
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'category_id' => 'Категорія',
            'name' => 'Назва товару',
            'slug' => 'URL',
            'description' => 'Опис',
            'short_description' => 'Короткий опис',
            'price' => 'Ціна',
            'old_price' => 'Стара ціна',
            'currency' => 'Валюта',
            'weight' => 'Вага (кг)',
            'image' => 'Зображення',
            'meta_title' => 'Meta title',
            'meta_description' => 'Meta description',
            'status' => 'Статус',
            'sort_order' => 'Сортування',
            'created_at' => 'Створено',
            'updated_at' => 'Оновлено',
        ];
    }


    public function getCategory()
    {
        return $this->hasOne(Categorys::class, ['id' => 'category_id']);
    }


    public static function findActive(?int $categoryId = null)
    {
        $query = static::find()
            ->where(['status' => 1])
            ->orderBy(['sort_order' => SORT_ASC]);

        if ($categoryId) {
            $query->andWhere(['category_id' => $categoryId]);
        }

        return $query->all();
    }


    public function getPriseAsText()
    {
        return $this->price .' '. $this->currency;
    }
}
