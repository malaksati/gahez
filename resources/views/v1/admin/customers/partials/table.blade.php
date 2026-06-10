@if ($customers->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('messages.Name') }}</th>
                    <th>{{ __('messages.Email') }} / {{ __('messages.Phone') }}</th>
                    <th>{{ __('messages.Status') }}</th>
                    <th>{{ __('messages.Orders') }}</th>
                    <th>{{ __('messages.Created') }}</th>
                    <th class="text-end" style="width: 120px;">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customers as $customer)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $customer->image }}" alt="{{ $customer->name }}" class="rounded-circle" width="36" height="36" style="object-fit: cover;">
                                <strong>{{ $customer->name }}</strong>
                            </div>
                        </td>
                        <td class="text-muted">
                            <div>{{ $customer->email ?: '-' }}</div>
                            <div>{{ $customer->phone ?: '-' }}</div>
                        </td>
                        <td>
                            <div class="d-flex flex-column align-items-start gap-1">
                                @include('v1.admin.partials.active-badge', ['active' => $customer->is_active])
                                @include('v1.admin.partials.verified-badge', ['verified' => $customer->is_verified])
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info">
                                <i class="bi bi-cart me-1"></i>{{ $customer->orders_count }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $customer->created_at->format('M d, Y') }}</td>
                        <td class="text-end">
                            @include('v1.admin.partials.table-actions', [
                                'showUrl' => route('v1.admin.customers.show', $customer),
                                'editUrl' => route('v1.admin.customers.edit', $customer),
                                'destroyUrl' => route('v1.admin.customers.destroy', $customer),
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 px-3 pb-3">{{ $customers->links() }}</div>
@else
    @include('v1.admin.partials.table-empty', [
        'icon' => 'people',
        'message' => __('messages.No customers found.'),
        'createUrl' => route('v1.admin.customers.create'),
        'createLabel' => __('messages.New customer'),
    ])
@endif
