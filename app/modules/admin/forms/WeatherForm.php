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
    /** колір снігу / сніжинок / дощу, #rrggbb або '' — стандартний */
    public $color;


    public function init(): void
    {
        parent::init();

        $s = WeatherEffect::settings();
        $this->mode = $s['mode'];
        $this->intensity = $s['intensity'];
        $this->mobile = $s['mobile'];
        $this->color = $s['color'];
    }


    public function rules(): array
    {
        return [
            ['mode', 'in', 'range' => array_keys(WeatherEffect::MODES)],
            ['intensity', 'in', 'range' => array_keys(WeatherEffect::INTENSITIES)],
            ['mobile', 'boolean'],
            ['color', 'filter', 'filter' => static fn ($v) => strtolower(trim((string)$v))],
            ['color', 'match', 'pattern' => '/^(#[0-9a-f]{6})?$/i', 'message' => 'Колір у форматі #rrggbb.'],
        ];
    }


    public function attributeLabels(): array
    {
        return [
            'mode' => 'Ефект',
            'intensity' => 'Інтенсивність',
            'mobile' => 'Показувати на телефонах',
            'color' => 'Колір',
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
            'color' => (string)$this->color,
        ]);
        Yii::info('Змінено ефект «сніг/дощ»: ' . $this->mode, __METHOD__);

        return true;
    }
}
