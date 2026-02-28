<?php
?>

<div class="modal fade" id="cityWelcomeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">В якому закладі ви зараз перебуваєте?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="cityWelcomeSelect" class="form-label">Виберіть з перерахованих:</label>
                    <?= \yii\helpers\Html::dropDownList('city', null, $this->params['cities'], [
                        'class' => 'form-select city-dropdown',
                        'id' => 'cityWelcomeSelect',
                    ])?>
                    <div class="invalid-feedback">Вказаний заклад відсутній у списку</div>
                </div>
                <button class="btn btn-dark w-100" type="submit" data-bs-dismiss="modal" aria-label="Close">Продовжити</button>
                <p class="text-muted small mt-2 mb-0">Це потрібно, щоб показати доступні товари та ціни для вашого місця.</p>
            </div>
        </div>
    </div>
</div>