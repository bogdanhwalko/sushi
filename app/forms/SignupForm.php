<?php

namespace app\forms;

use app\components\RateLimiter;
use app\models\User;
use app\validators\PasswordValidator;

/**
 * Реєстрація клієнта: дані профілю + пароль.
 */
class SignupForm extends ProfileForm
{
    /** Не більше RATE_LIMIT реєстрацій з однієї IP за RATE_WINDOW секунд */
    private const RATE_LIMIT = 3;

    private const RATE_WINDOW = 3600;

    public $password;
    public $password_repeat;


    public function __construct($config = [])
    {
        parent::__construct(new User([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]), $config);
    }


    protected static function customerAttributes(): array
    {
        return array_merge(parent::customerAttributes(), ['password', 'password_repeat']);
    }


    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['password', 'password_repeat'], 'required'],
            ['password', PasswordValidator::class],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Паролі не збігаються.'],
        ]);
    }


    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'password' => 'Пароль',
            'password_repeat' => 'Повторіть пароль',
        ]);
    }


    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        // захист від масової реєстрації ботами
        if (RateLimiter::tooMany('signup', self::RATE_LIMIT, self::RATE_WINDOW)) {
            $this->addError('username', 'Забагато реєстрацій з вашої мережі. Спробуйте пізніше.');
            return false;
        }

        $this->user->setPassword($this->password);

        if (!parent::save()) {
            return false;
        }

        RateLimiter::hit('signup', self::RATE_WINDOW);

        return true;
    }
}
