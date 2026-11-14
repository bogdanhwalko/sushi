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

    /**
     * Причина, з якої категорію не можна видалити, або null.
     * Товари й підкатегорії інакше лишились би з посиланням на неіснуючу категорію.
     */
    public function getDeleteBlocker(): ?string
    {
        $products = Product::find()->where(['category_id' => $this->id])->count();
        if ($products > 0) {
            return "У категорії є товари ($products). Перенесіть їх в іншу категорію або вимкніть категорію замість видалення.";
        }

        $children = static::find()->where(['parent_id' => $this->id])->count();
        if ($children > 0) {
            return "У категорії є підкатегорії ($children). Спершу перенесіть або видаліть їх.";
        }

        return null;
    }


    public function beforeDelete(): bool
    {
        return parent::beforeDelete() && $this->getDeleteBlocker() === null;
    }


    public static function getBySelect(): array
    {
        return ArrayHelper::map(
            self::find()->select(['id', 'name'])->all(),
            'id', 'name'
        );
    }
}
