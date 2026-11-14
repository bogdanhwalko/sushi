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

    /**
     * стандартний логотип — векторний (перемальований з logo.png). Кольори частин — CSS-змінні
     * всередині SVG (--digits, --text, --roll, --roll-shade, --roll-light, --outline) зі стандартними
     * значеннями за замовчуванням, тож перефарбувати = дописати одне правило svg{--roll:…}.
     */
    public const DEFAULT_LOGO = 'images/icons/logo.svg';

    /** підпапка UPLOAD_DIR для перефарбованих копій стандартного логотипа (створюються автоматично) */
    public const LOGO_COLORS_DIR = 'logo';

    /** кольори частин стандартного логотипа (як у самому SVG) — для палітри в адмінці */
    public const STANDARD_LOGO_COLORS = [
        'logo_digits' => '#fdfcfb',
        'logo_text' => '#dd4c2a',
        'logo_roll' => '#dd4c2a',
        'logo_outline' => '#0a0302',
    ];

    /** ключі налаштувань, що зберігають ім'я завантаженого файлу (для історії та прибирання) */
    public const FILE_KEYS = ['image_desktop', 'image_mobile', 'logo'];

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

    /** стандартні відтінки помаранчевого — як у :root у site.css (для перегляду в адмінці) */
    public const STANDARD_ACCENT_PALETTE = [
        '--accent' => '#dd4c2a',
        '--accent-dark' => '#d94c18',
        '--accent-rgb' => '221, 76, 42',
        '--accent-light-rgb' => '240, 93, 35',
        '--accent-glow-rgb' => '255, 106, 42',
        '--accent-darker' => '#b93c1d',
        '--accent-deep' => '#7a210f',
    ];

    public const TEXT_KEYS =['eyebrow', 'title', 'title_accent', 'subtitle', 'badge'];

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
        'logo' => '',            // ім'я файлу в UPLOAD_DIR; порожньо — стандартний логотип
        'logo_digits' => '',     // кольори стандартного логотипа, #rrggbb; порожньо — стандартний,
        'logo_text' => '',       // а напис і рол — фірмовий колір (accent_color), якщо його задано
        'logo_roll' => '',
        'logo_outline' => '',
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


    /** URL логотипа: свій завантажений, або стандартний (перефарбований, якщо задано кольори) */
    public static function logoUrl(?array $values = null): string
    {
        $values = $values ?? self::all();
        $file = $values['logo'] ?? '';
        if ($file !== '') {
            return Yii::getAlias('@web/') . self::UPLOAD_DIR . '/' . $file;
        }

        $colored = self::coloredLogoFile(self::logoVars($values));

        return Yii::getAlias('@web/') . ($colored !== null
            ? self::UPLOAD_DIR . '/' . self::LOGO_COLORS_DIR . '/' . $colored
            : self::DEFAULT_LOGO);
    }


    /**
     * Кольори частин стандартного логотипа, що відрізняються від стандартних.
     * Напис і рол без власного кольору беруть фірмовий; тінь і відблиск рола — відтінки його кольору.
     * Та сама логіка є в JS перегляду адмінки (views/hero/index.php → logoVars).
     *
     * @return array<string, string> CSS-змінна SVG → колір
     */
    public static function logoVars(array $values): array
    {
        $pick = static fn (string $key): string => self::isColor($values[$key] ?? '') ? strtolower($values[$key]) : '';
        $accent = $pick('accent_color');

        $vars = [];
        if (($c = $pick('logo_digits')) !== '') {
            $vars['--digits'] = $c;
        }
        if (($c = $pick('logo_text') ?: $accent) !== '') {
            $vars['--text'] = $c;
        }
        if (($c = $pick('logo_roll') ?: $accent) !== '') {
            $vars['--roll'] = $c;
            $vars['--roll-shade'] = self::mix($c, [0, 0, 0], 0.2);
            $vars['--roll-light'] = self::mix($c, [255, 255, 255], 0.36);
        }
        if (($c = $pick('logo_outline')) !== '') {
            $vars['--outline'] = $c;
        }

        return $vars;
    }


    /**
     * Перефарбована копія стандартного логотипа. Ім'я файлу — від кольорів, тож однакові кольори
     * дають той самий файл, а браузер може кешувати його, як звичайну картинку.
     *
     * @return string|null ім'я файлу в UPLOAD_DIR/LOGO_COLORS_DIR; null — кольорів немає або не вдалося записати
     */
    public static function coloredLogoFile(array $vars): ?string
    {
        if (!$vars) {
            return null;
        }

        $rule = 'svg{' . implode(';', array_map(static fn ($k, $v) => "$k:$v", array_keys($vars), $vars)) . '}';
        $name = 'logo-' . substr(md5($rule), 0, 12) . '.svg';
        $dir = self::uploadPath() . '/' . self::LOGO_COLORS_DIR;
        $path = "$dir/$name";
        if (is_file($path)) {
            return $name;
        }

        $svg = @file_get_contents(Yii::getAlias('@webroot/' . self::DEFAULT_LOGO));
        if ($svg === false || strpos($svg, '<style>') === false) {
            Yii::warning('Не знайдено шаблон логотипа ' . self::DEFAULT_LOGO, __METHOD__);
            return null;
        }
        $svg = preg_replace('/<style>/', '<style>' . $rule, $svg, 1);

        // пишемо в тимчасовий файл і перейменовуємо — паралельний запит не побачить недописаний SVG
        $tmp = $path . '.' . uniqid('', true) . '.tmp';
        if ((!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir))
            || @file_put_contents($tmp, $svg) === false
            || !@rename($tmp, $path)) {
            @unlink($tmp);
            Yii::warning("Не вдалося записати перефарбований логотип: $path", __METHOD__);
            return null;
        }

        return $name;
    }


    /** Видаляє перефарбовані копії логотипа, крім потрібних (імена файлів) */
    public static function pruneColoredLogos(array $keep): void
    {
        $dir = self::uploadPath() . '/' . self::LOGO_COLORS_DIR;
        $keep = array_flip($keep);
        foreach (is_dir($dir) ? scandir($dir) : [] as $file) {
            if ($file[0] !== '.' && !isset($keep[$file]) && is_file("$dir/$file") && !@unlink("$dir/$file")) {
                Yii::warning("Не вдалося видалити $dir/$file", __METHOD__);
            }
        }
    }


    /** <img> логотипа сайту; data-hero-logo — щоб перегляд в адмінці міг його підмінити */
    public static function logoImg(array $options = []): string
    {
        return Html::img(self::logoUrl(), array_merge(['alt' => '107 Sushi', 'data-hero-logo' => true], $options));
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

        if (self::isColor(self::get('text_color'))) {
            $style .= ' --hero-text: ' . self::get('text_color') . ';';
        }

        return $style;
    }


    /**
     * Фірмовий колір (помаранчевий) для всього сайту: заголовок, ціни, кнопки, підсвітки.
     * Якщо задано в адмінці — перевизначає змінні --accent* з site.css; інакше нічого не виводить.
     */
    public static function registerAccent(View $view): void
    {
        $color = self::get('accent_color');
        if (!self::isColor($color)) {
            return;
        }

        $css = ':root {';
        foreach (self::accentPalette($color) as $var => $value) {
            $css .= " $var: $value;";
        }
        $view->registerCss($css . ' }', [], 'hero-accent');
    }


    /**
     * Відтінки з одного кольору — у тих самих пропорціях, що й стандартні відтінки помаранчевого.
     * Та сама формула є в JS перегляду адмінки (views/hero/index.php → accentPalette).
     *
     * @return array<string, string> CSS-змінна → значення
     */
    public static function accentPalette(string $hex): array
    {
        $black = [0, 0, 0];
        $white = [255, 255, 255];
        $rgb = static fn (string $c): string => implode(', ', array_map('hexdec', str_split(ltrim($c, '#'), 2)));

        return [
            '--accent' => strtolower($hex),
            '--accent-dark' => self::mix($hex, $black, 0.04),
            '--accent-rgb' => $rgb($hex),
            '--accent-light-rgb' => $rgb(self::mix($hex, $white, 0.07)),
            '--accent-glow-rgb' => $rgb(self::mix($hex, $white, 0.14)),
            '--accent-darker' => self::mix($hex, $black, 0.17),
            '--accent-deep' => self::mix($hex, $black, 0.45),
        ];
    }


    /** Колір $hex, зсунутий на частку $t до кольору $to ([r, g, b]) */
    private static function mix(string $hex, array $to, float $t): string
    {
        $rgb = array_map('hexdec', str_split(ltrim($hex, '#'), 2));

        return vsprintf('#%02x%02x%02x', array_map(
            static fn ($c, $target) => (int)round($c + ($target - $c) * $t), $rgb, $to
        ));
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
