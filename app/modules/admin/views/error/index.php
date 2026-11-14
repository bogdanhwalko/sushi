<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Throwable $exception */

use yii\helpers\Html;
use yii\web\HttpException;

$code = $exception instanceof HttpException ? $exception->statusCode : 500;

// зрозумілий заголовок замість "Not Found (#404)" / "Database Exception"
$titles = [
    400 => 'Некоректний запит',
    403 => 'Доступ заборонено',
    404 => 'Сторінку не знайдено',
    405 => 'Метод не дозволено',
];
$name = $titles[$code] ?? ($code >= 500 ? 'Помилка сервера' : $name);
$this->title = $name;
$isAdminLayout = $this->context->layout === 'main';
?>
<div class="<?= $isAdminLayout ? 'content' : 'card' ?>">
    <div class="<?= $isAdminLayout ? 'container-fluid' : 'card-body' ?>">
        <div class="error-page my-4">
            <h2 class="headline <?= $code >= 500 ? 'text-danger' : 'text-warning' ?>"><?= (int)$code ?></h2>

            <div class="error-content">
                <h3>
                    <i class="fas fa-exclamation-triangle <?= $code >= 500 ? 'text-danger' : 'text-warning' ?>"></i>
                    <?= Html::encode($name) ?>
                </h3>

                <p><?= nl2br(Html::encode($message)) ?></p>

                <p class="mb-0">
                    <?php if ($isAdminLayout): ?>
                        <?= Html::a('<i class="fas fa-arrow-left mr-1"></i>Назад', 'javascript:history.back()', ['class' => 'btn btn-sm btn-outline-secondary mr-2']) ?>
                        <?= Html::a('<i class="fas fa-home mr-1"></i>Головна адмінки', ['/admin-panel/'], ['class' => 'btn btn-sm btn-primary']) ?>
                    <?php else: ?>
                        <?= Html::a('На сайт', Yii::$app->homeUrl, ['class' => 'btn btn-sm btn-outline-secondary mr-2']) ?>
                        <?= Html::a('Увійти в адмінку', ['/admin-panel/auth/login'], ['class' => 'btn btn-sm btn-primary']) ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>
