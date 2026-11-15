<?php

use app\components\WeatherEffect;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\admin\forms\WeatherForm $model */

$this->title = 'Ефекти на сайті';
$this->params['breadcrumbs'][] = $this->title;

$icons = [
    'off' => 'fas fa-ban',
    'snow' => 'fas fa-snowman',
    'flakes' => 'far fa-snowflake',
    'rain' => 'fas fa-cloud-rain',
    'leaves' => 'fab fa-canadian-maple-leaf',
    'sakura' => 'fas fa-spa',
    'confetti' => 'fas fa-glass-cheers',
    'hearts' => 'fas fa-heart',
];

// швидкий вибір кольору: світлі добре видно на темній шапці, насичені — на білому фоні сторінки
$presets = [
    '#ffffff' => 'Білий',
    '#d6ecff' => 'Світло-блакитний',
    '#9cc8f2' => 'Блакитний',
    '#5b8fd1' => 'Синій',
    '#b8c2cf' => 'Сріблястий',
    '#7d8a9c' => 'Сірий',
];

$colorId = Html::getInputId($model, 'color');
$pickerValue = $model->color ?: (WeatherEffect::DEFAULT_COLORS[$model->mode] ?? '#ffffff');

$this->registerCss(<<<CSS
.weather-choices { display: grid; grid-template-columns: repeat(auto-fill, minmax(96px, 1fr)); gap: .6rem; }
.weather-choice { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .35rem; margin: 0;
    padding: .75rem .35rem; border: 2px solid #dee2e6; border-radius: .5rem; cursor: pointer; font-weight: 600; font-size: .85rem;
    text-align: center; user-select: none; }
.weather-choice i { font-size: 1.5rem; color: #6c757d; }
.weather-choice input { position: absolute; opacity: 0; pointer-events: none; }
.weather-choice.is-checked { border-color: #3c8dbc; background: #eef6fb; }
.weather-choice.is-checked i { color: #3c8dbc; }

.color-field .form-control-color { width: 3rem; padding: .2rem; height: calc(1.5em + .75rem + 2px); }
.weather-presets { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .5rem; }
.weather-preset { width: 1.75rem; height: 1.75rem; border-radius: 50%; border: 2px solid #ced4da; padding: 0; cursor: pointer; }
.weather-preset.is-active { border-color: #3c8dbc; box-shadow: 0 0 0 2px rgba(60, 141, 188, .35); }

.pv-frame { position: relative; overflow: hidden; border-radius: .5rem; border: 1px solid #dee2e6; background: #0d0f10; }
.pv-frame iframe { position: absolute; top: 0; left: 0; border: 0; transform-origin: 0 0; pointer-events: none; }
.pv-frame.is-loading::after { content: "Завантаження…"; position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #adb5bd; background: #0d0f10; font-size: .85rem; }
.pv-mobile { width: 220px; flex-shrink: 0; border-radius: 1.25rem; border-width: 6px; border-color: #343a40; }
.pv-label { font-size: .8rem; color: #6c757d; margin-bottom: .35rem; }
CSS);
?>
<div class="weather-settings row">
    <div class="col-lg-5 col-xl-4">
        <?php $form = ActiveForm::begin(['id' => 'weather-form']); ?>
        <div class="card card-lightblue">
            <div class="card-header"><h3 class="card-title">Ефект на сайті</h3></div>
            <div class="card-body">
                <?= $form->field($model, 'mode')->radioList(WeatherEffect::MODES, [
                    'class' => 'weather-choices',
                    'item' => static function ($index, $label, $name, $checked, $value) use ($icons) {
                        return Html::label(
                            Html::radio($name, $checked, ['value' => $value])
                            . Html::tag('i', '', ['class' => $icons[$value] ?? 'fas fa-circle'])
                            . Html::encode($label),
                            null,
                            ['class' => 'weather-choice' . ($checked ? ' is-checked' : '')]
                        );
                    },
                ])->label(false) ?>

                <div class="js-weather-options">
                    <div class="js-weather-color">
                        <?= $form->field($model, 'color', [
                            'options' => ['class' => 'form-group color-field'],
                            'inputTemplate' => '<div class="input-group" style="max-width: 320px">'
                                . '<div class="input-group-prepend">'
                                . Html::input('color', null, $pickerValue, ['class' => 'form-control form-control-color', 'id' => 'weather-color-picker', 'title' => 'Вибрати колір'])
                                . '</div>{input}<div class="input-group-append">'
                                . Html::button('Стандартний', ['class' => 'btn btn-outline-secondary', 'id' => 'weather-color-reset'])
                                . '</div></div>'
                                . '<div class="weather-presets">'
                                . implode('', array_map(
                                    static fn ($hex, $label) => Html::button('', [
                                        'class' => 'weather-preset',
                                        'style' => "background: $hex",
                                        'title' => $label,
                                        'data-color' => $hex,
                                    ]),
                                    array_keys($presets),
                                    $presets
                                ))
                                . '</div>',
                        ])->textInput(['maxlength' => 7, 'placeholder' => 'стандартний'])
                            ->hint('Для снігу, сніжинок і дощу. Темніший контур додається автоматично, тож навіть білий сніг видно на білому фоні.') ?>
                    </div>
                    <p class="text-muted small js-weather-palette" style="display: none">
                        <i class="fas fa-palette mr-1"></i>Кольори цього ефекту підібрані заздалегідь.
                    </p>

                    <?= $form->field($model, 'intensity')->dropDownList(WeatherEffect::INTENSITIES) ?>

                    <?= $form->field($model, 'mobile')->checkbox()
                        ->hint('На слабких телефонах анімація може трохи навантажувати батарею.') ?>
                </div>

                <p class="text-muted small mb-0">
                    Ефект не заважає натискати кнопки й посилання, стає на паузу, коли вкладка неактивна,
                    і не показується тим, хто в налаштуваннях телефона чи комп'ютера вимкнув анімації.
                    Коли вимкнено — на сайт нічого не завантажується.
                </p>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <span class="text-muted small">Перегляд — одразу, на сайті — після збереження.</span>
                <?= Html::submitButton('<i class="fas fa-save mr-1"></i>Зберегти', ['class' => 'btn btn-info']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <div class="col-lg-7 col-xl-8">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mr-auto">Перегляд</h3>
                <?= Html::a('<i class="fas fa-external-link-alt"></i>', Yii::$app->homeUrl, ['target' => '_blank', 'rel' => 'noopener', 'class' => 'btn btn-tool', 'title' => 'Відкрити сайт']) ?>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-start flex-wrap" style="gap: 1rem">
                    <div class="flex-grow-1" style="min-width: 280px">
                        <div class="pv-label">ПК (1440 px) — головна від шапки до меню, щоб було видно і темне, і світле тло</div>
                        <div class="pv-frame pv-desktop is-loading" data-width="1440" data-height="900">
                            <iframe title="Перегляд на ПК" tabindex="-1"></iframe>
                        </div>
                    </div>
                    <div>
                        <div class="pv-label">Телефон (390 px)</div>
                        <div class="pv-frame pv-mobile is-loading" data-width="390" data-height="780">
                            <iframe title="Перегляд на телефоні" tabindex="-1"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$json = static fn ($v) => json_encode($v, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$config = $json([
    'page' => Url::to(['/site/index']),
    'script' => Yii::getAlias('@web/') . WeatherEffect::SCRIPT,
    'defaultColors' => WeatherEffect::DEFAULT_COLORS,
    'colorId' => $colorId,
]);
$this->registerJs(<<<JS
(function () {
    const cfg = $config;
    const form = document.getElementById('weather-form');
    const colorInput = document.getElementById(cfg.colorId);
    const picker = document.getElementById('weather-color-picker');
    const frames = Array.from(document.querySelectorAll('.pv-frame'));
    const isColor = (v) => /^#[0-9a-f]{6}$/i.test(v);
    const PREVIEW_CSS = '.modal,.modal-backdrop,.offcanvas,.offcanvas-backdrop,.toast-container,#yii-debug-toolbar,.compact-menu-scroll-top{display:none!important}'
        + 'html,body{overflow:hidden!important}body{padding-right:0!important}';

    const state = () => {
        const checked = form.querySelector('input[name="WeatherForm[mode]"]:checked');
        const mobile = form.querySelector('input[type="checkbox"][name="WeatherForm[mobile]"]');
        return {
            mode: checked ? checked.value : 'off',
            intensity: parseInt(form.elements['WeatherForm[intensity]'].value, 10) || 2,
            mobile: !!(mobile && mobile.checked),
            color: isColor(colorInput.value.trim()) ? colorInput.value.trim().toLowerCase() : '',
            force: true, // у перегляді показуємо навіть якщо в адміна в системі вимкнено анімації
        };
    };

    // у сторінці сайту може не бути ні полотна, ні скрипта (якщо ефект зараз вимкнено) — додаємо
    const apply = (frame) => {
        const win = frame.querySelector('iframe').contentWindow;
        const doc = win && win.document;
        if (!doc || !doc.body) return;

        if (!doc.getElementById('weather-preview-css')) {
            const style = doc.createElement('style');
            style.id = 'weather-preview-css';
            style.textContent = PREVIEW_CSS;
            doc.head.appendChild(style);
        }
        if (!doc.getElementById('weatherLayer')) {
            const canvas = doc.createElement('canvas');
            canvas.id = 'weatherLayer';
            canvas.className = 'weather-layer';
            canvas.dataset.mode = 'off';
            doc.body.insertBefore(canvas, doc.body.firstChild);
        }
        if (win.siteWeather) {
            win.siteWeather.set(state());
        } else if (!doc.getElementById('weather-preview-js')) {
            const script = doc.createElement('script');
            script.id = 'weather-preview-js';
            script.src = cfg.script;
            script.onload = () => win.siteWeather && win.siteWeather.set(state());
            doc.body.appendChild(script);
        }
    };
    const applyAll = () => frames.forEach(apply);

    const fit = () => frames.forEach((frame) => {
        const iframe = frame.querySelector('iframe');
        const w = +frame.dataset.width, h = +frame.dataset.height;
        const scale = frame.clientWidth / w;
        iframe.style.width = w + 'px';
        iframe.style.height = h + 'px';
        iframe.style.transform = 'scale(' + scale + ')';
        frame.style.height = Math.round(h * scale) + 'px';
    });

    frames.forEach((frame) => {
        const iframe = frame.querySelector('iframe');
        iframe.addEventListener('load', () => {
            apply(frame);
            frame.classList.remove('is-loading');
        });
        iframe.src = cfg.page;
    });
    window.addEventListener('resize', fit);
    fit();

    // ---------- форма ----------
    const sync = () => {
        const s = state();
        const colorable = s.mode in cfg.defaultColors;
        form.querySelectorAll('.weather-choice').forEach((label) => {
            label.classList.toggle('is-checked', label.querySelector('input').checked);
        });
        form.querySelector('.js-weather-options').style.opacity = s.mode === 'off' ? '.5' : '';
        form.querySelector('.js-weather-color').style.display = colorable || s.mode === 'off' ? '' : 'none';
        form.querySelector('.js-weather-palette').style.display = colorable || s.mode === 'off' ? 'none' : '';
        // без свого кольору палітра показує стандартний для вибраного ефекту
        const shown = s.color || cfg.defaultColors[s.mode] || '#ffffff';
        if (picker.value !== shown) picker.value = shown;
        form.querySelectorAll('.weather-preset').forEach((b) => b.classList.toggle('is-active', b.dataset.color === s.color));
        applyAll();
    };

    picker.addEventListener('input', () => { colorInput.value = picker.value; sync(); });
    colorInput.addEventListener('input', sync);
    document.getElementById('weather-color-reset').addEventListener('click', () => { colorInput.value = ''; sync(); });
    form.querySelectorAll('.weather-preset').forEach((btn) => {
        btn.addEventListener('click', () => { colorInput.value = btn.dataset.color; sync(); });
    });
    form.addEventListener('change', sync);
    sync();
})();
JS);
