<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Користувач сайту: адміністратор або клієнт.
 *
 * @property int $id
 * @property string $username
 * @property string $password_hash
 * @property string $auth_key
 * @property string $role
 * @property string|null $last_name
 * @property string|null $first_name
 * @property string|null $middle_name
 * @property string|null $phone
 * @property string|null $email
 * @property int|null $city_id
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Cities|null $city
 * @property Orders[] $orders
 */
class User extends ActiveRecord implements IdentityInterface
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_CUSTOMER = 'customer';

    public const STATUS_BLOCKED = 0;
    public const STATUS_ACTIVE = 1;


    public static function tableName(): string
    {
        return '{{%user}}';
    }


    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'username' => 'Логін',
            'role' => 'Роль',
            'last_name' => 'Прізвище',
            'first_name' => "Ім'я",
            'middle_name' => 'По батькові',
            'phone' => 'Телефон',
            'email' => 'Email',
            'city_id' => 'Місто',
            'status' => 'Статус',
            'created_at' => 'Зареєстровано',
            'updated_at' => 'Оновлено',
        ];
    }


    public function getCity(): ActiveQuery
    {
        return $this->hasOne(Cities::class, ['id' => 'city_id']);
    }


    public function getOrders(): ActiveQuery
    {
        return $this->hasMany(Orders::class, ['user_id' => 'id'])
            ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);
    }


    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }


    /** Прізвище Ім'я По батькові (те, що заповнено) */
    public function getFullName(): string
    {
        return trim(implode(' ', array_filter([$this->last_name, $this->first_name, $this->middle_name])));
    }


    /**
     * Встановлює новий пароль. Ключ "Запам'ятати мене" теж змінюється,
     * тож усі збережені входи на інших пристроях анулюються.
     */
    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
        $this->generateAuthKey();
    }


    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString(32);
    }


    public function validatePassword($password): bool
    {
        return Yii::$app->security->validatePassword((string)$password, $this->password_hash);
    }


    public static function findByUsername($username): ?self
    {
        return static::findOne(['username' => (string)$username, 'status' => self::STATUS_ACTIVE]);
    }


    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Вхід за токеном не використовується.
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return is_string($authKey) && hash_equals((string)$this->auth_key, $authKey);
    }
}
