<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Оформлене замовлення.
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $city_id
 * @property string|null $city_name
 * @property string $customer_name
 * @property string $phone
 * @property int $total
 * @property int $status
 * @property int $telegram_sent
 * @property string $created_at
 * @property string $updated_at
 *
 * @property OrderItems[] $items
 * @property User|null $user
 * @property Cities|null $city
 */
class Orders extends ActiveRecord
{
    public const STATUS_NEW = 1;
    public const STATUS_DONE = 2;
    public const STATUS_CANCELED = 3;


    public static function tableName(): string
    {
        return '{{%orders}}';
    }


    public static function statusLabels(): array
    {
        return [
            self::STATUS_NEW => 'Нове',
            self::STATUS_DONE => 'Виконане',
            self::STATUS_CANCELED => 'Скасоване',
        ];
    }


    public function rules(): array
    {
        return [
            [['customer_name', 'phone'], 'required'],
            [['customer_name'], 'string', 'max' => 150],
            [['phone'], 'string', 'max' => 20],
            [['city_name'], 'string', 'max' => 100],
            [['user_id', 'city_id', 'total'], 'integer'],
            ['status', 'in', 'range' => array_keys(self::statusLabels())],
            ['status', 'default', 'value' => self::STATUS_NEW],
            ['telegram_sent', 'boolean'],
        ];
    }


    public function attributeLabels(): array
    {
        return [
            'id' => '№',
            'user_id' => 'Клієнт',
            'city_id' => 'Місто',
            'city_name' => 'Місто',
            'customer_name' => "Ім'я",
            'phone' => 'Телефон',
            'total' => 'Сума',
            'status' => 'Статус',
            'telegram_sent' => 'Надіслано в Telegram',
            'created_at' => 'Дата',
            'updated_at' => 'Оновлено',
        ];
    }


    public function getItems(): ActiveQuery
    {
        return $this->hasMany(OrderItems::class, ['order_id' => 'id'])->orderBy(['id' => SORT_ASC]);
    }


    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }


    public function getCity(): ActiveQuery
    {
        return $this->hasOne(Cities::class, ['id' => 'city_id']);
    }


    public function getStatusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? '—';
    }
}
