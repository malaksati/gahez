@php
    $currency = display_currency();
@endphp

@if ($coupons->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('messages.Code') }}</th>
                    <th>{{ __('messages.Discount type') }}</th>
                    <th>{{ __('messages.Discount value') }}</th>
                    <th>{{ __('messages.Min cart amount') }}</th>
                    <th>{{ __('messages.Usage limit') }}</th>
                    <th>{{ __('messages.Validity') }}</th>
                    <th>{{ __('messages.Status') }}</th>
                    <th>{{ __('messages.Orders') }}</th>
                    <th class="text-end" style="width: 160px;">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($coupons as $coupon)
                    <tr>
                        <td><code class="fs-6">{{ $coupon->code }}</code></td>
                        <td>@include('v1.admin.partials.discount-type-badge', ['type' => $coupon->type])</td>
                        <td>@include('v1.admin.partials.discount-value', ['type' => $coupon->type, 'amount' => $coupon->discount_value])</td>
                        <td>
                            @if ($coupon->min_cart_amount > 0)
                                {{ format_local_number((float) $coupon->min_cart_amount, 2) }}{{ $currency ? ' '.$currency : '' }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $ordersUsed = (int) ($coupon->orders_count ?? $coupon->totalOrdersUsed());
                            @endphp
                            <div>
                                @if ($coupon->usage_limit)
                                    {{ $ordersUsed }} / {{ $coupon->usage_limit }} {{ strtolower(__('messages.Orders')) }}
                                @else
                                    <span class="text-muted">{{ __('messages.Unlimited') }}</span>
                                    @if ($ordersUsed > 0)
                                        <span class="text-muted">({{ $ordersUsed }} {{ __('messages.used') }})</span>
                                    @endif
                                @endif
                            </div>
                            <small class="text-muted d-block">
                                @if ($coupon->usage_limit_per_user)
                                    {{ $coupon->usage_limit_per_user }} {{ __('messages.per user') }}
                                @else
                                    {{ __('messages.Unlimited per user') }}
                                @endif
                            </small>
                            @if ($coupon->first_order_only)
                                <span class="badge bg-info mt-1">{{ __('messages.First order only') }}</span>
                            @endif
                        </td>
                        <td>@include('v1.admin.partials.validity-period', ['model' => $coupon])</td>
                        <td>@include('v1.admin.partials.status-column', ['model' => $coupon])</td>
                        <td><span class="badge bg-secondary">{{ $coupon->orders_count ?? $coupon->orders->count() }}</span></td>
                        <td class="text-end">
                            @include('v1.admin.partials.table-actions', [
                                'notifyUrl' => route('v1.admin.coupons.notify-customers', $coupon),
                                'notifyEnabled' => $coupon->validityStatus() === 'running',
                                'showUrl' => route('v1.admin.coupons.show', $coupon),
                                'editUrl' => route('v1.admin.coupons.edit', $coupon),
                                'destroyUrl' => route('v1.admin.coupons.destroy', $coupon),
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 px-3 pb-3">{{ $coupons->links() }}</div>
@else
    @include('v1.admin.partials.table-empty', [
        'icon' => 'ticket-perforated',
        'message' => __('messages.No coupons.'),
        'createUrl' => route('v1.admin.coupons.create'),
        'createLabel' => __('messages.New coupon'),
    ])
@endif
