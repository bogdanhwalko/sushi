<?php

namespace app\validators;

use yii\validators\Validator;

class PhoneValidator extends Validator
{
    public function validateValue($value)
    {
        if (! preg_match('#^\+?380\d{9}$#', $value)) {
            return ['Номер телефону не коректний. Передайте номер телефону у форматі 380xxxxxxxxx.', []];
        }

        return '';
    }
}