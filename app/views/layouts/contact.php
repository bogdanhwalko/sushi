<section id="contact" class="py-5">
    <div class="container">
        <div class="row gy-4 align-items-center">
            <div class="col-lg-6">
                <h3 class="fw-bold mb-2">Замовляйте в один клік</h3>
                <p class="text-muted mb-4">Обирайте місто, рол або сет — ми покажемо актуальну наявність і доставимо без запізнень.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a class="btn btn-dark px-4" href="#menu">Відкрити меню</a>
                    <button class="btn btn-outline-dark px-4" type="button" id="consultBtn">Консультація</button>
                </div>
            </div>
            <div class="col-lg-5 ms-auto">
                <div class="contact-card bg-white rounded-4 p-4 shadow-lg">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-semibold">Графік</span>
                        <span class="text-muted small"><?= Yii::$app->params['schedule'] ?></span>
                    </div>
                    <p class="text-muted small mb-3">Однаковий для всіх міст, доставка починається за 45 хвилин.</p>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div>
                            <div class="text-muted small">Телефон</div>
                            <div class="fw-semibold"><?= Yii::$app->params['phone'] ?></div>
                        </div>
                        <div>
                            <div class="text-muted small">Email</div>
                            <div class="fw-semibold"><?= Yii::$app->params['senderEmail'] ?></div>
                        </div>
<!--                        <div>-->
<!--                            <div class="text-muted small">Instagram</div>-->
<!--                            <div class="fw-semibold">--><?php //= Yii::$app->params['instagram'] ?><!--</div>-->
<!--                        </div>-->
<!--                        <div>-->
<!--                            <div class="text-muted small">Адреса</div>-->
<!--                            <div class="fw-semibold" id="addressLabel">--><?php //= Yii::$app->params['address'] ?><!--</div>-->
<!--                        </div>-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
