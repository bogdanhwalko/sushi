<?php

use app\components\HeroSettings;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\admin\forms\HeroForm $model */

$this->title = 'Шапка сайту';
$this->params['breadcrumbs'][] = $this->title;

$defaultUrl = [
    'desktop' => Yii::getAlias('@web/') . HeroSettings::DEFAULT_IMAGES['desktop'],
    'mobile' => Yii::getAlias('@web/') . HeroSettings::DEFAULT_IMAGES['mobile'],
];

$this->registerCss(<<<CSS
.hero-preview { position: relative; overflow: hidden; border-radius: .5rem; background-color: #0d0f10; background-repeat: no-repeat; background-size: cover; color: #fff; }
.hero-preview::before { content: ""; position: absolute; inset: 0; background: rgba(13, 15, 16, var(--overlay, 0)); }
.hero-preview > * { position: relative; }
.hero-preview-desktop { aspect-ratio: 16 / 7; padding: 6% 5%; }
.hero-preview-mobile { width: 210px; aspect-ratio: 9 / 16; padding: 30% 8% 6%; flex-shrink: 0; }
.hero-preview-mobile .pv-eyebrow { font-size: .55rem; }
.hero-preview-mobile .pv-subtitle { font-size: .6rem; }
.hero-preview .pv-eyebrow { text-transform: uppercase; letter-spacing: .06em; font-size: .62rem; margin-bottom: .35rem; }
.hero-preview .pv-title { font-weight: 700; line-height: 1.05; font-size: 1.45rem; margin-bottom: .45rem; max-width: 62%; }
.hero-preview-mobile .pv-title { font-size: .95rem; max-width: none; }
.hero-preview .pv-accent { color: #f05d23; }
.hero-preview .pv-subtitle { font-size: .68rem; font-weight: 500; margin-bottom: .55rem; max-width: 62%; }
.hero-preview-mobile .pv-subtitle { max-width: none; }
.hero-preview .pv-badge { display: inline-flex; align-items: center; gap: .3rem; font-size: .6rem; padding: .25rem .6rem; border-radius: 999px; border: 1px solid rgba(25, 135, 84, .7); background: rgba(25, 135, 84, .25); }
.hero-preview .pv-badge::before { content: ""; width: .4rem; height: .4rem; border-radius: 50%; background: #2ecc71; }
.hero-image-field .custom-control { margin-top: .35rem; }
@media (min-width: 1200px) { .hero-preview-sticky { position: sticky; top: 1rem; } }
CSS);

$preview = static function (string $device) use ($model, $defaultUrl): string {
    $title = Html::tag('span', Html::encode($model->title), ['data-pv' => 'title'])
        . ' ' . Html::tag('span', Html::encode($model->title_accent), ['class' => 'pv-accent', 'data-pv' => 'title_accent']);

    return Html::tag('div',
        Html::tag('div',
            Html::tag('div', Html::encode($model->eyebrow), ['class' => 'pv-eyebrow', 'data-pv' => 'eyebrow'])
            . Html::tag('div', $title, ['class' => 'pv-title'])
            . Html::tag('div', Html::encode($model->subtitle), ['class' => 'pv-subtitle', 'data-pv' => 'subtitle'])
            . Html::tag('span', Html::encode($model->badge), ['class' => 'pv-badge', 'data-pv' => 'badge'])
        ),
        [
            'class' => "hero-preview hero-preview-$device",
            'id' => "hero-preview-$device",
            'data-default' => $defaultUrl[$device],
            'data-current' => HeroSettings::imageUrl($device),
            'style' => sprintf("background-image: url('%s'); background-position: %s center; --overlay: %s;",
                HeroSettings::imageUrl($device), $model->position, (int)$model->overlay / 100),
        ]
    );
};
?>
<div class="hero-settings">
    <?php $form = ActiveForm::begin([
        'id' => 'hero-form',
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <div class="row">
        <div class="col-xl-5">
            <div class="card card-lightblue">
                <div class="card-header"><h3 class="card-title">Фото фону</h3></div>
                <div class="card-body">
                    <p class="text-muted small">
                        JPG, PNG або WebP до 15 МБ. Фото автоматично зменшується і стискається,
                        тож можна завантажувати оригінал. Краще темні фото: текст шапки білий.
                    </p>

                    <div class="hero-image-field mb-3">
                        <?= $form->field($model, 'imageDesktop', ['options' => ['class' => 'mb-0']])
                            ->fileInput(['accept' => 'image/jpeg,image/png,image/webp', 'class' => 'form-control-file js-hero-file', 'data-device' => 'desktop'])
                            ->hint('Рекомендовано ~1920×800, горизонтальне. Показується на екранах від 768 px.') ?>
                        <?php if (HeroSettings::hasCustomImage('desktop')): ?>
                            <?= $form->field($model, 'resetDesktop', ['options' => ['class' => 'mb-0']])
                                ->checkbox(['class' => 'js-hero-reset', 'data-device' => 'desktop']) ?>
                        <?php endif; ?>
                    </div>

                    <div class="hero-image-field mb-3">
                        <?= $form->field($model, 'imageMobile', ['options' => ['class' => 'mb-0']])
                            ->fileInput(['accept' => 'image/jpeg,image/png,image/webp', 'class' => 'form-control-file js-hero-file', 'data-device' => 'mobile'])
                            ->hint('Рекомендовано ~1080×1400, вертикальне. Показується на телефонах.') ?>
                        <?php if (HeroSettings::hasCustomImage('mobile')): ?>
                            <?= $form->field($model, 'resetMobile', ['options' => ['class' => 'mb-0']])
                                ->checkbox(['class' => 'js-hero-reset', 'data-device' => 'mobile']) ?>
                        <?php endif; ?>
                    </div>

                    <?= $form->field($model, 'overlay', [
                        'template' => "{label} <span class=\"badge badge-secondary\" id=\"overlay-value\">" . (int)$model->overlay . "%</span>\n{input}\n{hint}\n{error}",
                    ])->input('range', ['min' => 0, 'max' => 80, 'step' => 5, 'class' => 'custom-range'])
                        ->hint('Якщо текст погано видно на світлому фото — збільште.') ?>

                    <?= $form->field($model, 'position')->dropDownList(HeroSettings::POSITIONS)
                        ->hint('Фото заповнює шапку й обрізається з боків; ця частина завжди буде видна.') ?>
                </div>
            </div>

            <div class="card card-lightblue">
                <div class="card-header"><h3 class="card-title">Тексти</h3></div>
                <div class="card-body">
                    <p class="text-muted small">Порожнє поле — показується стандартний текст.</p>
                    <?= $form->field($model, 'eyebrow')->textInput(['maxlength' => 60, 'data-pv-input' => 'eyebrow', 'placeholder' => HeroSettings::DEFAULTS['eyebrow']]) ?>
                    <?= $form->field($model, 'title')->textInput(['maxlength' => 80, 'data-pv-input' => 'title', 'placeholder' => HeroSettings::DEFAULTS['title']]) ?>
                    <?= $form->field($model, 'title_accent')->textInput(['maxlength' => 30, 'data-pv-input' => 'title_accent', 'placeholder' => HeroSettings::DEFAULTS['title_accent']]) ?>
                    <?= $form->field($model, 'subtitle')->textInput(['maxlength' => 120, 'data-pv-input' => 'subtitle', 'placeholder' => HeroSettings::DEFAULTS['subtitle']]) ?>
                    <?= $form->field($model, 'badge')->textInput(['maxlength' => 60, 'data-pv-input' => 'badge', 'placeholder' => HeroSettings::DEFAULTS['badge']]) ?>
                </div>
                <div class="card-footer text-right">
                    <?= Html::submitButton('<i class="fas fa-save mr-1"></i>Зберегти', ['class' => 'btn btn-info']) ?>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card hero-preview-sticky">
                <div class="card-header">
                    <h3 class="card-title">Перегляд</h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-external-link-alt mr-1"></i>Відкрити сайт', Yii::$app->homeUrl, ['target' => '_blank', 'rel' => 'noopener', 'class' => 'btn btn-tool']) ?>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">ПК</p>
                    <?= $preview('desktop') ?>

                    <p class="text-muted small mt-3 mb-2">Телефон</p>
                    <div class="d-flex align-items-start">
                        <?= $preview('mobile') ?>
                        <p class="text-muted small ml-3 mb-0">
                            Перегляд приблизний: на сайті шапка підлаштовується під екран, тож межі обрізання фото можуть трохи відрізнятись.
                            Зміни з'являться на сайті після натискання «Зберегти».
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$defaults = json_encode(HeroSettings::DEFAULTS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$this->registerJs(<<<JS
(function () {
    const defaults = $defaults;
    const previews = document.querySelectorAll('.hero-preview');
    const objectUrls = {};

    // тексти: порожнє поле — стандартний текст, як і на сайті
    document.querySelectorAll('[data-pv-input]').forEach((input) => {
        const key = input.dataset.pvInput;
        input.addEventListener('input', () => {
            const text = input.value.trim() || defaults[key];
            document.querySelectorAll('[data-pv="' + key + '"]').forEach((el) => { el.textContent = text; });
        });
    });

    const overlay = document.getElementById('heroform-overlay');
    overlay.addEventListener('input', () => {
        document.getElementById('overlay-value').textContent = overlay.value + '%';
        previews.forEach((p) => p.style.setProperty('--overlay', overlay.value / 100));
    });

    const position = document.getElementById('heroform-position');
    position.addEventListener('change', () => {
        previews.forEach((p) => { p.style.backgroundPosition = position.value + ' center'; });
    });

    const setImage = (device, url) => {
        document.getElementById('hero-preview-' + device).style.backgroundImage = "url('" + url + "')";
    };

    // нове фото — одразу в перегляді (ще до збереження)
    document.querySelectorAll('.js-hero-file').forEach((input) => {
        input.addEventListener('change', () => {
            const device = input.dataset.device;
            const preview = document.getElementById('hero-preview-' + device);
            if (objectUrls[device]) URL.revokeObjectURL(objectUrls[device]);
            const file = input.files[0];
            if (file) {
                objectUrls[device] = URL.createObjectURL(file);
                setImage(device, objectUrls[device]);
                const reset = document.querySelector('.js-hero-reset[data-device="' + device + '"]');
                if (reset) reset.checked = false;
            } else {
                setImage(device, preview.dataset.current);
            }
        });
    });

    document.querySelectorAll('.js-hero-reset').forEach((box) => {
        box.addEventListener('change', () => {
            const device = box.dataset.device;
            const preview = document.getElementById('hero-preview-' + device);
            const file = document.querySelector('.js-hero-file[data-device="' + device + '"]');
            if (box.checked && file) file.value = '';
            setImage(device, box.checked ? preview.dataset.default : preview.dataset.current);
        });
    });
})();
JS);
