<?php

namespace app\components;

use app\models\Configs;
use Yii;
use yii\helpers\Html;
use yii\web\View;

/**
 * Налаштування шапки сайту (фон, тексти, кольори), які змінюються в адмінці → «Шапка сайту».
 *
 * Зберігаються в таблиці config з префіксом "hero.". Усі читаються одним запитом
 * і кешуються одним записом. Що не задано (порожньо) — береться зі значень за замовчуванням,
 * тож без налаштувань сайт виглядає як раніше.
 *
 * Тексти виводяться з атрибутом data-hero="<ключ>": за ним адмінка підставляє зміни
 * в живий перегляд, а CSS фарбує їх у вибраний колір.
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

    /** кольори, якими текст показується без налаштувань (для палітри в адмінці) */
    public const STANDARD_COLORS = [
        'text_color' => '#ffffff',
        'accent_color' => '#dd4c2a',
    ];

    public const TEXT_KEYS = ['eyebrow', 'title', 'title_accent', 'subtitle', 'badge'];

    public const DEFAULTS = [
        'eyebrow' => 'Забери сам та отримай знижку -5%',
        'title' => 'Доставка японської кухні у твоєму місті',
        'title_accent' => '"107 SUSHI"',
        'subtitle' => 'Обирайте страви. Про все інше подбає наша команда.',
        'badge' => 'Доставка від 800 грн БЕЗКОШТОВНА',
        'text_color' => '',      // #rrggbb; порожньо — як у дизайні сайту
        'accent_color' => '',
        'image_desktop' => '',   // ім'я файлу в UPLOAD_DIR; порожньо — стандартне фото
        'image_mobile' => '',
        'overlay' => '0',        // затемнення фото, %
        'position' => 'left',    // яка частина фото лишається в кадрі
    ];

    private const PREFIX = 'hero.';
    private const CACHE_KEY = [self::class, 'v2'];

    private static ?array $stored = null;
    private static ?array $current = null;


    /**
     * Те, що збережено в адмінці, по всіх ключах ('' — не задано, тобто стандартне).
     * Саме це записується в історію і відновлюється з неї.
     *
     * @return array<string, string>
     */
    public static function stored(): array
    {
        if (self::$stored === null) {
            $rows = Yii::$app->cache->getOrSet(self::CACHE_KEY, static function () {
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

            self::$stored = self::normalize($rows);
        }

        return self::$stored;
    }


    /** @return array<string, string> усі значення для показу (задані в адмінці або стандартні) */
    public static function all(): array
    {
        if (self::$current === null) {
            $values = self::DEFAULTS;
            foreach (self::stored() as $key => $value) {
                if ($value !== '') {
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


    /**
     * Лише відомі ключі, усі — рядки; невідомі відкидаються, відсутні стають ''.
     *
     * @return array<string, string>
     */
    public static function normalize(array $values): array
    {
        $result = [];
        foreach (array_keys(self::DEFAULTS) as $key) {
            $result[$key] = isset($values[$key]) && is_scalar($values[$key]) ? (string)$values[$key] : '';
        }

        return $result;
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
        self::$stored = self::$current = null;
    }


    /** URL фото шапки: 'desktop' | 'mobile' */
    public static function imageUrl(string $device, ?array $values = null): string
    {
        $file = $values === null ? self::get('image_' . $device) : ($values['image_' . $device] ?? '');

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


    /** чи файл завантаженого фото є на диску */
    public static function imageExists(string $file): bool
    {
        return $file !== '' && $file === basename($file) && is_file(self::uploadPath() . '/' . $file);
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
     * Відкривальний тег шапки: класи + CSS-змінні (фото, затемнення, позиція, кольори).
     * Самі правила — у site.css (.gradient-hero).
     */
    public static function headerTag(string $class): string
    {
        $classes = [$class];
        if (self::get('text_color') !== '') {
            $classes[] = 'hero-custom-text';
        }
        if (self::get('accent_color') !== '') {
            $classes[] = 'hero-custom-accent';
        }

        return Html::beginTag('header', ['class' => implode(' ', $classes), 'style' => self::headerStyle()]);
    }


    public static function headerStyle(): string
    {
        $overlay = max(0, min(80, (int)self::get('overlay'))) / 100;
        $position = array_key_exists(self::get('position'), self::POSITIONS) ? self::get('position') : 'left';

        $style = sprintf(
            "--hero-bg: url('%s'); --hero-bg-mobile: url('%s'); --hero-overlay: %s; --hero-position: %s;",
            self::cssUrl(self::imageUrl('desktop')),
            self::cssUrl(self::imageUrl('mobile')),
            rtrim(rtrim(number_format($overlay, 2, '.', ''), '0'), '.') ?: '0',
            $position
        );

        foreach (['text_color' => '--hero-text', 'accent_color' => '--hero-accent'] as $key => $var) {
            if (self::isColor(self::get($key))) {
                $style .= " $var: " . self::get($key) . ';';
            }
        }

        return $style;
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


    /** Текст шапки (екранований), позначений для перегляду й кольору */
    public static function text(string $key): string
    {
        return Html::tag('span', Html::encode(self::get($key)), ['data-hero' => $key]);
    }


    /** Заголовок: біла частина + помаранчева */
    public static function titleHtml(string $accentClass): string
    {
        return self::text('title') . ' '
            . Html::tag('span', Html::encode(self::get('title_accent')), ['class' => $accentClass, 'data-hero' => 'title_accent']);
    }


    public static function isColor(string $value): bool
    {
        return (bool)preg_match('/^#[0-9a-f]{6}$/i', $value);
    }


    /** URL у CSS url('…'): лапки й дужки не можуть «вийти» за межі значення */
    private static function cssUrl(string $url): string
    {
        return str_replace(["'", '"', '(', ')', '\\', "\n", "\r"], ['%27', '%22', '%28', '%29', '%5C', '', ''], $url);
    }
}
