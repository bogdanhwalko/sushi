<?php

/** @var yii\web\View $this */
/** @var app\models\User $user */
/** @var app\forms\ProfileForm $profile */
/** @var app\forms\ChangePasswordForm $password */
/** @var yii\data\ActiveDataProvider $ordersProvider */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;

$this->title = 'Особистий кабінет';

$fullName = $user->getFullName() ?: $user->username;
$orders = $ordersProvider->getModels();

// після невдалого збереження форма лишається відкритою, щоб було видно помилки
$profileEditing = $profile->hasErrors();
$passwordEditing = $password->hasErrors();

$notSet = '<span class="text-muted">не вказано</span>';
$details = [
    'ПІБ' => Html::encode($fullName),
    'Телефон' => $user->phone ? Html::encode('+' . $user->phone) : $notSet,
    'Email' => $user->email ? Html::encode($user->email) : $notSet,
    'Місто' => $user->city ? Html::encode($user->city->name) : $notSet,
    'Логін' => Html::encode($user->username),
];

$this->registerJs(<<<JS
document.querySelectorAll('[data-edit-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const box = document.getElementById(btn.dataset.editToggle);
        const editing = box.classList.toggle('is-editing');
        const form = box.querySelector('form');

        if (editing) {
            const field = box.querySelector('.account-edit input:not([type=hidden]), .account-edit select');
            if (field) field.focus();
        } else if (form) {
            form.reset();
            form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
        }
    });
});
JS);
?>

<div class="account-wrap">
    <div class="account-hero">
        <p class="eyebrow text-muted mb-1">Особистий кабінет</p>
        <h1 class="account-title"><?= Html::encode($fullName) ?></h1>
    </div>

    <?php if ($user->isAdmin()): ?>
        <div class="alert alert-secondary d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span>Ви адміністратор — так цю сторінку бачать клієнти.</span>
            <?= Html::a('До адмінки', ['/admin-panel/'], ['class' => 'btn btn-sm btn-dark']) ?>
        </div>
    <?php endif; ?>

    <!-- Історія замовлень -->
    <section class="account-section" id="orders">
        <div class="account-section-head">
            <h2 class="h5 fw-bold mb-0">Мої замовлення</h2>
            <?php if ($ordersProvider->getTotalCount() > 0): ?>
                <span class="text-muted small">Всього: <?= (int)$ordersProvider->getTotalCount() ?></span>
            <?php endif; ?>
        </div>

        <?php if (empty($orders)): ?>
            <div class="account-panel account-empty">
                <p class="text-muted mb-3">У вас ще немає замовлень.</p>
                <?= Html::a('Перейти до меню', Yii::$app->homeUrl, ['class' => 'btn btn-dark']) ?>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($orders as $order): ?>
                    <?= $this->render('_order_card', ['order' => $order]) ?>
                <?php endforeach; ?>
            </div>

            <?php if ($ordersProvider->getPagination()->getPageCount() > 1): ?>
                <div class="mt-3 d-flex justify-content-center">
                    <?= LinkPager::widget([
                        'pagination' => $ordersProvider->getPagination(),
                        'maxButtonCount' => 5,
                        'options' => ['class' => 'pagination pagination-sm mb-0'],
                    ]) ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <!-- Особисті дані -->
    <section class="account-section account-editable<?= $profileEditing ? ' is-editing' : '' ?>" id="profile-box">
        <div class="account-section-head">
            <h2 class="h5 fw-bold mb-0">Особисті дані</h2>
            <button type="button" class="btn btn-sm btn-outline-dark account-view" data-edit-toggle="profile-box">
                Змінити
            </button>
        </div>

        <div class="account-panel">
            <dl class="account-details account-view mb-0">
                <?php foreach ($details as $label => $value): ?>
                    <div>
                        <dt><?= $label ?></dt>
                        <dd><?= $value ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>

            <div class="account-edit">
                <?php $form = ActiveForm::begin(['id' => 'profile-form', 'action' => ['/account/profile', '#' => 'profile-box']]); ?>

                <?= $this->render('_profile_fields', ['form' => $form, 'model' => $profile]) ?>

                <div class="account-form-actions">
                    <?= Html::submitButton('Зберегти', ['class' => 'btn btn-dark']) ?>
                    <button type="button" class="btn btn-link text-muted" data-edit-toggle="profile-box">Скасувати</button>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </section>

    <!-- Пароль -->
    <section class="account-section account-editable<?= $passwordEditing ? ' is-editing' : '' ?>" id="password-box">
        <div class="account-section-head">
            <h2 class="h5 fw-bold mb-0">Пароль</h2>
            <button type="button" class="btn btn-sm btn-outline-dark account-view" data-edit-toggle="password-box">
                Змінити
            </button>
        </div>

        <div class="account-panel">
            <p class="account-view text-muted mb-0">••••••••</p>

            <div class="account-edit">
                <?php $form = ActiveForm::begin(['id' => 'password-form', 'action' => ['/account/profile', '#' => 'password-box']]); ?>

                <div class="row g-3">
                    <div class="col-md-4">
                        <?= $form->field($password, 'current_password')->passwordInput(['autocomplete' => 'current-password']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($password, 'new_password')
                            ->passwordInput(['autocomplete' => 'new-password'])
                            ->hint('Щонайменше 8 символів') ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($password, 'new_password_repeat')->passwordInput(['autocomplete' => 'new-password']) ?>
                    </div>
                </div>

                <div class="account-form-actions">
                    <?= Html::submitButton('Змінити пароль', ['class' => 'btn btn-dark']) ?>
                    <button type="button" class="btn btn-link text-muted" data-edit-toggle="password-box">Скасувати</button>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </section>
</div>
