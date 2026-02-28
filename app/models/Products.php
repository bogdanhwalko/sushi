<?php

namespace app\models;

use app\modules\admin\models\Category;
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
            [['description', 'short_description', 'old_price', 'weight', 'image', 'meta_title', 'meta_description'], 'default', 'value' => null],
            [['currency'], 'default', 'value' => 'UAH'],
            [['status'], 'default', 'value' => 1],
            [['sort_order'], 'default', 'value' => 100],
            [['category_id', 'name', 'price'], 'required'],
            [['category_id', 'price', 'status', 'sort_order'], 'integer'],
            [['description'], 'string'],
            [['old_price', 'weight'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'slug', 'image', 'meta_title'], 'string', 'max' => 255],
            [['short_description', 'meta_description'], 'string', 'max' => 500],
            [['currency'], 'string', 'max' => 3],
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

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Категорія',
            'name' => 'Назва',
            'slug' => 'Аліас',
            'description' => 'Опис',
            'short_description' => 'Короткий опис',
            'price' => 'Ціна',
            'old_price' => 'Стара ціна',
            'currency' => 'Валюта',
            'weight' => 'Вага',
            'image' => 'Зображення',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'status' => 'Статус',
            'sort_order' => 'Сортування',
            'created_at' => 'Створено',
            'updated_at' => 'Оновлено',
        ];
    }


    /**
     * Gets query for [[CartItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCartItems()
    {
        return $this->hasMany(CartItems::class, ['product_id' => 'id']);
    }


    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
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
