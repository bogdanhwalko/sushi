<?php

use app\components\WeatherEffect;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\admin\forms\WeatherForm $model */

$this->title = 'Сніг і дощ';
$this->params['breadcrumbs'][] = $this->title;

$icons = ['off' => 'fas fa-ban', 'snow' => 'far fa-snowflake', 'rain' => 'fas fa-cloud-rain'];

$this->registerCss(<<<CSS
.weather-choices { display: flex; gap: .75rem; flex-wrap: wrap; }
.weather-choice { flex: 1 1 110px; display: flex; flex-direction: column; align-items: center; gap: .35rem; margin: 0;
    padding: .9rem .5rem; border: 2px solid #dee2e6; border-radius: .5rem; cursor: pointer; font-weight: 600; user-select: none; }
.weather-choice i { font-size: 1.6rem; color: #6c757d; }
.weather-choice input { position: absolute; opacity: 0; pointer-events: none; }
.weather-choice.is-checked { border-color: #3c8dbc; background: #eef6fb; }
.weather-choice.is-checked i { color: #3c8dbc; }

.pv-frame { position: relative; overflow: hidden; border-radius: .5rem; border: 1px solid #dee2e6; background: #0d0f10; }
.pv-frame iframe { position: absolute; top: 0; left: 0; border: 0; transform-origin: 0 0; pointer-events: none; }
.pv-frame.is-loading::after { content: "Завантаження…"; position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #adb5bd; background: #0d0f10; font-size: .85rem; }
.pv-mobile { width: 220px; flex-shrink: 0; border-radius: 1.25rem; border-width: 6px; border-color: #343a40; }
.pv-label { font-size: .8rem; color: #6c757d; margin-bottom: .35rem; }
CSS);
?>
<div class="weather-settings row">
    <div class="col-lg-4">
        <?php $form = ActiveForm::begin(['id' => 'weather-form']); ?>
        <div class="card card-lightblue">
            <div class="card-header"><h3 class="card-title">Ефект на сайті</h3></div>
            <div class="card-body">
                <?= $form->field($model, 'mode')->radioList(WeatherEffect::MODES, [
                    'class' => 'weather-choices',
                    'item' => static function ($index, $label, $name, $checked, $value) use ($icons) {
                        return Html::label(
                            Html::radio($name, $checked, ['value' => $value, 'class' => 'js-weather'])
                            . Html::tag('i', '', ['class' => $icons[$value] ?? 'fas fa-circle'])
                            . Html::encode($label),
                            null,
                            ['class' => 'weather-choice' . ($checked ? ' is-checked' : '')]
                        );
                    },
                ])->label(false) ?>

                <div class="js-weather-options">
                    <?= $form->field($model, 'intensity')->dropDownList(WeatherEffect::INTENSITIES, ['class' => 'form-control js-weather']) ?>

                    <?= $form->field($model, 'mobile')->checkbox(['class' => 'js-weather'])
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

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mr-auto">Перегляд</h3>
                <?= Html::a('<i class="fas fa-external-link-alt"></i>', Yii::$app->homeUrl, ['target' => '_blank', 'rel' => 'noopener', 'class' => 'btn btn-tool', 'title' => 'Відкрити сайт']) ?>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-start flex-wrap" style="gap: 1rem">
                    <div class="flex-grow-1" style="min-width: 280px">
                        <div class="pv-label">ПК (1440 px)</div>
                        <div class="pv-frame pv-desktop is-loading" data-width="1440" data-height="810">
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
]);
$this->registerJs(<<<JS
(function () {
    const cfg = $config;
    const form = document.getElementById('weather-form');
    const frames = Array.from(document.querySelectorAll('.pv-frame'));
    const PREVIEW_CSS = '.modal,.modal-backdrop,.offcanvas,.offcanvas-backdrop,.toast-container,#yii-debug-toolbar,.compact-menu-scroll-top{display:none!important}'
        + 'html,body{overflow:hidden!important}body{padding-right:0!important}';

    const state = () => {
        const checked = form.querySelector('input[name="WeatherForm[mode]"]:checked');
        const mobile = form.querySelector('input[type="checkbox"][name="WeatherForm[mobile]"]');
        return {
            mode: checked ? checked.value : 'off',
            intensity: parseInt(form.elements['WeatherForm[intensity]'].value, 10) || 2,
            mobile: !!(mobile && mobile.checked),
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

    const sync = () => {
        const s = state();
        form.querySelectorAll('.weather-choice').forEach((label) => {
            label.classList.toggle('is-checked', label.querySelector('input').checked);
        });
        form.querySelector('.js-weather-options').style.opacity = s.mode === 'off' ? '.5' : '';
        applyAll();
    };
    form.addEventListener('change', sync);
    sync();
})();
JS);
