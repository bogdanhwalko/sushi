<?php

use yii\helpers\Html;

/** @var \app\models\User|null $user */
$user = Yii::$app->user->identity;
$hasProfile = $user !== null && !$user->isAdmin();
?>
<?= Html::beginTag('div', [
    'class' => 'modal fade',
    'id' => 'checkoutModal',
    'tabindex' => '-1',
    'aria-hidden' => 'true',
    // дані з профілю мають пріоритет над збереженими в браузері
    'data-profile' => $hasProfile ? '1' : null,
]) ?>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Оформлення замовлення</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="checkoutName" class="form-label">Прізвище та ім'я</label>
                    <?= Html::textInput('name', $hasProfile ? $user->getFullName() : null, [
                        'class' => 'form-control',
                        'id' => 'checkoutName',
                        'placeholder' => 'Напр. Іваненко Іван',
                        'autocomplete' => 'name',
                        'required' => true,
                    ]) ?>
                    <div class="invalid-feedback">Вкажіть коректне прізвище та ім'я.</div>
                </div>
                <div class="mb-3">
                    <label for="checkoutPhone" class="form-label">Номер телефону</label>
                    <?= Html::input('tel', 'phone', $hasProfile && $user->phone ? '+' . $user->phone : '+38', [
                        'class' => 'form-control',
                        'id' => 'checkoutPhone',
                        'placeholder' => '+380 (XX) XXX-XX-XX',
                        'autocomplete' => 'tel',
                        'required' => true,
                    ]) ?>
                    <div class="invalid-feedback">Вкажіть коректний номер телефону.</div>
                </div>
                <div class="mb-3">
                    <label for="checkoutCity" class="form-label">Локація</label>
                    <?= Html::dropDownList('city', $hasProfile ? $user->city_id : null, $this->params['cities'], [
                        'class' => 'form-select city-dropdown',
                        'id' => 'checkoutCity'
                    ])?>
                    <div class="invalid-feedback">Вказаний заклад відсутній у списку</div>
                </div>
                <div class="alert alert-danger small py-2 d-none" id="checkoutErrors" role="alert"></div>
                <button class="btn btn-dark w-100" type="submit" id="order-confirm-button">Підтвердити замовлення</button>
                <p class="text-muted small mt-2 mb-0">Менеджер зателефонує для підтвердження деталей.</p>
                <?php if (Yii::$app->user->isGuest): ?>
                    <p class="small mt-2 mb-0">
                        <?= Html::a('Увійдіть', ['/account/login']) ?> або
                        <?= Html::a('зареєструйтеся', ['/account/signup']) ?>, щоб зберігати історію замовлень.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?= Html::endTag('div') ?>
