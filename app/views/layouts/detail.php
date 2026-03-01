<?php

use yii\helpers\Html;

?>

<div class="container pt-4">
    <div class="row align-items-center gy-4">
        <div class="col-lg-6">
            <p class="eyebrow mb-2">Забери сам та отримай знижку -5%</p>
            <h1 class="display-4 fw-bold lh-1 mb-3">
                Доставка японської кухні у твоєму місті
                <span class="highlight text-sushi">"107 SUSHI"</span>
            </h1>
            <p class="lead mb-4 fs-6 fw-medium">Обирайте страви. Про все інше подбає наша команда.</p>
            <div class="d-flex gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-2 flex-wrap" id="happyHours">
<!--                    <div class="pill-pill d-inline-flex align-items-center gap-2">-->
<!--                        <span class="happy-dot" id="happyDot" aria-hidden="true"></span>-->
<!--                        <span>Щасливі години</span>-->
<!--                    </div>-->
<!--                    <div class="pill-pill" id="happyStatus">Сьогодні 12:00–14:00</div>-->
                </div>
            </div>
        </div>
        <?php if (! empty($this->params['productOfWeek'])): ?>
            <div class="col-lg-5 ms-auto">
                <div
                    class="hero-card rounded-4 p-4 bg-white text-dark shadow-lg"
                    id="hero-card-block"
                    data-product="<?= $this->params['productOfWeek']->id ?>">

                    <div class="hero-card-top d-flex align-items-center mb-2">
                        <div class="hero-badge me-3">Акція тижня</div>
                        <span class="hero-time text-muted small">
                            Встигни до: <?= date('d-m-Y 22:00', strtotime('sunday this week'))?>
                        </span>
                    </div>
                    <div class="hero-product d-flex">
                        <?= Html::img('@products/' . $this->params['productOfWeek']->image, [
                            'alt' => $this->params['productOfWeek']->name,
                            'class' => 'rounded-3 hero-img',
                        ]) ?>
                        <div class="hero-content">
                            <h4 class="hero-title fw-semibold mb-1"><?= $this->params['productOfWeek']->name ?></h4>
                            <p class="hero-description text-muted small mb-2"><?= $this->params['productOfWeek']->description ?></p>
                            <div class="d-flex align-items-end text-right gap-2 flex-wrap">
                                <span class="hero-price badge bg-primary-soft text-primary fw-bold"><?= $this->params['productOfWeek']->price ?>$</span>
                            </div>
                            <div class="hero-actions d-flex align-items-center gap-2 flex-wrap">
                                <button class="btn btn-sm btn-dark view-details btn-block">Детальніше</button>
                                <button class="btn btn-sm btn-outline-dark add-to-cart">До кошика</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
