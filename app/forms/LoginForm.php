<?php

namespace app\forms;

use app\models\User;
use Yii;
use yii\base\Model;

/**
 * Вхід за логіном і паролем (спільний для сайту та адмін-панелі).
 * Після MAX_ATTEMPTS невдалих спроб з однієї IP вхід блокується на LOCK_SECONDS.
 */
class LoginForm extends Model
{
    /** Скільки невдалих спроб дозволено з однієї IP */
    private const MAX_ATTEMPTS = 5;

    /** На скільки секунд блокувати після перевищення ліміту */
    private const LOCK_SECONDS = 15 * 60;

    /** Тривалість входу з "Запам'ятати мене" */
    public const REMEMBER_DURATION = 3600 * 24 * 30;

    public $username;

    public $password;

    public $rememberMe = true;

    /** Якщо задано — увійти можуть лише користувачі з цією роллю */
    protected ?string $requiredRole = null;

    private $_user = false;


    public function rules()
    {
        return [
            ['username', 'trim'],
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }


    public function attributeLabels()
    {
        return [
            'username' => 'Логін',
            'password' => 'Пароль',
            'rememberMe' => "Запам'ятати",
        ];
    }


    public function validatePassword($attribute, $params)
    {
        if ($this->hasErrors()) {
            return;
        }

        if ($this->getFailedAttempts() >= self::MAX_ATTEMPTS) {
            $this->addError($attribute, 'Забагато невдалих спроб входу. Спробуйте через 15 хвилин.');
            return;
        }

        $user = $this->getUser();

        if (!$user || !$user->validatePassword($this->password)
            || ($this->requiredRole !== null && $user->role !== $this->requiredRole)
        ) {
            $this->registerFailedAttempt();
            $this->addError($attribute, 'Невірний логін або пароль.');
        }
    }


    public function login(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        Yii::$app->cache->delete($this->getAttemptsCacheKey());

        return Yii::$app->user->login($this->getUser(), $this->rememberMe ? self::REMEMBER_DURATION : 0);
    }


    public function getUser(): ?User
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }


    private function getAttemptsCacheKey(): string
    {
        return 'login-fail:' . md5((string)Yii::$app->request->userIP);
    }


    private function getFailedAttempts(): int
    {
        return (int)Yii::$app->cache->get($this->getAttemptsCacheKey());
    }


    private function registerFailedAttempt(): void
    {
        Yii::$app->cache->set($this->getAttemptsCacheKey(), $this->getFailedAttempts() + 1, self::LOCK_SECONDS);
    }
}
