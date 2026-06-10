<div class="offcanvas offcanvas-end" tabindex="-1" id="orderFiltersOffcanvas" aria-labelledby="orderFiltersOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="orderFiltersOffcanvasLabel">{{ __('messages.Filter orders') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('messages.Close') }}"></button>
    </div>
    <div class="offcanvas-body">
        <form method="GET" action="{{ route('v1.admin.orders.index') }}">
            <div class="mb-3">
                <label for="status" class="form-label">{{ __('messages.Status') }}</label>
                <select class="form-select" id="status" name="status">
                    <option value="">{{ __('messages.All statuses') }}</option>
                    @foreach (['pending', 'processing', 'ready_for_delivery', 'shipped', 'delivered', 'cancelled', 'refunded'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status || (is_array(explode(',', $filters['status'] ?? '')) && in_array($status, explode(',', $filters['status'] ?? ''))))>{{ __('messages.'.$status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="payment_status" class="form-label">{{ __('messages.Payment status') }}</label>
                <select class="form-select" id="payment_status" name="payment_status">
                    <option value="">{{ __('messages.All') }}</option>
                    @foreach (['pending', 'paid', 'failed', 'refunded'] as $paymentStatus)
                        <option value="{{ $paymentStatus }}" @selected(($filters['payment_status'] ?? '') === $paymentStatus)>{{ ucfirst($paymentStatus) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="refund_status" class="form-label">{{ __('messages.Refund status') }}</label>
                <select class="form-select" id="refund_status" name="refund_status">
                    <option value="">{{ __('messages.All') }}</option>
                    <option value="none" @selected(($filters['refund_status'] ?? '') === 'none')>{{ __('messages.Not refunded') }}</option>
                    <option value="refunded" @selected(($filters['refund_status'] ?? '') === 'refunded')>{{ __('messages.refunded') }}</option>
                </select>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label for="from_date" class="form-label">{{ __('messages.From date') }}</label>
                    <input type="date" class="form-control" id="from_date" name="from_date" value="{{ $filters['from_date'] ?? '' }}">
                </div>
                <div class="col-6">
                    <label for="to_date" class="form-label">{{ __('messages.To date') }}</label>
                    <input type="date" class="form-control" id="to_date" name="to_date" value="{{ $filters['to_date'] ?? '' }}">
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">{{ __('messages.Filter') }}</button>
                <a href="{{ route('v1.admin.orders.index') }}" class="btn btn-outline-secondary flex-fill">{{ __('messages.Reset') }}</a>
            </div>
        </form>
    </div>
</div>
