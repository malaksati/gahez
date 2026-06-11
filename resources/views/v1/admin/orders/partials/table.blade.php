@php $currency = display_currency(); @endphp

@if ($orders->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('messages.ID') }}</th>
                    <th>{{ __('messages.Customer') }}</th>
                    <th>{{ __('messages.Address') }}</th>
                    <th>{{ __('messages.Status') }}</th>
                    <th>{{ __('messages.Payment') }}</th>
                    <th>{{ __('messages.Total') }}</th>
                    <th>{{ __('messages.Delivery Time') }}</th>
                    <th>{{ __('messages.Date') }}</th>
                    <th>{{ __('messages.Activity') }}</th>
                    <th class="text-end" style="width: 100px;">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td><strong>#{{ $order->id }}</strong></td>
                        <td>
                            @if ($order->user)
                                <div>{{ $order->user->name }}</div>
                                <div class="small text-muted">{{ $order->user->email }}</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if ($order->address)
                                <div class="small">{{ $order->shipping_address_snapshot['city'] ?? $order->address->city }}, {{ $order->shipping_address_snapshot['address'] ?? $order->address->address }}</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>@include('v1.admin.orders.partials.order-status-badge', ['status' => $order->status])</td>
                        <td>
                            @include('v1.admin.orders.partials.order-payment-status-badge', ['paymentStatus' => $order->payment_status])
                            @if ($order->payment_method)
                                <div class="small text-muted mt-1">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</div>
                            @endif
                        </td>
                        <td><strong>{{ format_local_number((float) $order->total, 2) }}{{ $currency ? ' '.$currency : '' }}</strong></td>
                        <td><div class="small text-muted">{{ $order->delivery_expected_time ?? '—' }}</div></td>
                        <td class="small text-muted">{{ $order->created_at?->format('M d, Y H:i') }}</td>
                        <td>@include('v1.admin.orders.partials.order-log-summary', ['order' => $order])</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm table-actions" role="group">
                                <a href="{{ route('v1.admin.orders.show', $order) }}" class="btn btn-outline-info" title="{{ __('messages.View') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('v1.admin.orders.invoice', $order) }}" class="btn btn-outline-secondary" target="_blank" rel="noopener" title="{{ __('messages.Invoice') }}">
                                    <i class="bi bi-receipt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 px-3 pb-3">{{ $orders->links() }}</div>
@else
    @include('v1.admin.partials.table-empty', [
        'icon' => 'bag-check',
        'message' => __('messages.No orders.'),
    ])
@endif
