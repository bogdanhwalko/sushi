<?php

namespace app\modules\admin\forms;

use app\components\WeatherEffect;
use Yii;
use yii\base\Model;

/**
 * Адмінка → «Сніг і дощ».
 */
class WeatherForm extends Model
{
    public $mode;
    public $intensity;
    public $mobile;


    public function init(): void
    {
        parent::init();

        $s = WeatherEffect::settings();
        $this->mode = $s['mode'];
        $this->intensity = $s['intensity'];
        $this->mobile = $s['mobile'];
    }


    public function rules(): array
    {
        return [
            ['mode', 'in', 'range' => array_keys(WeatherEffect::MODES)],
            ['intensity', 'in', 'range' => array_keys(WeatherEffect::INTENSITIES)],
            ['mobile', 'boolean'],
        ];
    }


    public function attributeLabels(): array
    {
        return [
            'mode' => 'Ефект',
            'intensity' => 'Інтенсивність',
            'mobile' => 'Показувати на телефонах',
        ];
    }


    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        WeatherEffect::save([
            'mode' => $this->mode,
            'intensity' => (int)$this->intensity,
            'mobile' => (bool)$this->mobile,
        ]);
        Yii::info('Змінено ефект «сніг/дощ»: ' . $this->mode, __METHOD__);

        return true;
    }
}
