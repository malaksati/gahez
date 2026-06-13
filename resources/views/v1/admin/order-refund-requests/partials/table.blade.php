@php
    $statusBadge = static function (string $status): string {
        return match ($status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'pending' => 'warning',
            default => 'secondary',
        };
    };
@endphp

@if ($refundRequests->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0 table-scroll-x">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('messages.ID') }}</th>
                        <th>{{ __('messages.Order') }}</th>
                        <th>{{ __('messages.Customer') }}</th>
                        <th>{{ __('messages.Reason') }}</th>
                        <th>{{ __('messages.Status') }}</th>
                        <th>{{ __('messages.Date') }}</th>
                        <th class="text-end" style="width: 220px;">{{ __('messages.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($refundRequests as $refundRequest)
                        <tr>
                            <td><strong>#{{ $refundRequest->id }}</strong></td>
                            <td>
                                <a href="{{ route('v1.admin.orders.show', $refundRequest->order_id) }}" class="text-decoration-none">
                                    #{{ $refundRequest->order_id }}
                                </a>
                            </td>
                            <td>{{ $refundRequest->user?->name ?? '—' }}</td>
                            <td>{{ Str::limit($refundRequest->reason ?? '—', 40) }}</td>
                            <td>
                                <span class="badge bg-{{ $statusBadge($refundRequest->status) }} text-capitalize">
                                    {{ __('messages.'.$refundRequest->status) }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $refundRequest->created_at?->format('M d, Y H:i') }}</td>
                            <td class="text-end">
                                @if ($refundRequest->status === 'pending')
                                    <form method="POST" action="{{ route('v1.admin.order-refund-requests.approve', $refundRequest) }}" class="d-inline">
                                        @csrf
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-success"
                                            data-order-confirm-submit
                                            data-confirm-message="{{ e(__('messages.Approve this refund request?') . "\n\n" . __('messages.The order will be refunded to the customer.')) }}"
                                        >
                                            <i class="bi bi-check2 me-1"></i>{{ __('messages.Accept') }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('v1.admin.order-refund-requests.reject', $refundRequest) }}" class="d-inline">
                                        @csrf
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            data-order-confirm-submit
                                            data-confirm-message="{{ e(__('messages.Reject this refund request?') . "\n\n" . __('messages.The customer will be notified that the refund was rejected.')) }}"
                                        >
                                            <i class="bi bi-x-lg me-1"></i>{{ __('messages.Reject') }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                                {{-- <a href="{{ route('v1.admin.order-refund-requests.show', $refundRequest) }}" class="btn btn-sm btn-outline-secondary ms-1" title="{{ __('messages.View') }}">
                                    <i class="bi bi-eye"></i>
                                </a> --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 px-3 pb-3">{{ $refundRequests->links() }}</div>
    </div>
@else
    @include('v1.admin.partials.table-empty', [
        'icon' => 'arrow-return-left',
        'message' => __('messages.No refund requests.'),
    ])
@endif
