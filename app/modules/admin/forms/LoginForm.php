<?php

namespace app\modules\admin\forms;

use app\models\User;

/**
 * Вхід в адмін-панель — лише для користувачів з роллю admin.
 */
class LoginForm extends \app\forms\LoginForm
{
    protected ?string $requiredRole = User::ROLE_ADMIN;
}
