<div class="modal fade" id="cityWelcomeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Оберіть місто</h5>
            </div>
            <div class="modal-body">
                <form id="cityWelcomeForm" novalidate>
                    <div class="mb-3">
                        <label for="cityWelcomeSelect" class="form-label">Місто доставки</label>
                        <select id="cityWelcomeSelect" class="form-select" required>
                            <option value="" disabled selected>???????? ?????</option>
                            <!--                        --><?php //foreach ($cityMap as $cityKey => $city): ?>
                            <!--                            --><?php //if (!is_array($city)) { continue; } ?>
                            <!--                            --><?php //$label = $city['label'] ?? $cityKey; ?>
                            <!--                            <option value="--><?php //= Html::encode($cityKey) ?><!--">--><?php //= Html::encode($label) ?><!--</option>-->
                            <!--                        --><?php //endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Оберіть місто для продовження.</div>
                    </div>
                    <button class="btn btn-dark w-100" type="submit" id="cityWelcomeBtn">Продовжити</button>
                    <p class="text-muted small mt-2 mb-0">Ваш вибір збережемо для наступного візиту.</p>
                </form>
            </div>
        </div>
    </div>
</div>