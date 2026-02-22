<div class="modal fade" id="consultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Консультація</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="consultForm" novalidate>
                    <div class="mb-3">
                        <label for="consultPhone" class="form-label">Номер телефону</label>
                        <input type="tel" class="form-control" id="consultPhone" placeholder="+38 (0XX) XXX-XX-XX" autocomplete="tel" required>
                        <div class="invalid-feedback">Вкажіть коректний номер телефону.</div>
                    </div>
                    <button class="btn btn-dark w-100" type="submit">Замовити дзвінок</button>
                    <p class="text-muted small mt-2 mb-0">Передзвонимо протягом 15 хвилин.</p>
                </form>
            </div>
        </div>
    </div>
</div>