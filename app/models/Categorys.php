<?php

namespace app\models;

use app\modules\api\models\Product;
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
            [['parent_id', 'description'], 'default', 'value' => null],
            [['sort_order'], 'default', 'value' => 100],
            [['status'], 'default', 'value' => 1],
            [['parent_id', 'sort_order', 'status'], 'integer'],
            [['name', 'slug'], 'required'],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'slug'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Батьківська категорія',
            'name' => 'Назва',
            'slug' => 'Аліс',
            'description' => 'Опис',
            'sort_order' => 'Сортування',
            'status' => 'Статус',
            'created_at' => 'Створено',
            'updated_at' => 'Оновлено',
        ];
    }


    public function getParent()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }


    public function getProducts()
    {
        return $this->hasMany(Product::class, ['category_id' => 'id'])
            ->alias('p')
            ->andOnCondition(['p.status' => 1])
            ->orderBy(['p.sort_order' => SORT_DESC]);
    }


    public function getChildren()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }


    public static function getActive(bool $withProducts = false): array
    {
        $query = static::find()
            ->alias('t1')
            ->select(['t1.slug', 't1.name', 't1.id'])
            ->andWhere(['t1.status' => 1])
            ->orderBy(['t1.sort_order' => SORT_DESC]);

        if ($withProducts) {
            $query->joinWith('products');
        }

        return $query->all();
    }


    public static function getActiveFirstId(): int
    {
        return static::find()
            ->andWhere(['status' => 1])
            ->orderBy(['sort_order' => SORT_DESC])
            ->limit(1)
            ->scalar();
    }
}