<?php

namespace app\modules\admin\models;

use app\models\Products;
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



    /**
     * Зберігає товар разом із новим фото (якщо його вибрали).
     * Помилки — у $this->errors, щоб форма показала їх адміну.
     * Попереднє фото видаляється з диска лише після успішного збереження.
     */
    public function saveWithImage(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $oldImage = $this->getOldAttribute('image');

        if (!$this->uploadImage()) {
            return false;
        }

        if (!$this->save(false)) {
            // товар не зберігся — новий файл нікому не потрібен
            if ($this->image !== $oldImage) {
                static::deleteImageFile($this->image);
                $this->image = $oldImage;
            }
            $this->addError('imageFile', 'Не вдалося зберегти товар.');
            return false;
        }

        if ($oldImage && $oldImage !== $this->image) {
            static::deleteImageFile($oldImage, $this->id);
        }

        return true;
    }


    public function uploadImage(): bool
    {
        if (!$this->imageFile) {
            return true; // нічого не завантажували — не помилка
        }

        if (!$this->validate(['imageFile'])) {
            return false;
        }

        $dir = static::imageDir();
        if (!FileHelper::createDirectory($dir)) {
            $this->addError('imageFile', 'Немає папки для зображень або прав на запис у неї.');
            return false;
        }

        // безпечно генеруємо ім'я
        $name = bin2hex(random_bytes(16)) . '.' . strtolower($this->imageFile->extension);

        if (!$this->imageFile->saveAs($dir . '/' . $name)) {
            Yii::error('Не вдалося зберегти фото товару: помилка завантаження ' . $this->imageFile->error, __METHOD__);
            $this->addError('imageFile', 'Не вдалося зберегти файл. Спробуйте ще раз.');
            return false;
        }

        // шлях для збереження в БД (url-частина)
        $this->image = $name;

        return true;
    }


    public function afterDelete(): void
    {
        parent::afterDelete();

        static::deleteImageFile($this->image, $this->id);
    }


    /**
     * Видаляє файл фото, якщо ним не користується жоден інший товар
     * (у старих даних одне фото могло бути в кількох товарів).
     */
    public static function deleteImageFile(?string $image, ?int $exceptProductId = null): void
    {
        // у БД лише ім'я файлу; будь-що з шляхом ігноруємо
        if (empty($image) || $image !== basename($image)) {
            return;
        }

        $usedElsewhere = static::find()
            ->where(['image' => $image])
            ->andFilterWhere(['<>', 'id', $exceptProductId])
            ->exists();
        if ($usedElsewhere) {
            return;
        }

        $path = static::imageDir() . '/' . $image;
        if (is_file($path) && !@unlink($path)) {
            Yii::warning("Не вдалося видалити старе фото товару: $path", __METHOD__);
        }
    }


    private static function imageDir(): string
    {
        return Yii::getAlias('@webroot/uploads/products');
    }
}
