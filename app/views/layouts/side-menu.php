<?php

use yii\helpers\Html;
use yii\helpers\Url;

$navItems = $this->params['sideMenuLinks'] ?? [
    ['label' => 'Меню', 'href' => '#menu'],
    ['label' => 'Про нас', 'href' => '#about'],
    ['label' => 'Контакти', 'href' => '#contact'],
];

?>

<div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="sideMenu" aria-labelledby="sideMenuLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sideMenuLabel">Навігація</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column gap-3">
        <?php foreach ($navItems as $navItem): ?>
            <?= Html::a(
                Html::encode($navItem['label']),
                $navItem['href'],
                [
                    'class' => 'text-white text-decoration-none fw-semibold',
                    'data-bs-dismiss' => 'offcanvas',
                ]
            ) ?>
        <?php endforeach; ?>
        <div class="border-top pt-3">
            <label for="cityWelcomeSelect" class="form-label">Локація</label>
            <?= Html::dropDownList('city', null, $this->params['cities'], [
                'class' => 'form-select form-select-sm side-city-select city-dropdown',
                'id' => 'sideCitySelector',
            ]) ?>
            <div class="invalid-feedback">Вказаний заклад відсутній у списку</div>
        </div>
        <div class="border-top pt-3 text-white-50 small">
            <div>Графік: <?= Yii::$app->params['schedule'] ?></div>
        </div>
    </div>
</div>
