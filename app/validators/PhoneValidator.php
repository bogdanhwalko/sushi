<?php

namespace app\validators;

use yii\validators\Validator;

class PhoneValidator extends Validator
{
    /**
     * Зводить номер до формату 380XXXXXXXXX.
     * Приймає "+38 (097) 123-45-67", "0971234567", "971234567" тощо.
     * Якщо привести не вдалося — повертає лише цифри як є (валідація їх відхилить).
     */
    public static function normalize($value): string
    {
        $digits = preg_replace('/\D+/', '', (string)$value);

        if (strlen($digits) === 9) {
            return '380' . $digits;
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            return '38' . $digits;
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '80')) {
            return '3' . $digits;
        }

        return $digits;
    }


    public function validateValue($value)
    {
        if (! preg_match('#^\+?380\d{9}$#', (string)$value)) {
            return [$this->message ?? 'Номер телефону не коректний. Передайте номер телефону у форматі 380xxxxxxxxx.', []];
        }

        return null;
    }
}
