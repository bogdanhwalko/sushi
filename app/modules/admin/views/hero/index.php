<?php

use app\components\HeroSettings;
use app\models\HeroHistory;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\admin\forms\HeroForm $model */
/** @var HeroHistory[] $history */

$this->title = 'Шапка сайту';
$this->params['breadcrumbs'][] = $this->title;

$web = Yii::getAlias('@web/');

$this->registerCss(<<<CSS
/* живий перегляд: справжня сторінка сайту, зменшена до ширини колонки */
.pv-frame { position: relative; overflow: hidden; border-radius: .5rem; border: 1px solid #dee2e6; background: #0d0f10; }
.pv-frame iframe { position: absolute; top: 0; left: 0; border: 0; transform-origin: 0 0; pointer-events: none; }
.pv-frame.is-loading::after { content: "Завантаження…"; position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #adb5bd; background: #0d0f10; font-size: .85rem; }
.pv-mobile { width: 250px; flex-shrink: 0; border-radius: 1.25rem; border-width: 6px; border-color: #343a40; }
.pv-label { font-size: .8rem; color: #6c757d; margin-bottom: .35rem; }
@media (min-width: 1200px) { .hero-preview-sticky { position: sticky; top: 1rem; } }

.color-field .form-control-color { width: 3rem; padding: .2rem; height: calc(1.5em + .75rem + 2px); }
.color-field .input-group { max-width: 320px; }

.hero-history .thumb { display: inline-block; border-radius: .25rem; border: 1px solid #dee2e6; background: #0d0f10 center / cover no-repeat; vertical-align: middle; }
.hero-history .thumb-desktop { width: 96px; height: 42px; }
.hero-history .thumb-mobile { width: 30px; height: 42px; margin-left: .25rem; }
.hero-history .swatch { display: inline-block; width: .9rem; height: .9rem; border-radius: 50%; border: 1px solid rgba(0,0,0,.2); vertical-align: -2px; }
.hero-history tr.is-current { background: #e8f4fd; }
.hero-history .thumb-logo { width: 54px; height: 42px; margin-left: .25rem; background-size: contain; background-color: #1f2328; }

/* логотип показуємо на темному, як у шапці */
.logo-current { display: inline-flex; align-items: center; justify-content: center; min-width: 140px; height: 84px; padding: .5rem 1rem; border-radius: .5rem; background: #0d0f10; }
.logo-current img { max-height: 64px; max-width: 220px; object-fit: contain; }
CSS);
?>
<div class="hero-settings">
    <?php $form = ActiveForm::begin([
        'id' => 'hero-form',
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <div class="row">
        <div class="col-xl-5">
            <div class="card card-lightblue">
                <div class="card-header"><h3 class="card-title">Логотип</h3></div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="logo-current mr-3">
                            <?= Html::img(HeroSettings::logoUrl(), ['alt' => 'Поточний логотип', 'id' => 'logo-current']) ?>
                        </span>
                        <span class="text-muted small">
                            <?= HeroSettings::get('logo') === '' ? 'Стандартний логотип' : 'Свій логотип' ?>.
                            Показується в шапці на головній, у меню й у кабінеті.
                        </span>
                    </div>
                    <?= $form->field($model, 'logoFile', ['options' => ['class' => 'mb-0']])
                        ->fileInput(['accept' => 'image/png,image/webp,image/jpeg', 'class' => 'form-control-file js-hero-file', 'data-device' => 'logo'])
                        ->hint('PNG або WebP з прозорим фоном, до 5 МБ. Світлий логотип: шапка темна. Висота на сайті — до 70 px, ширина — до 200 px.') ?>
                    <?php if (HeroSettings::get('logo') !== ''): ?>
                        <?= $form->field($model, 'resetLogo', ['options' => ['class' => 'mb-0']])
                            ->checkbox(['class' => 'js-hero-reset', 'data-device' => 'logo']) ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card card-lightblue">
                <div class="card-header"><h3 class="card-title">Фото фону</h3></div>
                <div class="card-body">
                    <p class="text-muted small">
                        JPG, PNG або WebP до 15 МБ. Фото автоматично зменшується і стискається,
                        тож можна завантажувати оригінал. Попередні фото лишаються в історії.
                    </p>

                    <div class="mb-3">
                        <?= $form->field($model, 'imageDesktop', ['options' => ['class' => 'mb-0']])
                            ->fileInput(['accept' => 'image/jpeg,image/png,image/webp', 'class' => 'form-control-file js-hero-file', 'data-device' => 'desktop'])
                            ->hint('Рекомендовано ~1920×800, горизонтальне. Показується на екранах від 768 px.') ?>
                        <?php if (HeroSettings::hasCustomImage('desktop')): ?>
                            <?= $form->field($model, 'resetDesktop', ['options' => ['class' => 'mb-0']])
                                ->checkbox(['class' => 'js-hero-reset', 'data-device' => 'desktop']) ?>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
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
                    ])->input('range', ['min' => 0, 'max' => 80, 'step' => 5, 'class' => 'custom-range js-pv'])
                        ->hint('Якщо текст погано видно на світлому фото — збільште.') ?>

                    <?= $form->field($model, 'position')->dropDownList(HeroSettings::POSITIONS, ['class' => 'form-control js-pv'])
                        ->hint('Фото заповнює шапку й обрізається з боків; ця частина завжди буде видна.') ?>
                </div>
            </div>

            <div class="card card-lightblue">
                <div class="card-header"><h3 class="card-title">Тексти та кольори</h3></div>
                <div class="card-body">
                    <p class="text-muted small">Порожнє поле — показується стандартний текст.</p>
                    <?php foreach (['eyebrow' => 60, 'title' => 80, 'title_accent' => 30, 'subtitle' => 120, 'badge' => 60] as $attr => $max): ?>
                        <?= $form->field($model, $attr)->textInput([
                            'maxlength' => $max,
                            'class' => 'form-control js-pv',
                            'data-pv-text' => $attr,
                            'placeholder' => HeroSettings::DEFAULTS[$attr],
                        ]) ?>
                    <?php endforeach; ?>

                    <?php foreach (['text_color', 'accent_color'] as $attr): ?>
                        <?php $standard = HeroSettings::STANDARD_COLORS[$attr]; ?>
                        <?= $form->field($model, $attr, [
                            'options' => ['class' => 'form-group color-field'],
                            'inputTemplate' => '<div class="input-group">'
                                . '<div class="input-group-prepend">'
                                . Html::input('color', null, $model->$attr ?: $standard, [
                                    'class' => 'form-control form-control-color js-color-picker',
                                    'data-target' => Html::getInputId($model, $attr),
                                    'title' => 'Вибрати колір',
                                ])
                                . '</div>{input}<div class="input-group-append">'
                                . Html::button('Стандартний', [
                                    'class' => 'btn btn-outline-secondary js-color-reset',
                                    'data-target' => Html::getInputId($model, $attr),
                                    'data-standard' => $standard,
                                ])
                                . '</div></div>',
                        ])->textInput([
                            'maxlength' => 7,
                            'class' => 'form-control js-pv js-color-value',
                            'placeholder' => 'стандартний',
                        ])->hint($attr === 'accent_color'
                            ? 'Змінює помаранчевий скрізь на сайті: заголовок, ціни, кнопки, підсвітки фону. Логотип — картинка, його колір не змінюється.'
                            : 'Колір текстів шапки (крім помаранчевої частини заголовка).') ?>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Зміни видно в перегляді одразу, на сайті — після збереження.</span>
                    <?= Html::submitButton('<i class="fas fa-save mr-1"></i>Зберегти', ['class' => 'btn btn-info']) ?>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card hero-preview-sticky">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title mr-auto">Перегляд</h3>
                    <div class="btn-group btn-group-sm mr-2" role="group" aria-label="Сторінка для перегляду">
                        <button type="button" class="btn btn-secondary js-pv-page" data-url="<?= Html::encode(Url::to(['/site/index'])) ?>">Головна</button>
                        <button type="button" class="btn btn-outline-secondary js-pv-page" data-url="<?= Html::encode(Url::to(['/site/menu'])) ?>">Меню</button>
                    </div>
                    <?= Html::a('<i class="fas fa-external-link-alt"></i>', Yii::$app->homeUrl, ['target' => '_blank', 'rel' => 'noopener', 'class' => 'btn btn-tool', 'title' => 'Відкрити сайт']) ?>
                </div>
                <div class="card-body">
                    <div class="pv-label">ПК (1440 px)</div>
                    <div class="pv-frame pv-desktop is-loading" data-width="1440" data-height="640">
                        <iframe title="Перегляд шапки на ПК" tabindex="-1"></iframe>
                    </div>

                    <div class="d-flex align-items-start mt-3">
                        <div>
                            <div class="pv-label">Телефон (390 px)</div>
                            <div class="pv-frame pv-mobile is-loading" data-width="390" data-height="720" data-max-height="780">
                                <iframe title="Перегляд шапки на телефоні" tabindex="-1"></iframe>
                            </div>
                        </div>
                        <p class="text-muted small ml-3 mt-4 mb-0">
                            Це справжня сторінка сайту, зменшена до розміру колонки:
                            нові тексти, кольори, затемнення й вибрані фото підставляються в неї одразу.
                            Відвідувачі побачать зміни лише після натискання «Зберегти».
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

    <!-- Історія -->
    <div class="card hero-history">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mr-auto"><i class="fas fa-history mr-1"></i>Історія змін</h3>
            <?= Html::a('<i class="fas fa-undo mr-1"></i>Скинути все до стандартного', ['reset'], [
                'class' => 'btn btn-sm btn-outline-danger',
                'data-method' => 'post',
                'data-confirm' => 'Повернути стандартні фото, логотип, тексти й кольори? Поточний варіант лишиться в історії.',
            ]) ?>
        </div>
        <div class="card-body p-0">
            <?php if (!$history): ?>
                <p class="text-muted p-3 mb-0">
                    Ще нічого не змінювали. Після першого збереження тут з'являться попередній і новий варіанти,
                    і до будь-якого з них можна буде повернутись.
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                        <tr>
                            <th class="pl-3">Коли</th>
                            <th>Фото й логотип</th>
                            <th>Заголовок</th>
                            <th>Кольори</th>
                            <th class="text-right pr-3"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $currentFound = false; ?>
                        <?php foreach ($history as $row): ?>
                            <?php
                            $v = $row->getValues();
                            $shown = static fn (string $key) => $v[$key] !== '' ? $v[$key] : HeroSettings::DEFAULTS[$key];
                            // однакові варіанти можуть повторюватись (після відновлення) — «поточний» лише найновіший
                            $current = !$currentFound && $row->isCurrent();
                            $currentFound = $currentFound || $current;
                            ?>
                            <tr class="<?= $current ? 'is-current' : '' ?>">
                                <td class="pl-3 align-middle text-nowrap">
                                    <?= Html::encode(Yii::$app->formatter->asDatetime($row->created_at, 'php:d.m.Y H:i')) ?>
                                    <div class="small text-muted"><?= Html::encode($row->user->username ?? '—') ?></div>
                                </td>
                                <td class="align-middle text-nowrap">
                                    <?php foreach (['desktop', 'mobile'] as $device): ?>
                                        <?php $file = $v['image_' . $device]; ?>
                                        <span class="thumb thumb-<?= $device ?>"
                                              style="background-image: url('<?= Html::encode(HeroSettings::imageUrl($device, $v)) ?>')"
                                              title="<?= $device === 'desktop' ? 'ПК' : 'Телефон' ?>: <?= $file === '' ? 'стандартне' : 'своє фото' ?>"></span>
                                    <?php endforeach; ?>
                                    <span class="thumb thumb-logo"
                                          style="background-image: url('<?= Html::encode(HeroSettings::logoUrl($v)) ?>')"
                                          title="Логотип: <?= $v['logo'] === '' ? 'стандартний' : 'свій' ?>"></span>
                                    <div class="small text-muted">
                                        затемнення <?= (int)$v['overlay'] ?>% · <?= Html::encode(HeroSettings::POSITIONS[$shown('position')] ?? '') ?>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <?= Html::encode($shown('title')) ?>
                                    <strong style="color: <?= HeroSettings::isColor($v['accent_color']) ? $v['accent_color'] : HeroSettings::STANDARD_COLORS['accent_color'] ?>">
                                        <?= Html::encode($shown('title_accent')) ?>
                                    </strong>
                                    <div class="small text-muted text-truncate" style="max-width: 420px"><?= Html::encode($shown('eyebrow') . ' · ' . $shown('badge')) ?></div>
                                </td>
                                <td class="align-middle text-nowrap small">
                                    <?php foreach (['text_color' => 'текст', 'accent_color' => 'акцент'] as $key => $label): ?>
                                        <?php $color = HeroSettings::isColor($v[$key]) ? $v[$key] : HeroSettings::STANDARD_COLORS[$key]; ?>
                                        <span class="swatch" style="background: <?= $color ?>"></span>
                                        <span class="mr-2 <?= $v[$key] === '' ? 'text-muted' : '' ?>"><?= $label ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td class="align-middle text-right pr-3">
                                    <?php if ($current): ?>
                                        <span class="badge badge-primary">Зараз на сайті</span>
                                    <?php else: ?>
                                        <?= Html::a('<i class="fas fa-undo mr-1"></i>Відновити', ['restore', 'id' => $row->id], [
                                            'class' => 'btn btn-sm btn-outline-primary',
                                            'data-method' => 'post',
                                            'data-confirm' => 'Повернути шапку до варіанта від '
                                                . Yii::$app->formatter->asDatetime($row->created_at, 'php:d.m.Y H:i') . '?',
                                        ]) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="text-muted small px-3 py-2 mb-0 border-top">
                    Зберігаються останні <?= HeroHistory::KEEP ?> варіантів разом із фото.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$json = static fn ($v) => json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$config = $json([
    'defaults' => HeroSettings::DEFAULTS,
    'standardAccent' => HeroSettings::STANDARD_ACCENT_PALETTE,
    'images' => [
        'desktop' => ['current' => HeroSettings::imageUrl('desktop'), 'standard' => $web . HeroSettings::DEFAULT_IMAGES['desktop']],
        'mobile' => ['current' => HeroSettings::imageUrl('mobile'), 'standard' => $web . HeroSettings::DEFAULT_IMAGES['mobile']],
        'logo' => ['current' => HeroSettings::logoUrl(), 'standard' => $web . HeroSettings::DEFAULT_LOGO],
    ],
]);
$this->registerJs(<<<JS
(function () {
    const cfg = $config;
    const form = document.getElementById('hero-form');
    const frames = Array.from(document.querySelectorAll('.pv-frame'));
    const objectUrls = {};
    const isColor = (v) => /^#[0-9a-f]{6}$/i.test(v);

    // та сама формула, що й HeroSettings::accentPalette() у PHP
    const accentPalette = (hex) => {
        const rgb = [1, 3, 5].map((i) => parseInt(hex.substr(i, 2), 16));
        const mix = (to, t) => rgb.map((c, i) => Math.round(c + (to[i] - c) * t));
        const toHex = (c) => '#' + c.map((v) => v.toString(16).padStart(2, '0')).join('');
        const black = [0, 0, 0], white = [255, 255, 255];
        return {
            '--accent': toHex(rgb),
            '--accent-dark': toHex(mix(black, 0.04)),
            '--accent-rgb': rgb.join(', '),
            '--accent-light-rgb': mix(white, 0.07).join(', '),
            '--accent-glow-rgb': mix(white, 0.14).join(', '),
            '--accent-darker': toHex(mix(black, 0.17)),
            '--accent-deep': toHex(mix(black, 0.45)),
        };
    };

    // ---------- стан форми → те, що підставляємо в сторінку ----------
    const imageUrl = (device) => {
        if (objectUrls[device]) return objectUrls[device];
        const reset = form.querySelector('.js-hero-reset[data-device="' + device + '"]');
        return reset && reset.checked ? cfg.images[device].standard : cfg.images[device].current;
    };

    const state = () => {
        const val = (name) => (form.elements['HeroForm[' + name + ']'] || {}).value || '';
        const texts = {};
        ['eyebrow', 'title', 'title_accent', 'subtitle', 'badge'].forEach((k) => {
            texts[k] = val(k).trim() || cfg.defaults[k];
        });
        return {
            texts: texts,
            overlay: parseInt(val('overlay') || '0', 10),
            position: val('position') || 'left',
            text: isColor(val('text_color')) ? val('text_color') : '',
            accent: isColor(val('accent_color')) ? val('accent_color') : '',
            desktop: imageUrl('desktop'),
            mobile: imageUrl('mobile'),
            logo: imageUrl('logo'),
        };
    };

    // ---------- підстановка в iframe (та сама сторінка сайту, той самий домен) ----------
    const PREVIEW_CSS = '.modal,.modal-backdrop,.offcanvas,.offcanvas-backdrop,.toast-container,#snowLayer,#yii-debug-toolbar,.compact-menu-scroll-top{display:none!important}'
        + 'html,body{overflow:hidden!important}body{padding-right:0!important}';

    const apply = (frame) => {
        const doc = frame.querySelector('iframe').contentDocument;
        const header = doc && doc.querySelector('header.gradient-hero');
        if (!header) return;

        if (!doc.getElementById('hero-preview-css')) {
            const style = doc.createElement('style');
            style.id = 'hero-preview-css';
            style.textContent = PREVIEW_CSS;
            doc.head.appendChild(style);
        }

        const s = state();
        header.style.setProperty('--hero-bg', 'url("' + s.desktop + '")');
        header.style.setProperty('--hero-bg-mobile', 'url("' + s.mobile + '")');
        header.style.setProperty('--hero-overlay', String(s.overlay / 100));
        header.style.setProperty('--hero-position', s.position);
        doc.querySelectorAll('img[data-hero-logo]').forEach((img) => { if (img.getAttribute('src') !== s.logo) img.src = s.logo; });
        header.classList.toggle('hero-custom-text', !!s.text);
        if (s.text) header.style.setProperty('--hero-text', s.text);

        // фірмовий колір — на всю сторінку (ціни, кнопки, підсвітки), як і на сайті
        const palette = s.accent ? accentPalette(s.accent) : cfg.standardAccent;
        Object.keys(palette).forEach((name) => doc.documentElement.style.setProperty(name, palette[name]));

        doc.querySelectorAll('[data-hero]').forEach((el) => {
            const key = el.dataset.hero;
            if (key in s.texts) el.textContent = s.texts[key];
        });

        // показуємо рівно шапку (на телефоні — не вище за ~2 екрани)
        const height = Math.min(header.offsetHeight, +frame.dataset.maxHeight || Infinity);
        if (height > 0 && String(height) !== frame.dataset.height) {
            frame.dataset.height = height;
            fit();
        }
    };

    const applyAll = () => frames.forEach(apply);

    // ---------- масштаб: сторінка шириною 1440/390 px вписується в колонку ----------
    const fit = () => frames.forEach((frame) => {
        const iframe = frame.querySelector('iframe');
        const w = +frame.dataset.width, h = +frame.dataset.height;
        const scale = frame.clientWidth / w;
        iframe.style.width = w + 'px';
        iframe.style.height = h + 'px';
        iframe.style.transform = 'scale(' + scale + ')';
        frame.style.height = Math.round(h * scale) + 'px';
    });

    const load = (url) => frames.forEach((frame) => {
        frame.classList.add('is-loading');
        frame.querySelector('iframe').src = url;
    });

    frames.forEach((frame) => {
        const iframe = frame.querySelector('iframe');
        iframe.addEventListener('load', () => {
            if (!iframe.getAttribute('src')) return; // порожній iframe до першого завантаження
            apply(frame);
            frame.classList.remove('is-loading');
            // скрипти сторінки можуть дописати щось після load — ще раз через мить
            setTimeout(() => apply(frame), 400);
        });
    });

    document.querySelectorAll('.js-pv-page').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.js-pv-page').forEach((b) => {
                b.classList.toggle('btn-secondary', b === btn);
                b.classList.toggle('btn-outline-secondary', b !== btn);
            });
            load(btn.dataset.url);
        });
    });

    window.addEventListener('resize', fit);
    fit();
    load(document.querySelector('.js-pv-page').dataset.url);

    // ---------- поля форми ----------
    form.addEventListener('input', (e) => {
        if (e.target.id === 'heroform-overlay') {
            document.getElementById('overlay-value').textContent = e.target.value + '%';
        }
        applyAll();
    });
    form.addEventListener('change', applyAll);

    document.querySelectorAll('.js-hero-file').forEach((input) => {
        input.addEventListener('change', () => {
            const device = input.dataset.device;
            if (objectUrls[device]) URL.revokeObjectURL(objectUrls[device]);
            objectUrls[device] = input.files[0] ? URL.createObjectURL(input.files[0]) : null;
            const reset = form.querySelector('.js-hero-reset[data-device="' + device + '"]');
            if (reset && input.files[0]) reset.checked = false;
            applyAll();
        });
    });

    document.querySelectorAll('.js-hero-reset').forEach((box) => {
        box.addEventListener('change', () => {
            const file = form.querySelector('.js-hero-file[data-device="' + box.dataset.device + '"]');
            if (box.checked && file && file.value) {
                file.value = '';
                if (objectUrls[box.dataset.device]) URL.revokeObjectURL(objectUrls[box.dataset.device]);
                objectUrls[box.dataset.device] = null;
            }
            applyAll();
        });
    });

    // кольори: палітра ↔ поле зі значенням ↔ «Стандартний»
    document.querySelectorAll('.js-color-picker').forEach((picker) => {
        const input = document.getElementById(picker.dataset.target);
        picker.addEventListener('input', () => { input.value = picker.value; applyAll(); });
        input.addEventListener('input', () => { if (isColor(input.value)) picker.value = input.value.toLowerCase(); });
    });
    document.querySelectorAll('.js-color-reset').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            input.value = '';
            document.querySelector('.js-color-picker[data-target="' + btn.dataset.target + '"]').value = btn.dataset.standard;
            applyAll();
        });
    });
})();
JS);
