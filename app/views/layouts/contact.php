<section id="contact" class="py-5">
    <div class="container">
        <div class="contact-surface">
            <div class="row gy-4 align-items-stretch">
                <div class="col-lg-5">
                    <div class="contact-card h-100">
                        <div class="contact-head d-flex justify-content-between align-items-center mb-2">
                            <span class="contact-label">Графік</span>
                            <span class="contact-time"><?= Yii::$app->params['schedule'] ?></span>
                        </div>
                        <p class="contact-subtext mb-3">Однаковий для всіх міст.</p>
                        <div class="contact-grid">
                            <div class="contact-item">
                                <div class="contact-item-label">Телефон</div>
                                <a class="contact-item-value" href="tel:<?= preg_replace('/[^0-9+]/', '', Yii::$app->params['phone']) ?>">
                                    <?= Yii::$app->params['phone'] ?>
                                </a>
                            </div>
                            <div class="contact-item">
                                <div class="contact-item-label">Email</div>
                                <a class="contact-item-value" href="mailto:<?= Yii::$app->params['senderEmail'] ?>">
                                    <?= Yii::$app->params['senderEmail'] ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="contact-intro h-100">
                        <p class="contact-kicker mb-2">Контакти</p>
                        <h3 class="contact-title fw-bold mb-2">Замовляйте в один клік</h3>
                        <p class="contact-copy mb-4">Обирайте місто, рол або сет — ми покажемо актуальну наявність і доставимо без запізнень.</p>
                        <div class="contact-actions d-flex flex-wrap gap-3">
                            <a class="btn btn-dark px-4 contact-btn-primary" href="#menu">Відкрити меню</a>
                            <button class="btn btn-outline-dark px-4 contact-btn-secondary" type="button" id="consultBtn">Консультація</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
