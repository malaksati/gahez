@extends('layouts.app')

@php
    $page = 'orders';
    $locale = app()->getLocale();
    $currency = display_currency();
    $title = __('messages.Order').' #'.$order->id;

    $statusBadgeHtml = view('v1.admin.orders.partials.order-status-badge', ['status' => $order->status])->render();
    $paymentBadgeHtml = view('v1.admin.orders.partials.order-payment-status-badge', ['paymentStatus' => $order->payment_status])->render();
    $statusButtons = [
        'processing' => ['label' => __('messages.processing'), 'class' => 'btn-outline-info', 'icon' => 'bi-gear'],
        'ready_for_delivery' => ['label' => __('messages.ready_for_delivery'), 'class' => 'btn-outline-warning', 'icon' => 'bi-box-seam'],
        'shipped' => ['label' => __('messages.shipped'), 'class' => 'btn-outline-primary', 'icon' => 'bi-truck'],
        'delivered' => ['label' => __('messages.delivered'), 'class' => 'btn-outline-success', 'icon' => 'bi-check-circle'],
        'cancelled' => ['label' => __('messages.cancelled'), 'class' => 'btn-outline-danger', 'icon' => 'bi-x-circle'],
    ];

    $actionsByStatus = [
        'pending' => ['processing', 'cancelled'],
        'processing' => ['ready_for_delivery', 'cancelled'],
        'ready_for_delivery' => ['shipped', 'cancelled'],
        'shipped' => ['delivered', 'cancelled'],
        'delivered' => [],
        'cancelled' => [],
        'refunded' => [],
    ];

    $availableActions = $actionsByStatus[$order->status] ?? [];

    $paymentButtons = [
        'paid' => ['label' => __('messages.paid'), 'class' => 'btn-outline-success', 'icon' => 'bi-check-circle'],
        'failed' => ['label' => __('messages.failed'), 'class' => 'btn-outline-danger', 'icon' => 'bi-x-circle'],
        'pending' => ['label' => __('messages.pending'), 'class' => 'btn-outline-warning', 'icon' => 'bi-clock'],
        'refunded' => ['label' => __('messages.refunded'), 'class' => 'btn-outline-secondary', 'icon' => 'bi-arrow-return-left'],
    ];

    $paymentActionsByStatus = [
        'pending' => ['paid', 'failed'],
        'paid' => ['refunded'],
        'failed' => ['pending', 'paid'],
        'refunded' => [],
    ];

    $availablePaymentActions = $paymentActionsByStatus[$order->payment_status] ?? [];
@endphp

@section('title', $title)

@section('content')
    @include('v1.admin.partials.show-header', [
        'indexRoute' => 'v1.admin.orders.index',
        'indexLabel' => __('messages.Orders'),
        'title' => $title,
        'badges' => $statusBadgeHtml.$paymentBadgeHtml,
    ])

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ __('messages.Order information') }}</h5>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">{{ __('messages.Customer') }}</dt>
                        <dd class="col-sm-8">
                            <strong>{{ $order->user->name ?? $order->customer_name ?? '—' }}</strong><br>
                            <span class="small text-muted">{{ $order->user->email ?? $order->customer_email ?? '—' }}</span>
                        </dd>

                        <dt class="col-sm-4 mt-3">{{ __('messages.Sub total') }}</dt>
                        <dd class="col-sm-8 mt-3">{{ format_local_number((float) $order->sub_total, 2) }}{{ $currency ? ' '.$currency : '' }}</dd>

                        <dt class="col-sm-4 mt-3">{{ __('messages.Order discount') }}</dt>
                        <dd class="col-sm-8 mt-3">{{ format_local_number((float) $order->order_discount, 2) }}{{ $currency ? ' '.$currency : '' }}</dd>

                        @if ($order->coupon)
                            <dt class="col-sm-4 mt-3">{{ __('messages.Coupon') }}</dt>
                            <dd class="col-sm-8 mt-3">
                                <a href="{{ route('v1.admin.coupons.show', $order->coupon) }}"><code>{{ $order->coupon->code }}</code></a>
                                <span class="text-muted ms-1">(-{{ format_local_number((float) $order->coupon_discount, 2) }}{{ $currency ? ' '.$currency : '' }})</span>
                            </dd>
                        @endif

                        <dt class="col-sm-4 mt-3">{{ __('messages.Shipping') }}</dt>
                        <dd class="col-sm-8 mt-3">
                            {{ format_local_number((float) $order->total_shipping, 2) }}{{ $currency ? ' '.$currency : '' }}
                            @if ($order->is_fast_shipping)
                                <span class="badge bg-info text-dark ms-1">{{ __('messages.Fast shipping') }}</span>
                            @endif
                        </dd>

                        @if ($order->shipping_day)
                            <dt class="col-sm-4 mt-3">{{ __('messages.Shipping day') }}</dt>
                            <dd class="col-sm-8 mt-3">{{ __('messages.weekday_'.$order->shipping_day) }}</dd>
                        @endif

                        @if ((float) $order->wallet_used > 0)
                            <dt class="col-sm-4 mt-3">{{ __('messages.Wallet used') }}</dt>
                            <dd class="col-sm-8 mt-3">
                                <span class="badge bg-primary">{{ format_local_number((float) $order->wallet_used, 2) }}{{ $currency ? ' '.$currency : '' }}</span>
                            </dd>
                        @endif

                        <dt class="col-sm-4 mt-3">{{ __('messages.Total') }}</dt>
                        <dd class="col-sm-8 mt-3"><strong class="fs-5">{{ format_local_number((float) $order->total, 2) }}{{ $currency ? ' '.$currency : '' }}</strong></dd>

                        <dt class="col-sm-4 mt-3">{{ __('messages.Payment') }}</dt>
                        <dd class="col-sm-8 mt-3">
                            @include('v1.admin.orders.partials.order-payment-status-badge', ['paymentStatus' => $order->payment_status])
                            @if ($order->payment_method)
                                <span class="text-muted ms-2">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 mt-3">{{ __('messages.Refund status') }}</dt>
                        <dd class="col-sm-8 mt-3">{{ $order->refund_status ? ucfirst(str_replace('_', ' ', $order->refund_status)) : '—' }}</dd>

                        @if ($order->notes)
                            <dt class="col-sm-4 mt-3">{{ __('messages.Notes') }}</dt>
                            <dd class="col-sm-8 mt-3">{{ $order->notes }}</dd>
                        @endif

                        <dt class="col-sm-4 mt-3">{{ __('messages.Created at') }}</dt>
                        <dd class="col-sm-8 mt-3"><small class="text-muted">{{ $order->created_at?->format('M d, Y H:i') }}</small></dd>

                        @if(isset($orderRating))
                        <dt class="col-sm-4 mt-3">{{ __('messages.Order Rating') }}</dt>
                        <dd class="col-sm-8 mt-3">
                            <div class="text-warning">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $orderRating->rating ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                            @if($orderRating->comment)
                                <div class="small mt-1 text-muted">{{ $orderRating->comment }}</div>
                            @endif
                        </dd>
                        @endif

                    </dl>
                </div>
            </div>

            @php
                $addressSnapshot = $order->shipping_address_snapshot ?? [];
            @endphp
            @if ($order->address || ! empty($addressSnapshot))
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">{{ __('messages.Shipping address') }}</h5>
                        <p class="mb-0">
                            <strong>{{ $addressSnapshot['name'] ?? ($order->address->name ?? '—') }}</strong><br>
                            {{ $addressSnapshot['address'] ?? ($order->address->address ?? '—') }}<br>
                            {{ $addressSnapshot['city'] ?? ($order->address->city ?? '') }}@if (! empty($addressSnapshot['state']) || $order->address?->state), {{ $addressSnapshot['state'] ?? $order->address->state }}@endif<br>
                            {{ $addressSnapshot['phone'] ?? ($order->address->phone ?? '—') }}
                        </p>
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">{{ __('messages.Order items') }} ({{ $order->items->count() }})</h5>
                </div>
                <div class="card-body p-0">
                    @if ($order->items->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('messages.Product') }}</th>
                                        <th>{{ __('messages.Variant') }}</th>
                                        <th class="text-center">{{ __('messages.Qty') }}</th>
                                        <th>{{ __('messages.Note') }}</th>
                                        <th class="text-end">{{ __('messages.Price') }}</th>
                                        <th class="text-end">{{ __('messages.Sub total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->items as $item)
                                        @php
                                            $productName = $item->product
                                                ? ($item->product->getTranslation('name', $locale, false) ?: $item->product->getTranslation('name', 'en', false))
                                                : (($locale === 'ar' ? $item->product_name_ar : null) ?: $item->product_name ?: '—');
                                            $variantName = $item->variant
                                                ? ($item->variant->getTranslation('name', $locale, false) ?: $item->variant->getTranslation('name', 'en', false))
                                                : (($locale === 'ar' ? $item->variant_name_ar : null) ?: $item->variant_name ?: null);
                                            $lineTotal = ((float) $item->unit_price * (int) $item->quantity)
                                                - (float) $item->line_discount;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if ($item->product && $item->product->thumbnail)
                                                        <img src="{{ $item->product->thumbnail }}" alt="{{ $productName }}" class="img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light border rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <i class="bi bi-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                    @if ($item->product)
                                                        <a href="{{ route('v1.admin.products.show', $item->product) }}">{{ $productName }}</a>
                                                    @else
                                                        —
                                                    @endif
                                                </div>
                                            </td>
                                            <td><span class="text-muted small">{{ $variantName ?? '—' }}</span></td>
                                            <td class="text-center">@num($item->quantity)</td>
                                            <td><span class="text-muted small">{{ $item->note ?: '—' }}</span></td>
                                            <td class="text-end">{{ format_local_number((float) $item->unit_price, 2) }}{{ $currency ? ' '.$currency : '' }}</td>
                                            <td class="text-end"><strong>{{ format_local_number($lineTotal, 2) }}{{ $currency ? ' '.$currency : '' }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4 mb-0">{{ __('messages.No order items.') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">{{ __('messages.Order status') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        @include('v1.admin.orders.partials.order-status-badge', ['status' => $order->status])
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse ($availableActions as $action)
                            @php
                                $meta = $statusButtons[$action];
                                $statusConfirmMessage = $action === 'cancelled'
                                    ? __('messages.Confirm cancel this order?')
                                    : __('messages.Confirm order status change to :status?', ['status' => $meta['label']]);
                            @endphp
                            <form action="{{ route('v1.admin.orders.update-status', $order) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="{{ $action }}">
                                @if($action === 'cancelled')
                                    <button type="button" class="btn {{ $meta['class'] }} btn-sm" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                                        <i class="bi {{ $meta['icon'] }} me-1"></i>{{ $meta['label'] }}
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        class="btn {{ $meta['class'] }} btn-sm"
                                        data-order-confirm-submit
                                        data-confirm-message="{{ e($statusConfirmMessage) }}"
                                    >
                                        <i class="bi {{ $meta['icon'] }} me-1"></i>{{ $meta['label'] }}
                                    </button>
                                @endif
                            </form>
                        @empty
                            <span class="text-muted small">{{ __('messages.No status actions available.') }}</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">{{ __('messages.Payment status') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        @include('v1.admin.orders.partials.order-payment-status-badge', ['paymentStatus' => $order->payment_status])
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse ($availablePaymentActions as $paymentAction)
                            @php
                                $paymentMeta = $paymentButtons[$paymentAction];
                                $paymentConfirmMessage = __('messages.Confirm payment status change to :status?', [
                                    'status' => $paymentMeta['label'],
                                ]);
                            @endphp
                            <form action="{{ route('v1.admin.orders.update-payment-status', $order) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="payment_status" value="{{ $paymentAction }}">
                                <button
                                    type="button"
                                    class="btn {{ $paymentMeta['class'] }} btn-sm"
                                    data-order-confirm-submit
                                    data-confirm-message="{{ e($paymentConfirmMessage) }}"
                                >
                                    <i class="bi {{ $paymentMeta['icon'] }} me-1"></i>{{ $paymentMeta['label'] }}
                                </button>
                            </form>
                        @empty
                            <span class="text-muted small">{{ __('messages.No payment actions available.') }}</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body py-3">
                    <a href="{{ route('v1.admin.orders.invoice', $order) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-receipt me-1"></i>{{ __('messages.Invoice') }}
                    </a>
                    <a href="{{ route('v1.admin.orders.edit', $order) }}" class="btn btn-outline-primary btn-sm ms-2">
                        <i class="bi bi-pencil me-1"></i>{{ __('messages.Edit order') }}
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <dl class="mb-0">
                        <dt class="small text-muted">{{ __('messages.Order ID') }}</dt>
                        <dd><code>#{{ $order->id }}</code></dd>
                        <dt class="small text-muted mt-3">{{ __('messages.Paid at') }}</dt>
                        <dd>{{ $order->paid_at?->format('M d, Y H:i') ?? '—' }}</dd>
                    </dl>
                </div>
            </div>

            @include('v1.admin.partials.show-timestamps', ['model' => $order])

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">{{ __('messages.Order logs') }}</h5>
                </div>
                <div class="card-body p-0">
                    @include('v1.admin.orders.partials.order-logs-list', ['order' => $order])
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Order Modal -->
    <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('v1.admin.orders.update-status', $order) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="cancelled">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelOrderModalLabel">{{ __('messages.Cancel Order') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="cancellation_reason" class="form-label">{{ __('messages.Cancellation Reason') }}</label>
                            <textarea name="cancellation_reason" id="cancellation_reason" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.Close') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('messages.Confirm Cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection
