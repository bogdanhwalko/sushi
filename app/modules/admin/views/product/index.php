<?php

use app\modules\admin\models\Category;
use app\modules\admin\models\Product;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;

/** @var yii\web\View $this */
/** @var app\modules\admin\models\search\ProductSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $viewMode table | cards */

$this->title = 'Товари';
$this->params['breadcrumbs'][] = $this->title;

\yii\web\YiiAsset::register($this); // data-method / data-confirm для кнопки видалення в картках
$this->registerJsFile('@web/js/admin/productInline.js?v=2', ['depends' => \yii\web\JqueryAsset::class]);
$this->registerCss(<<<CSS
.js-inline-edit { cursor: pointer; border-bottom: 1px dashed transparent; white-space: nowrap; }
.js-inline-edit:hover { border-bottom-color: #adb5bd; }
.js-inline-edit.is-saved { background: #d4edda; transition: background .2s; }
.js-inline-edit.is-editing { cursor: default; border-bottom-color: transparent; display: inline-block; }
.inline-edit-group { width: 10.5rem; flex-wrap: nowrap; }
.inline-edit-group .form-control { min-width: 0; }
.product-admin-card .product-excerpt { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4; }
.product-card-actions { display: flex; align-items: center; background: #fff; border-top: 1px solid rgba(0,0,0,.06); padding: .6rem .75rem; }
.product-card-actions .btn + .btn { margin-left: .4rem; }
.product-card-actions .btn-light { border: 1px solid #dee2e6; color: #495057; width: 2.1rem; }
.product-card-actions .btn-light:hover { background: #e9ecef; }
.product-card-actions .btn-delete:hover { background: #dc3545; border-color: #dc3545; color: #fff; }
.product-card-img { position: relative; display: flex; align-items: center; justify-content: center; aspect-ratio: 1 / 1; overflow: hidden; background: #f4f6f9; color: #ced4da; font-size: 2.5rem; border-radius: .25rem .25rem 0 0; }
.product-card-img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.product-admin-card.is-inactive .product-card-img { opacity: .45; }
.product-admin-card .card-title { font-size: 1rem; line-height: 1.3; }
CSS);

/** Значення, яке змінюється подвійним кліком (ціна, вага) */
$inline = static fn (Product $model, string $field): string => Html::tag('span', Html::encode($model->inlineText($field)), [
    'class' => 'js-inline-edit',
    'data-id' => $model->id,
    'data-field' => $field,
    'data-value' => $model->$field === null ? '' : (int)$model->$field,
    'title' => 'Двічі клацніть, щоб змінити',
]);

$categories = Category::getBySelect();
$statuses = ['Не активний', 'Активний'];
?>
<div class="product-index">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <?= Html::a('Додати товар', ['create'], ['class' => 'btn btn-info mb-2']) ?>

        <div class="btn-group mb-2" role="group" aria-label="Вигляд списку">
            <?= Html::a('<i class="fas fa-list mr-1"></i>Таблиця', Url::current(['view' => 'table']), [
                'class' => 'btn btn-sm ' . ($viewMode === 'table' ? 'btn-secondary' : 'btn-outline-secondary'),
            ]) ?>
            <?= Html::a('<i class="fas fa-th-large mr-1"></i>Картки', Url::current(['view' => 'cards']), [
                'class' => 'btn btn-sm ' . ($viewMode === 'cards' ? 'btn-secondary' : 'btn-outline-secondary'),
            ]) ?>
        </div>
    </div>

    <p class="text-muted small mb-2">Ціну та вагу можна змінити подвійним кліком по значенню.</p>

    <?php if ($viewMode === 'cards'): ?>

        <!-- фільтр для карток (у таблиці фільтри в заголовку) -->
        <form method="get" action="<?= Url::to(['index']) ?>" class="form-row align-items-end mb-3">
            <div class="col-sm-5 col-md-4 mb-2">
                <?= Html::activeTextInput($searchModel, 'name', ['class' => 'form-control', 'placeholder' => 'Назва товару']) ?>
            </div>
            <div class="col-sm-4 col-md-3 mb-2">
                <?= Html::activeDropDownList($searchModel, 'category_id', $categories, ['class' => 'form-control', 'prompt' => 'Всі категорії']) ?>
            </div>
            <div class="col-sm-3 col-md-2 mb-2">
                <?= Html::activeDropDownList($searchModel, 'status', $statuses, ['class' => 'form-control', 'prompt' => 'Всі статуси']) ?>
            </div>
            <div class="col-md-3 mb-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i>Знайти</button>
                <?= Html::a('Скинути', ['index'], ['class' => 'btn btn-link']) ?>
            </div>
        </form>

        <?= ListView::widget([
            'dataProvider' => $dataProvider,
            'itemView' => '_card',
            'viewParams' => ['inline' => $inline],
            'options' => ['class' => 'product-cards'],
            'itemOptions' => ['class' => 'col-6 col-md-4 col-xl-3 mb-4'],
            'layout' => "<div class=\"text-muted small mb-2\">{summary}</div>\n<div class=\"row\">{items}</div>\n{pager}",
            'emptyText' => 'Товарів не знайдено.',
            'pager' => ['class' => \yii\bootstrap4\LinkPager::class],
        ]) ?>

    <?php else: ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'pager' => ['class' => \yii\bootstrap4\LinkPager::class],
            'columns' => [
                'id',
                'name',
                [
                    'attribute' => 'category_id',
                    'value' => fn ($ml) => $ml->category->name ?? '—',
                    'filter' => Html::activeDropDownList($searchModel, 'category_id', $categories, [
                        'class' => 'form-control',
                        'prompt' => 'Всі Категорії',
                    ]),
                ],
                [
                    'attribute' => 'price',
                    'format' => 'raw',
                    'value' => fn (Product $ml) => $inline($ml, 'price'),
                    'filter' => false,
                ],
                [
                    'attribute' => 'weight',
                    'format' => 'raw',
                    'value' => fn (Product $ml) => $inline($ml, 'weight'),
                    'filter' => false,
                ],
                [
                    'attribute' => 'status',
                    'value' => fn ($ml) => $ml->status
                        ? '<span class="badge bg-success">Активний</span>'
                        : '<span class="badge bg-danger">Не активний</span>',
                    'format' => 'html',
                    'filter' => Html::activeDropDownList($searchModel, 'status', $statuses, [
                        'class' => 'form-control',
                        'prompt' => 'Всі статуси',
                    ]),
                ],
                [
                    'class' => ActionColumn::class,
                    'urlCreator' => fn ($action, Product $model) => Url::toRoute([$action, 'id' => $model->id]),
                ],
            ],
        ]); ?>

    <?php endif; ?>

</div>
