<?php

namespace app\components;

use app\models\Configs;
use Yii;
use yii\helpers\Html;
use yii\web\View;

/**
 * Налаштування шапки сайту (фон і тексти), які змінюються в адмінці → «Шапка сайту».
 *
 * Зберігаються в таблиці config з префіксом "hero.". Усі читаються одним запитом
 * і кешуються одним записом. Що не задано — береться зі значень за замовчуванням,
 * тож без налаштувань сайт виглядає як раніше.
 */
final class HeroSettings
{
    /** веб-папка завантажених фото шапки (від public_html) */
    public const UPLOAD_DIR = 'uploads/hero';

    /** стандартні фото — у репозиторії */
    public const DEFAULT_IMAGES = [
        'desktop' => 'images/header.webp',
        'mobile' => 'images/header-mobile.webp',
    ];

    /** до якої ширини екрана показується фото для телефона (як sm-брейкпоінт Bootstrap) */
    public const MOBILE_MAX_WIDTH = 767.98;

    public const POSITIONS = [
        'left' => 'Ліворуч',
        'center' => 'По центру',
        'right' => 'Праворуч',
    ];

    public const DEFAULTS = [
        'eyebrow' => 'Забери сам та отримай знижку -5%',
        'title' => 'Доставка японської кухні у твоєму місті',
        'title_accent' => '"107 SUSHI"',
        'subtitle' => 'Обирайте страви. Про все інше подбає наша команда.',
        'badge' => 'Доставка від 800 грн БЕЗКОШТОВНА',
        'image_desktop' => '',   // ім'я файлу в UPLOAD_DIR; порожньо — стандартне фото
        'image_mobile' => '',
        'overlay' => '0',        // затемнення фото, %
        'position' => 'left',    // яка частина фото лишається в кадрі
    ];

    private const PREFIX = 'hero.';
    private const CACHE_KEY = [self::class, 'v1'];

    private static ?array $current = null;


    /** @return array<string, string> усі значення (задані в адмінці або стандартні) */
    public static function all(): array
    {
        if (self::$current === null) {
            $stored = Yii::$app->cache->getOrSet(self::CACHE_KEY, static function () {
                $rows = Configs::find()
                    ->select(['value', 'name'])
                    ->where(['like', 'name', self::PREFIX . '%', false])
                    ->andWhere(['status' => 1])
                    ->indexBy('name')
                    ->column();

                $values = [];
                foreach ($rows as $name => $value) {
                    $values[substr($name, strlen(self::PREFIX))] = (string)$value;
                }

                return $values;
            });

            $values = self::DEFAULTS;
            foreach ($stored as $key => $value) {
                // порожній текст = «як було» (стандартний)
                if (array_key_exists($key, $values) && $value !== '') {
                    $values[$key] = $value;
                }
            }
            self::$current = $values;
        }

        return self::$current;
    }


    public static function get(string $key): string
    {
        return self::all()[$key] ?? '';
    }


    /** @param array<string, string|null> $values */
    public static function save(array $values): void
    {
        foreach ($values as $key => $value) {
            if (array_key_exists($key, self::DEFAULTS)) {
                Configs::set(self::PREFIX . $key, $value === null ? '' : (string)$value);
            }
        }

        Yii::$app->cache->delete(self::CACHE_KEY);
        self::$current = null;
    }


    /** URL фото шапки: 'desktop' | 'mobile' */
    public static function imageUrl(string $device): string
    {
        $file = self::get('image_' . $device);

        return Yii::getAlias('@web/') . ($file !== '' ? self::UPLOAD_DIR . '/' . $file : self::DEFAULT_IMAGES[$device]);
    }


    /** чи завантажене своє фото (а не стандартне) */
    public static function hasCustomImage(string $device): bool
    {
        return self::get('image_' . $device) !== '';
    }


    /** шлях до папки завантажених фото на диску */
    public static function uploadPath(): string
    {
        return Yii::getAlias('@webroot/' . self::UPLOAD_DIR);
    }


    /** Видаляє завантажене фото шапки (лише ім'я файлу в UPLOAD_DIR, без шляхів) */
    public static function deleteImage(?string $file): void
    {
        if (empty($file) || $file !== basename($file)) {
            return;
        }

        $path = self::uploadPath() . '/' . $file;
        if (is_file($path) && !@unlink($path)) {
            Yii::warning("Не вдалося видалити старе фото шапки: $path", __METHOD__);
        }
    }


    /**
     * CSS-змінні для <header class="gradient-hero">: фото, затемнення, позиція.
     * Самі правила фону — у site.css (.gradient-hero).
     */
    public static function headerStyle(): string
    {
        $overlay = max(0, min(80, (int)self::get('overlay'))) / 100;
        $position = array_key_exists(self::get('position'), self::POSITIONS) ? self::get('position') : 'left';

        return sprintf(
            "--hero-bg: url('%s'); --hero-bg-mobile: url('%s'); --hero-overlay: %s; --hero-position: %s;",
            self::cssUrl(self::imageUrl('desktop')),
            self::cssUrl(self::imageUrl('mobile')),
            rtrim(rtrim(number_format($overlay, 2, '.', ''), '0'), '.') ?: '0',
            $position
        );
    }


    /**
     * Попереднє завантаження фону: браузер бере потрібне фото (ПК або телефон) одразу,
     * а не після розбору CSS, — шапка з'являється швидше.
     */
    public static function registerPreload(View $view): void
    {
        $max = self::MOBILE_MAX_WIDTH;
        $view->registerLinkTag(['rel' => 'preload', 'as' => 'image', 'href' => self::imageUrl('mobile'), 'media' => "(max-width: {$max}px)"], 'hero-preload-mobile');
        $view->registerLinkTag(['rel' => 'preload', 'as' => 'image', 'href' => self::imageUrl('desktop'), 'media' => '(min-width: ' . ($max + 0.02) . 'px)'], 'hero-preload-desktop');
    }


    /** Заголовок: біла частина + помаранчева (обидві екрановані) */
    public static function titleHtml(string $accentClass): string
    {
        $accent = self::get('title_accent');

        return Html::encode(self::get('title'))
            . ($accent !== '' ? ' ' . Html::tag('span', Html::encode($accent), ['class' => $accentClass]) : '');
    }


    /** URL у CSS url('…'): лапки й дужки не можуть «вийти» за межі значення */
    private static function cssUrl(string $url): string
    {
        return str_replace(["'", '"', '(', ')', '\\', "\n", "\r"], ['%27', '%22', '%28', '%29', '%5C', '', ''], $url);
    }
}
