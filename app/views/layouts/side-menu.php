<?php

use yii\helpers\Html;

?>

<div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="sideMenu" aria-labelledby="sideMenuLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sideMenuLabel">Навігація</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column gap-3">
        <a class="text-white text-decoration-none fw-semibold" href="#menu" data-bs-dismiss="offcanvas">Меню</a>
        <a class="text-white text-decoration-none fw-semibold" href="#about" data-bs-dismiss="offcanvas">Про нас</a>
        <a class="text-white text-decoration-none fw-semibold" href="#contact" data-bs-dismiss="offcanvas">Контакти</a>
        <div class="border-top pt-3">
<!--            <div class="city-select-label">Місто</div>-->
<!--            --><?php //= Html::dropDownList(
//                'cities_in_menu',
//                'all',
//                \app\models\Cities::getCitiesInSelect(),
//                [
//                    'class' => 'form-select form-select-sm side-city-select',
//                    'id' => 'sideCitySelector'
//                ]
//            )?>
        </div>
        <div class="border-top pt-3 text-white-50 small">
            <div>Графік: <?= Yii::$app->params['schedule'] ?></div>
        </div>
    </div>
</div>