<?php

namespace app\modules\admin\models;

use app\models\Products;
use Yii;
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
class Product extends Products
{

    /** @var UploadedFile|null */
    public $imageFile;


    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['imageFile'], 'file',
                'skipOnEmpty' => true,
                'extensions' => ['png','jpg','jpeg','webp'],
                'maxSize' => 5 * 1024 * 1024, // 5MB
            ],
        ]);
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
