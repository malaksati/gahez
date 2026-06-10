<div class="modal fade" id="orderActionConfirmModal" tabindex="-1" aria-labelledby="orderActionConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderActionConfirmModalLabel">{{ __('messages.Confirm action') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.Close') }}"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" data-order-confirm-message></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('messages.Cancel') }}</button>
                <button type="button" class="btn btn-primary" data-order-confirm-accept>{{ __('messages.Confirm') }}</button>
            </div>
        </div>
    </div>
</div>
