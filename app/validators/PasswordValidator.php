<?php

namespace app\validators;

use yii\validators\Validator;

/**
 * Пароль: від 8 символів і не довше 72 байтів.
 *
 * bcrypt враховує лише перші 72 байти. Кирилична літера в UTF-8 займає 2 байти,
 * тож без цієї перевірки пароль з понад 36 кириличних літер мовчки обрізався б.
 */
class PasswordValidator extends Validator
{
    public const MIN_LENGTH = 8;

    public const MAX_BYTES = 72;


    public function validateAttribute($model, $attribute): void
    {
        $value = $model->$attribute;

        if (!is_string($value)) {
            $this->addError($model, $attribute, 'Некоректний пароль.');
            return;
        }

        if (mb_strlen($value, 'UTF-8') < self::MIN_LENGTH) {
            $this->addError($model, $attribute, 'Пароль має містити щонайменше {min} символів.', ['min' => self::MIN_LENGTH]);
            return;
        }

        if (strlen($value) > self::MAX_BYTES) {
            $this->addError($model, $attribute,
                'Пароль задовгий: до 72 латинських символів або до 36 кириличних.');
        }
    }
}
