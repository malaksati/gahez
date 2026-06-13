<div class="modal fade" id="orderActionConfirmModal" tabindex="-1" aria-labelledby="orderActionConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title w-100 text-center" id="orderActionConfirmModalLabel">{{ __('messages.Confirm action') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.Close') }}"></button>
            </div>
            <div class="modal-body text-center pt-2">
                <div class="mb-3">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-1"></i>
                </div>
                <p class="mb-0 fw-semibold" style="white-space: pre-line" data-order-confirm-message></p>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('messages.Cancel') }}</button>
                <button type="button" class="btn btn-danger" data-order-confirm-accept>{{ __('messages.Confirm') }}</button>
            </div>
        </div>
    </div>
</div>
