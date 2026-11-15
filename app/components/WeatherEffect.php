<?php

namespace app\components;

use app\models\Configs;
use Yii;
use yii\helpers\Html;
use yii\web\View;

/**
 * Погодні та святкові ефекти на сайті (сніг, сніжинки, дощ, листя, сакура, конфеті) —
 * вмикаються в адмінці → «Сніг і дощ».
 *
 * Налаштування — один рядок config "weather" (JSON). Коли ефект вимкнено, на сторінку
 * нічого не додається (ні полотна, ні скрипта). Коли увімкнено — одне <canvas> поверх сторінки
 * і невеликий скрипт js/weather.js; він не реагує на кліки, ставиться на паузу у фоновій вкладці
 * і не запускається в тих, хто в системі просив менше анімацій.
 */
final class WeatherEffect
{
    public const MODES = [
        'off' => 'Вимкнено',
        'snow' => 'Сніг',
        'flakes' => 'Сніжинки',
        'rain' => 'Дощ',
        'leaves' => 'Осіннє листя',
        'sakura' => 'Сакура',
        'confetti' => 'Конфеті',
    ];

    /**
     * Режими, для яких можна вибрати колір, і їхні стандартні кольори (як DEFAULT_COLORS у js/weather.js).
     * Листя, сакура й конфеті мають власну палітру.
     */
    public const DEFAULT_COLORS = [
        'snow' => '#ffffff',
        'flakes' => '#ffffff',
        'rain' => '#8caad7',
    ];

    public const INTENSITIES = [
        1 => 'Легкий',
        2 => 'Помірний',
        3 => 'Сильний',
    ];

    public const DEFAULTS = [
        'mode' => 'off',
        'intensity' => 2,
        'mobile' => true,   // показувати на телефонах
        'color' => '',      // #rrggbb для DEFAULT_COLORS-режимів; порожньо — стандартний
    ];

    /** версія скрипта — змінювати разом із js/weather.js */
    public const SCRIPT = 'js/weather.js?v=3';

    private const CONFIG = 'weather';


    /** @return array{mode: string, intensity: int, mobile: bool, color: string} */
    public static function settings(): array
    {
        $data = json_decode((string)Configs::get(self::CONFIG), true);

        return self::normalize(is_array($data) ? $data : []);
    }


    /** @return array{mode: string, intensity: int, mobile: bool, color: string} */
    public static function normalize(array $data): array
    {
        $mode = (string)($data['mode'] ?? self::DEFAULTS['mode']);
        $intensity = (int)($data['intensity'] ?? self::DEFAULTS['intensity']);
        $color = strtolower((string)($data['color'] ?? ''));

        return [
            'mode' => array_key_exists($mode, self::MODES) ? $mode : self::DEFAULTS['mode'],
            'intensity' => array_key_exists($intensity, self::INTENSITIES) ? $intensity : self::DEFAULTS['intensity'],
            'mobile' => (bool)($data['mobile'] ?? self::DEFAULTS['mobile']),
            'color' => preg_match('/^#[0-9a-f]{6}$/', $color) ? $color : '',
        ];
    }


    public static function save(array $data): void
    {
        Configs::set(self::CONFIG, json_encode(self::normalize($data)));
    }


    /**
     * Полотно ефекту + скрипт (лише якщо ефект увімкнено). Викликається в layout одразу після <body>.
     */
    public static function render(View $view): string
    {
        $s = self::settings();
        if ($s['mode'] === 'off') {
            return '';
        }

        $view->registerJsFile(Yii::getAlias('@web/') . self::SCRIPT, ['defer' => true], 'weather-effect');

        return self::canvas($s);
    }


    /** <canvas> з налаштуваннями в data-атрибутах (їх читає js/weather.js) */
    public static function canvas(array $s): string
    {
        return Html::tag('canvas', '', [
            'id' => 'weatherLayer',
            'class' => 'weather-layer',
            'aria-hidden' => 'true',
            'data-mode' => $s['mode'],
            'data-intensity' => $s['intensity'],
            'data-mobile' => $s['mobile'] ? '1' : '0',
            'data-color' => isset(self::DEFAULT_COLORS[$s['mode']]) && $s['color'] !== '' ? $s['color'] : null,
        ]);
    }
}
