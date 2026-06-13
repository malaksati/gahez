@extends('layouts.app')

@php
    $page = 'orders';
    $locale = app()->getLocale();
    $usersData = $users->map(function ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'addresses' => $user->addresses->map(fn ($address) => [
                'id' => $address->id,
                'name' => $address->name,
                'address' => $address->address,
                'city' => $address->city,
                'state' => $address->state,
                'phone' => $address->phone,
            ])->values()->all(),
        ];
    })->values()->all();
@endphp

@section('title', __('messages.New Order'))
@section('subtitle', __('messages.Create order manually'))

@section('page-actions')
    <a href="{{ route('v1.admin.orders.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>{{ __('messages.Back to list') }}
    </a>
@endsection

@section('content')
<form action="{{ route('v1.admin.orders.store') }}" method="POST" id="createOrderForm">
    @csrf

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4"
                x-data="{
                    customerType: @js(old('customer_type', 'existing')),
                    userId: @js(old('user_id', '')),
                    addressMode: @js(old('address_mode', 'existing')),
                    addressId: @js(old('address_id', '')),
                    users: @js($usersData),
                    get selectedUser() {
                        return this.users.find((user) => String(user.id) === String(this.userId));
                    },
                    get addresses() {
                        return this.selectedUser?.addresses ?? [];
                    },
                    onCustomerTypeChange() {
                        if (this.customerType === 'new') {
                            this.addressMode = 'new';
                        } else if (this.addresses.length === 0) {
                            this.addressMode = 'new';
                        }
                    },
                    onUserChange() {
                        this.addressId = '';
                        if (this.addresses.length === 0) {
                            this.addressMode = 'new';
                        } else {
                            this.addressMode = 'existing';
                        }
                    }
                }"
                x-init="onCustomerTypeChange()">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">{{ __('messages.Customer Details') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label">{{ __('messages.Customer type') }}</label>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="customer_type" id="customer_type_existing" value="existing"
                                    x-model="customerType" @change="onCustomerTypeChange()">
                                <label class="form-check-label" for="customer_type_existing">{{ __('messages.Existing customer') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="customer_type" id="customer_type_new" value="new"
                                    x-model="customerType" @change="onCustomerTypeChange()">
                                <label class="form-check-label" for="customer_type_new">{{ __('messages.New customer') }}</label>
                            </div>
                        </div>
                    </div>

                    <div x-show="customerType === 'existing'" x-cloak>
                        <div class="mb-3">
                            <label for="user_id" class="form-label">{{ __('messages.Customer') }} *</label>
                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id"
                                x-model="userId" @change="onUserChange()" :required="customerType === 'existing'"
                                :disabled="customerType !== 'existing'">
                                <option value="">{{ __('messages.Select customer') }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                                        {{ $user->name }} @if($user->phone)({{ $user->phone }})@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <template x-if="userId && addresses.length > 0">
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.Address') }}</label>
                                <div class="d-flex flex-wrap gap-3 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="address_mode_existing" value="existing"
                                            x-model="addressMode">
                                        <label class="form-check-label" for="address_mode_existing">{{ __('messages.Use existing address') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="address_mode_new" value="new"
                                            x-model="addressMode">
                                        <label class="form-check-label" for="address_mode_new">{{ __('messages.Add new address') }}</label>
                                    </div>
                                </div>

                                <div x-show="addressMode === 'existing'" x-cloak>
                                    <select class="form-select @error('address_id') is-invalid @enderror" id="address_id" name="address_id"
                                        x-model="addressId" :required="customerType === 'existing' && addressMode === 'existing'"
                                        :disabled="customerType !== 'existing' || addressMode !== 'existing'">
                                        <option value="">{{ __('messages.Select an address') }}</option>
                                        <template x-for="addr in addresses" :key="addr.id">
                                            <option :value="addr.id" x-text="`${addr.name} - ${addr.address} (${addr.city || '—'})`"></option>
                                        </template>
                                    </select>
                                    @error('address_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </template>

                        <template x-if="userId && addresses.length === 0">
                            <p class="text-muted small mb-3">{{ __('messages.No addresses found for this customer.') }}</p>
                        </template>
                    </div>

                    <input type="hidden" name="address_mode" x-model="addressMode">

                    <div x-show="customerType === 'new'" x-cloak>
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label for="customer_name" class="form-label">{{ __('messages.Name') }} *</label>
                                <input type="text" class="form-control @error('customer_name') is-invalid @enderror" id="customer_name" name="customer_name"
                                    value="{{ old('customer_name') }}" :required="customerType === 'new'" :disabled="customerType !== 'new'">
                                @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="customer_email" class="form-label">{{ __('messages.Email') }}</label>
                                <input type="email" class="form-control @error('customer_email') is-invalid @enderror" id="customer_email" name="customer_email"
                                    value="{{ old('customer_email') }}" :disabled="customerType !== 'new'">
                                @error('customer_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="customer_phone" class="form-label">{{ __('messages.Phone') }}</label>
                                <input type="text" class="form-control @error('customer_phone') is-invalid @enderror" id="customer_phone" name="customer_phone"
                                    value="{{ old('customer_phone') }}" :disabled="customerType !== 'new'">
                                @error('customer_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <p class="text-muted small mb-0">{{ __('messages.Provide email or phone') }}</p>
                            </div>
                        </div>
                    </div>

                    <div x-show="customerType === 'new' || addressMode === 'new'" x-cloak>
                        <hr class="my-4">
                        <h6 class="mb-3">{{ __('messages.Shipping address') }}</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="address_name" class="form-label">{{ __('messages.Address label') }} *</label>
                                <input type="text" class="form-control @error('address_name') is-invalid @enderror" id="address_name" name="address_name"
                                    value="{{ old('address_name') }}" placeholder="{{ __('messages.e.g. Home, Work') }}"
                                    :disabled="customerType === 'existing' && addressMode === 'existing'">
                                @error('address_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="address_phone" class="form-label">{{ __('messages.Phone') }}</label>
                                <input type="text" class="form-control @error('address_phone') is-invalid @enderror" id="address_phone" name="address_phone"
                                    value="{{ old('address_phone') }}"
                                    :disabled="customerType === 'existing' && addressMode === 'existing'">
                                @error('address_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="address" class="form-label">{{ __('messages.Address') }} *</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2"
                                    :disabled="customerType === 'existing' && addressMode === 'existing'">{{ old('address') }}</textarea>
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label">{{ __('messages.City') }}</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}"
                                    :disabled="customerType === 'existing' && addressMode === 'existing'">
                                @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="state" class="form-label">{{ __('messages.State') }}</label>
                                <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state') }}"
                                    :disabled="customerType === 'existing' && addressMode === 'existing'">
                                @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="latitude" class="form-label">{{ __('messages.Latitude') }} *</label>
                                <input type="text" class="form-control @error('latitude') is-invalid @enderror" id="latitude" name="latitude"
                                    value="{{ old('latitude') }}" placeholder="29.3759"
                                    :disabled="customerType === 'existing' && addressMode === 'existing'">
                                @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="longitude" class="form-label">{{ __('messages.Longitude') }} *</label>
                                <input type="text" class="form-control @error('longitude') is-invalid @enderror" id="longitude" name="longitude"
                                    value="{{ old('longitude') }}" placeholder="47.9774"
                                    :disabled="customerType === 'existing' && addressMode === 'existing'">
                                @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ __('messages.Order items') }} *</h5>
                    <button type="button" class="btn btn-sm btn-primary" id="addItemBtn">
                        <i class="bi bi-plus-lg me-1"></i>{{ __('messages.Add Item') }}
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40%">{{ __('messages.Product') }}</th>
                                <th style="width: 25%">{{ __('messages.Variant') }}</th>
                                <th style="width: 15%">{{ __('messages.Price') }}</th>
                                <th style="width: 15%">{{ __('messages.Qty') }}</th>
                                <th style="width: 5%"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">{{ __('messages.Order Details') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">{{ __('messages.Status') }} *</label>
                        <select class="form-select" id="status" name="status" required>
                            @foreach(['pending', 'processing', 'ready_for_delivery', 'shipped', 'delivered'] as $status)
                                <option value="{{ $status }}" @selected(old('status', 'pending') === $status)>{{ __('messages.'.$status) }}</option>
                            @endforeach
                        </select>
                    </div>

                    @include('v1.admin.orders.partials.payment-method-fields', [
                        'selectedMethod' => old('payment_method', 'cash_on_delivery'),
                    ])

                    <div class="mb-3">
                        <label for="payment_status" class="form-label">{{ __('messages.Payment status') }} *</label>
                        <select class="form-select" id="payment_status" name="payment_status" required>
                            <option value="pending" @selected(old('payment_status', 'pending') === 'pending')>{{ __('messages.Pending') }}</option>
                            <option value="paid" @selected(old('payment_status') === 'paid')>{{ __('messages.Paid') }}</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="shipping_cost" class="form-label">{{ __('messages.Shipping cost') }}</label>
                        <input type="number" step="0.01" class="form-control" id="shipping_cost" name="shipping_cost" value="{{ old('shipping_cost') }}" placeholder="{{ __('messages.Leave blank to auto calculate') }}">
                        <small class="text-muted">{{ __('messages.Admin order shipping auto hint') }}</small>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">{{ __('messages.Notes') }}</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check2-circle me-1"></i>{{ __('messages.Create order') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="itemRowTemplate">
    <tr>
        <td>
            <select class="form-select product-select" name="items[__INDEX__][product_id]" required>
                <option value="">{{ __('messages.Select product') }}</option>
            </select>
        </td>
        <td>
            <select class="form-select variant-select" name="items[__INDEX__][variant_id]" disabled>
                <option value="">—</option>
            </select>
        </td>
        <td>
            <input type="number" step="0.01" class="form-control item-price" name="items[__INDEX__][unit_price]" required min="0">
        </td>
        <td>
            <input type="number" class="form-control item-qty" name="items[__INDEX__][quantity]" value="1" required min="1">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
    window.__orderCreate = {
        products: @json($productsData),
        labels: {
            selectProduct: @json(__('messages.Select product')),
        },
    };
</script>
@endpush
