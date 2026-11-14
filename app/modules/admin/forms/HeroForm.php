<?php

namespace app\modules\admin\forms;

use app\components\HeroSettings;
use app\components\ImageOptimizer;
use app\models\HeroHistory;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Адмінка → «Шапка сайту»: фото фону (ПК і телефон), тексти, затемнення, позиція фото.
 */
class HeroForm extends Model
{
    /** найбільший розмір після стискання: ПК / телефон */
    private const SIZES = [
        'desktop' => [1920, 1280],
        'mobile' => [1080, 1600],
    ];

    public $eyebrow;
    public $title;
    public $title_accent;
    public $subtitle;
    public $badge;
    public $overlay;
    public $position;
    /** #rrggbb або '' — колір як у дизайні сайту */
    public $text_color;
    public $accent_color;

    /** @var UploadedFile|null */
    public $imageDesktop;
    /** @var UploadedFile|null */
    public $imageMobile;

    /** «повернути стандартне фото» */
    public $resetDesktop = false;
    public $resetMobile = false;


    public function init(): void
    {
        parent::init();

        foreach (['eyebrow', 'title', 'title_accent', 'subtitle', 'badge', 'overlay', 'position', 'text_color', 'accent_color'] as $attr) {
            $this->$attr = HeroSettings::get($attr);
        }
    }


    public function rules(): array
    {
        return [
            // зайві пробіли й переноси → один пробіл; некоректні байти UTF-8 замінюються, а не ламають сторінку
            [['eyebrow', 'title', 'title_accent', 'subtitle', 'badge'], 'filter', 'filter' => static fn ($v) => trim(preg_replace('/\s+/u', ' ', mb_scrub((string)$v, 'UTF-8')) ?? '')],
            [['eyebrow', 'badge'], 'string', 'max' => 60],
            ['title', 'string', 'max' => 80],
            ['title_accent', 'string', 'max' => 30],
            ['subtitle', 'string', 'max' => 120],

            [['text_color', 'accent_color'], 'filter', 'filter' => static fn ($v) => strtolower(trim((string)$v))],
            [['text_color', 'accent_color'], 'match', 'pattern' => '/^(#[0-9a-f]{6})?$/', 'message' => 'Колір у форматі #rrggbb.'],

            ['overlay', 'integer', 'min' => 0, 'max' => 80],
            ['position', 'in', 'range' => array_keys(HeroSettings::POSITIONS)],
            [['resetDesktop', 'resetMobile'], 'boolean'],

            [['imageDesktop', 'imageMobile'], 'file',
                'skipOnEmpty' => true,
                'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                'maxSize' => 15 * 1024 * 1024,
                'tooBig' => 'Файл завеликий: до 15 МБ.',
                'wrongExtension' => 'Лише JPG, PNG або WebP.',
                'wrongMimeType' => 'Лише JPG, PNG або WebP.',
            ],
        ];
    }


    public function attributeLabels(): array
    {
        return [
            'eyebrow' => 'Верхній рядок',
            'title' => 'Заголовок (біла частина)',
            'title_accent' => 'Заголовок (помаранчева частина)',
            'subtitle' => 'Підзаголовок',
            'badge' => 'Бейдж',
            'overlay' => 'Затемнення фото',
            'position' => 'Яку частину фото тримати в кадрі',
            'text_color' => 'Колір тексту',
            'accent_color' => 'Колір помаранчевої частини заголовка',
            'imageDesktop' => 'Фото для ПК',
            'imageMobile' => 'Фото для телефона',
            'resetDesktop' => 'Повернути стандартне фото',
            'resetMobile' => 'Повернути стандартне фото',
        ];
    }


    public function load($data, $formName = null): bool
    {
        $loaded = parent::load($data, $formName);
        $this->imageDesktop = UploadedFile::getInstance($this, 'imageDesktop');
        $this->imageMobile = UploadedFile::getInstance($this, 'imageMobile');

        return $loaded || $this->imageDesktop || $this->imageMobile;
    }


    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $values = [
            'eyebrow' => $this->eyebrow,
            'title' => $this->title,
            'title_accent' => $this->title_accent,
            'subtitle' => $this->subtitle,
            'badge' => $this->badge,
            'overlay' => (string)(int)$this->overlay,
            'position' => $this->position,
            'text_color' => $this->text_color,
            'accent_color' => $this->accent_color,
        ];

        // фото: нове → стискаємо; «стандартне» → порожньо; інакше лишається як було.
        // Старі фото не видаляємо: на них посилається історія (HeroHistory::prune прибере непотрібні).
        $created = [];
        foreach (['desktop' => 'imageDesktop', 'mobile' => 'imageMobile'] as $device => $attr) {
            $reset = $device === 'desktop' ? $this->resetDesktop : $this->resetMobile;

            if ($this->$attr instanceof UploadedFile) {
                [$w, $h] = self::SIZES[$device];
                $file = ImageOptimizer::save($this->$attr->tempName, HeroSettings::uploadPath(), $w, $h);
                if ($file === null) {
                    $this->addError($attr, ImageOptimizer::$lastError);
                    array_map([HeroSettings::class, 'deleteImage'], $created);
                    return false;
                }
                $created[] = $file;
                $values['image_' . $device] = $file;
            } elseif ($reset) {
                $values['image_' . $device] = '';
            }
        }

        HeroHistory::apply($values);

        Yii::info('Змінено налаштування шапки сайту', __METHOD__);

        return true;
    }
}
