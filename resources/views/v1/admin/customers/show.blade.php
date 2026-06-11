@extends('layouts.app')

@section('title', __('messages.Customer details'))
@section('subtitle', $customer->name)
@section('page-actions')
    <a href="{{ route('v1.admin.customers.edit', $customer) }}" class="btn btn-primary">
        <i class="bi bi-pencil me-1"></i>{{ __('messages.Edit') }}
    </a>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center pt-5 pb-4">
                    <img src="{{ $customer->image }}" alt="{{ $customer->name }}" class="rounded-circle mb-3" width="96"
                        height="96" style="object-fit: cover;">
                    <h5 class="mb-1">{{ $customer->name }}</h5>
                    <p class="text-muted mb-3">{{ $customer->email ?: $customer->phone }}</p>
                    <div class="mb-3">
                        <div class="text-muted small mb-2">{{ __('messages.Status') }}</div>
                        <div class="d-flex flex-column align-items-center gap-1 mb-2">
                            @include('v1.admin.partials.active-badge', ['active' => $customer->is_active])
                            @include('v1.admin.partials.verified-badge', ['verified' => $customer->is_verified])
                        </div>
                        <span class="badge bg-info bg-opacity-10 text-info">
                            <i class="bi bi-cart me-1"></i>{{ $customer->orders_count }} {{ __('messages.Orders') }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-center gap-3 mt-2">
                        <div class="text-center">
                            <div class="text-muted small">{{ __('messages.Wallet balance') }}</div>
                            <strong>{{ format_local_number((float) $customer->wallet, 2) }} {{ display_currency() }}</strong>
                        </div>
                        <div class="text-center">
                            <div class="text-muted small">{{ __('messages.Points balance') }}</div>
                            <strong>{{ format_local_number((int) $customer->points) }}</strong>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-3">
                    <div class="d-flex justify-content-between text-muted small">
                        <span>{{ __('messages.Joined') }}</span>
                        <strong>{{ $customer->created_at->format('M d, Y H:i') }}</strong>
                    </div>
                </div>
            </div>
            @include('v1.admin.customers.partials.goals-progress', ['goalProgress' => $goalProgress])
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom">
                    <h6 class="mb-0">{{ __('messages.Basic Information') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Name'),
                            'value' => $customer->name,
                        ])
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Email'),
                            'value' => $customer->email ?: '-',
                        ])
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Phone'),
                            'value' => $customer->phone ?: '-',
                        ])
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Birthdate'),
                            'value' => $customer->birthdate?->format('d-m-Y') ?: '—',
                        ])
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Created at'),
                            'value' => $customer->created_at
                                ? e($customer->created_at->format('M d, Y H:i')).' <span class="text-muted">('.e($customer->created_at->diffForHumans()).')</span>'
                                : '—',
                        ])
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Updated at'),
                            'value' => $customer->updated_at
                                ? e($customer->updated_at->format('M d, Y H:i')).' <span class="text-muted">('.e($customer->updated_at->diffForHumans()).')</span>'
                                : '—',
                        ])
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom">
                    <h6 class="mb-0">{{ __('messages.Addresses') }}</h6>
                </div>
                <div class="card-body p-0">
                    @if ($customer->addresses && $customer->addresses->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach ($customer->addresses as $address)
                                <div class="list-group-item px-4 py-3">
                                    <h6 class="mb-1">{{ $address->name }} @if ($address->is_default)
                                            <span class="badge bg-primary ms-1">{{ __('messages.Default') }}</span>
                                        @endif
                                    </h6>
                                    <p class="mb-0 text-muted small">
                                        {{ $address->address }}<br>
                                        @if ($address->city || $address->state)
                                            {{ collect([$address->city, $address->state])->filter()->implode(', ') }}
                                        @endif
                                        @if ($address->phone)
                                            <br>{{ $address->phone }}
                                        @endif
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-geo-alt fs-2 d-block mb-2 text-opacity-50"></i>
                            {{ __('messages.No addresses found for this customer.') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('messages.Points history') }}</h6>
                    @if ($customer->pointTransactions->count() > 0)
                        <span class="badge bg-secondary">{{ $customer->pointTransactions->count() }}</span>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if ($customer->pointTransactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('messages.Date') }}</th>
                                        <th>{{ __('messages.Type') }}</th>
                                        <th class="text-end">{{ __('messages.Points') }}</th>
                                        <th class="text-end">{{ __('messages.Balance after') }}</th>
                                        <th>{{ __('messages.Notes') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($customer->pointTransactions as $tx)
                                        <tr>
                                            <td class="small text-muted">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                @if ($tx->type === 'addition')
                                                    <span class="badge bg-success">{{ __('messages.Addition') }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ __('messages.Subtraction') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end {{ $tx->type === 'addition' ? 'text-success' : 'text-danger' }}">
                                                {{ $tx->type === 'addition' ? '+' : '-' }}{{ format_local_number((int) $tx->amount) }}
                                            </td>
                                            <td class="text-end">{{ format_local_number((int) $tx->balance_after) }}</td>
                                            <td class="small text-muted">{{ $tx->notes ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-star fs-2 d-block mb-2 text-opacity-50"></i>
                            {{ __('messages.No point transactions yet.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
