<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Оформлення замовлення</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="checkoutName" class="form-label">Прізвище та ім'я</label>
                    <input type="text" class="form-control" id="checkoutName" placeholder="Напр. Іваненко Іван" autocomplete="name" required>
                    <div class="invalid-feedback">Вкажіть коректне прізвище та ім'я.</div>
                </div>
                <div class="mb-3">
                    <label for="checkoutPhone" class="form-label">Номер телефону</label>
                    <input
                        type="tel"
                        class="form-control"
                        id="checkoutPhone"
                        placeholder="+380 (XX) XXX-XX-XX"
                        autocomplete="tel"
                        value="+38"
                        required>
                    <div class="invalid-feedback">Вкажіть коректний номер телефону.</div>
                </div>
                <div class="mb-3">
                    <label for="cityWelcomeSelect" class="form-label">Локація</label>
                    <?= \yii\helpers\Html::dropDownList('city', null, $this->params['cities'], [
                        'class' => 'form-select city-dropdown'
                    ])?>
                    <div class="invalid-feedback">Вказаний заклад відсутній у списку</div>
                </div>
                <button class="btn btn-dark w-100" type="submit" id="order-confirm-button">Підтвердити замовлення</button>
                <p class="text-muted small mt-2 mb-0">Менеджер зателефонує для підтвердження деталей.</p>
            </div>
        </div>
    </div>
</div>