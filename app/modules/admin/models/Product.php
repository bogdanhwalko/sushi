<?php

namespace app\modules\admin\models;

use app\models\CartItems;
use Yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property int $category_id Основна категорія
 * @property string $name Назва товару
 * @property string $slug URL-ідентифікатор
 * @property string|null $description Повний опис
 * @property string|null $short_description Короткий опис
 * @property int $price Ціна
 * @property float|null $old_price Стара ціна
 * @property string $currency Валюта
 * @property float|null $weight Вага (кг)
 * @property string|null $image Головне зображення
 * @property string|null $meta_title SEO title
 * @property string|null $meta_description SEO description
 * @property int $status 1 = активний, 0 = вимкнений
 * @property int $sort_order Порядок сортування
 * @property string $created_at
 * @property string $updated_at
 *
 * @property CartItem[] $cartItems
 */
class Product extends \yii\db\ActiveRecord
{

    /** @var UploadedFile|null */
    public $imageFile;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
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
            [['slug'], 'unique'],

            [['imageFile'], 'file',
                'skipOnEmpty' => true,
                'extensions' => ['png','jpg','jpeg','webp'],
                'maxSize' => 5 * 1024 * 1024, // 5MB
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
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


    public function uploadImage(): bool
    {
        if (!$this->imageFile) {
            return true; // нічого не завантажували — не помилка
        }

        if (!$this->validate(['imageFile'])) {
            return false;
        }

        $dir = \Yii::getAlias('@webroot/uploads/products/');

        // безпечно генеруємо ім'я
        $name = bin2hex(random_bytes(16)) . '.' . $this->imageFile->extension;
        $path = $dir . DIRECTORY_SEPARATOR . $name;

        if (!$this->imageFile->saveAs($path)) {
            $this->addError('imageFile', 'Не вдалося зберегти файл.');
            return false;
        }

        // шлях для збереження в БД (url-частина)
        $this->image = $name;

        return true;
    }
}
