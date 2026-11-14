<?php

namespace app\forms;

use app\components\RateLimiter;
use app\models\Cities;
use app\validators\PhoneValidator;
use Yii;
use yii\base\Model;
use yii\helpers\Html;

/**
 * Заявка «Передзвоніть мені» з блоку контактів.
 * Надсилається в Telegram — у групу міста (як замовлення) або в основний чат.
 */
class ConsultationForm extends Model
{
    /** Не більше RATE_LIMIT заявок з однієї IP за RATE_WINDOW секунд */
    private const RATE_LIMIT = 3;

    private const RATE_WINDOW = 10 * 60;

    public $phone;

    /** Місто, обране відвідувачем на сайті (необов'язково) */
    public $city;


    public function rules(): array
    {
        return [
            ['phone', 'required', 'message' => 'Вкажіть номер телефону.'],
            ['phone', 'filter', 'filter' => [PhoneValidator::class, 'normalize']],
            ['phone', PhoneValidator::class, 'message' => 'Вкажіть коректний номер телефону.'],

            ['city', 'default', 'value' => null],
            ['city', 'integer'],
            // невідоме місто не блокує заявку — просто ігноруємо його
            ['city', 'exist', 'targetClass' => Cities::class, 'targetAttribute' => ['city' => 'id'],
                'filter' => ['status' => 1], 'skipOnError' => true],
        ];
    }


    public function attributeLabels(): array
    {
        return [
            'phone' => 'Телефон',
            'city' => 'Місто',
        ];
    }


    public function afterValidate(): void
    {
        if ($this->hasErrors('city')) {
            $this->clearErrors('city');
            $this->city = null;
        }

        parent::afterValidate();
    }


    public function send(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        if (RateLimiter::tooMany('consultation', self::RATE_LIMIT, self::RATE_WINDOW)) {
            $this->addError('phone', 'Ви вже залишили кілька заявок. Ми передзвонимо найближчим часом.');
            return false;
        }

        $city = $this->city ? Cities::findOne((int)$this->city) : null;
        $group = $city?->telegramGroup;

        $message = "\xf0\x9f\x93\x9e <b>Заявка на консультацію</b>\n";
        $message .= "\xe2\x98\x8e <b>+" . Html::encode($this->phone) . '</b>';

        $webUser = Yii::$app->user;
        if (!$webUser->isGuest && !$webUser->identity->isAdmin()) {
            $name = $webUser->identity->getFullName() ?: $webUser->identity->username;
            $message .= ' (' . Html::encode($name) . ")\n\xf0\x9f\x91\xa4 Зареєстрований клієнт";
        }
        if ($city !== null) {
            $message .= "\n\xf0\x9f\x97\xba " . Html::encode($city->name);
        }

        try {
            $sender = Yii::$app->ts;
            if (!empty($group->bot_id)) {
                $sender->changeToken($group->bot_id);
            }

            $sent = $sender->sendMessage($message, $group->telegram_id ?? null);
            if (!$sent) {
                Yii::warning('Telegram: заявку на консультацію не надіслано: ' . json_encode($sender->getLastResponse()), __METHOD__);
            }
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            $sent = false;
        }

        if (!$sent) {
            // заявка нікуди не збереглась — чесно кажемо про це клієнту
            $this->addError('phone', 'Не вдалося надіслати заявку. Спробуйте ще раз або зателефонуйте нам.');
            return false;
        }

        RateLimiter::hit('consultation', self::RATE_WINDOW);

        return true;
    }


}
