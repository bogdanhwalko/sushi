<?php

namespace app\forms;

use app\models\Cities;
use app\models\User;
use app\validators\PhoneValidator;
use yii\base\Model;

/**
 * Редагування профілю.
 * Адмін змінює лише логін, клієнт — логін, ПІБ, телефон, email та місто.
 */
class ProfileForm extends Model
{
    public const SCENARIO_ADMIN = 'admin';
    public const SCENARIO_CUSTOMER = 'customer';

    public $username;
    public $last_name;
    public $first_name;
    public $middle_name;
    public $phone;
    public $email;
    public $city_id;

    protected User $user;


    public function __construct(User $user, $config = [])
    {
        $this->user = $user;

        foreach (self::customerAttributes() as $attribute) {
            $this->$attribute = $user->$attribute;
        }

        parent::__construct($config);

        $this->scenario = $user->isAdmin() ? self::SCENARIO_ADMIN : self::SCENARIO_CUSTOMER;
    }


    protected static function customerAttributes(): array
    {
        return ['username', 'last_name', 'first_name', 'middle_name', 'phone', 'email', 'city_id'];
    }


    public function scenarios(): array
    {
        return [
            self::SCENARIO_ADMIN => ['username'],
            self::SCENARIO_CUSTOMER => static::customerAttributes(),
        ];
    }


    public function rules(): array
    {
        return [
            [['username', 'last_name', 'first_name', 'middle_name', 'email'], 'trim'],

            ['username', 'required'],
            ['username', 'string', 'min' => 3, 'max' => 64],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_.\-]+$/',
                'message' => 'Логін може містити лише латинські літери, цифри та символи _ . -'],
            ['username', 'unique', 'targetClass' => User::class,
                'filter' => fn($query) => $this->user->isNewRecord ? null : $query->andWhere(['<>', 'id', $this->user->id]),
                'message' => 'Цей логін уже зайнятий.'],

            [['last_name', 'first_name'], 'required'],
            [['last_name', 'first_name', 'middle_name'], 'string', 'max' => 100],
            [['middle_name', 'email'], 'default', 'value' => null],

            ['phone', 'filter', 'filter' => [PhoneValidator::class, 'normalize']],
            ['phone', 'required'],
            ['phone', PhoneValidator::class, 'message' => 'Вкажіть номер у форматі +380XXXXXXXXX.'],

            ['email', 'string', 'max' => 255],
            ['email', 'email'],

            ['city_id', 'required', 'message' => 'Оберіть місто.'],
            ['city_id', 'integer'],
            ['city_id', 'exist', 'targetClass' => Cities::class, 'targetAttribute' => ['city_id' => 'id'],
                'filter' => ['status' => 1], 'message' => 'Оберіть місто зі списку.'],
        ];
    }


    public function attributeLabels(): array
    {
        return (new User())->attributeLabels();
    }


    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        foreach ($this->activeAttributes() as $attribute) {
            if ($this->user->hasAttribute($attribute)) {
                $this->user->$attribute = $this->$attribute;
            }
        }

        return $this->user->save(false);
    }


    public function getUser(): User
    {
        return $this->user;
    }
}
