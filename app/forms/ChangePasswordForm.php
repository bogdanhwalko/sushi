<?php

namespace app\forms;

use app\models\User;
use app\validators\PasswordValidator;
use Yii;
use yii\base\Model;

/**
 * Зміна пароля (для адмінів і клієнтів).
 */
class ChangePasswordForm extends Model
{
    public $current_password;
    public $new_password;
    public $new_password_repeat;

    private User $user;


    public function __construct(User $user, $config = [])
    {
        $this->user = $user;
        parent::__construct($config);
    }


    public function rules(): array
    {
        return [
            [['current_password', 'new_password', 'new_password_repeat'], 'required'],
            ['current_password', function ($attribute) {
                if (!$this->user->validatePassword($this->$attribute)) {
                    $this->addError($attribute, 'Поточний пароль невірний.');
                }
            }],
            ['new_password', PasswordValidator::class],
            ['new_password_repeat', 'compare', 'compareAttribute' => 'new_password', 'message' => 'Паролі не збігаються.'],
        ];
    }


    public function attributeLabels(): array
    {
        return [
            'current_password' => 'Поточний пароль',
            'new_password' => 'Новий пароль',
            'new_password_repeat' => 'Повторіть новий пароль',
        ];
    }


    /**
     * Змінює пароль. Входи на інших пристроях анулюються,
     * поточна сесія залишається активною.
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->user->setPassword($this->new_password);
        if (!$this->user->save(false, ['password_hash', 'auth_key'])) {
            return false;
        }

        $webUser = Yii::$app->user;
        $remembered = Yii::$app->request->cookies->has($webUser->identityCookie['name'] ?? '_identity');
        $webUser->login($this->user, $remembered ? LoginForm::REMEMBER_DURATION : 0);

        $this->current_password = $this->new_password = $this->new_password_repeat = null;

        return true;
    }
}
