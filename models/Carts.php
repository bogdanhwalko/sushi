<?php

namespace app\models;

use app\validators\PhoneValidator;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * @property int $id
 * @property int|null $session_id
 * @property int $status
 * @property string $currency
 * @property string|null $note
 * @property int $phone
 * @property string $created_at
 * @property string $updated_at
 */
class Carts extends ActiveRecord
{

    /**
     * Статуси корзини
     */
    public const STATUS_ACTIVE = 1;
    public const STATUS_ARCHIVED = 0;
    public const STATUS_ORDERED = 2;

    public static function tableName(): string
    {
        return 'cart';
    }


    public function rules(): array
    {
        return [
            ['session_id', 'required'],
            ['status', 'integer'],

            [['currency'], 'string', 'max' => 3],
            [['note'], 'string', 'max' => 500],

            ['phone', PhoneValidator::class],

            [['status'], 'default', 'value' => 1],
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
            'session_id' => 'Користувач',
            'status' => 'Статус',
            'currency' => 'Валюта',
            'note' => 'Коментар',
            'phone' => 'Телефон',
            'created_at' => 'Створено',
            'updated_at' => 'Оновлено',
        ];
    }

}
