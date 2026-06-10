@extends('layouts.app')

@section('title', __('messages.Edit order'))
@section('heading', __('messages.Order #:id', ['id' => $order->id]))

@section('content')
    <form action="{{ route('v1.admin.orders.update', $order) }}" method="POST" class="card border-0 shadow-sm col-lg-8">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="status" class="form-label">{{ __('messages.Order status') }}</label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                        @foreach (['pending', 'processing', 'ready_for_delivery', 'cancelled', 'refunded'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $order->status) === $status)>{{ __('messages.'.$status) }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="payment_status" class="form-label">{{ __('messages.Payment status') }}</label>
                    <select name="payment_status" id="payment_status" class="form-select @error('payment_status') is-invalid @enderror">
                        @foreach (['pending', 'paid', 'failed', 'refunded'] as $paymentStatus)
                            <option value="{{ $paymentStatus }}" @selected(old('payment_status', $order->payment_status) === $paymentStatus)>{{ __('messages.'.$paymentStatus) }}</option>
                        @endforeach
                    </select>
                    @error('payment_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    @include('v1.admin.orders.partials.payment-method-fields', [
                        'selectedMethod' => old('payment_method', $order->payment_method),
                    ])
                </div>
                <div class="col-12">
                    <label for="notes" class="form-label">{{ __('messages.Notes') }}</label>
                    <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $order->notes) }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            
            @if (in_array($order->status, ['pending', 'processing']))
                <hr class="my-4">
                <h5 class="mb-3">{{ __('messages.Customer information') }}</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="customer_name" class="form-label">{{ __('messages.Name') }}</label>
                        <input type="text" name="customer_name" id="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name', $order->customer_name) }}">
                        @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="customer_phone" class="form-label">{{ __('messages.Phone') }}</label>
                        <input type="text" name="customer_phone" id="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone', $order->customer_phone) }}">
                        @error('customer_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                @php $snapshot = $order->shipping_address_snapshot ?? []; @endphp
                <div x-data="{
                    editingAddress: {{ empty($snapshot) || $errors->has('shipping_*') ? 'true' : 'false' }},
                    hasAddress: {{ !empty($snapshot) || old('shipping_name') ? 'true' : 'false' }},
                    removeAddress: {{ old('remove_address') ? 'true' : 'false' }},
                    deleteAddress() {
                        this.hasAddress = false;
                        this.editingAddress = false;
                        this.removeAddress = true;
                    }
                }">
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                        <h5 class="mb-0">{{ __('messages.Shipping address') }}</h5>
                        <template x-if="!hasAddress">
                            <button type="button" class="btn btn-sm btn-outline-primary" @click="hasAddress = true; editingAddress = true; removeAddress = false">
                                <i class="bi bi-plus-lg"></i> {{ __('messages.Add address') }}
                            </button>
                        </template>
                    </div>

                    <input type="hidden" name="remove_address" :value="removeAddress ? '1' : '0'">

                    <template x-if="hasAddress">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start" x-show="!editingAddress">
                                    <div>
                                        <strong>{{ $snapshot['name'] ?? old('shipping_name') }}</strong><br>
                                        {{ $snapshot['address'] ?? old('shipping_address') }}<br>
                                        {{ $snapshot['city'] ?? old('shipping_city') }}, {{ $snapshot['state'] ?? old('shipping_state') }}<br>
                                        {{ $snapshot['phone'] ?? old('shipping_phone') }}
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-white shadow-sm text-primary me-2" @click="editingAddress = true" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16"><path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/></svg>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-white shadow-sm text-danger" @click="deleteAddress()" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16"><path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="row g-3" x-show="editingAddress" style="display: none;">
                                    <div class="col-md-6">
                                        <label for="shipping_name" class="form-label">{{ __('messages.Name') }}</label>
                                        <input type="text" name="shipping_name" id="shipping_name" class="form-control @error('shipping_name') is-invalid @enderror" value="{{ old('shipping_name', $snapshot['name'] ?? '') }}">
                                        @error('shipping_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="shipping_phone" class="form-label">{{ __('messages.Phone') }}</label>
                                        <input type="text" name="shipping_phone" id="shipping_phone" class="form-control @error('shipping_phone') is-invalid @enderror" value="{{ old('shipping_phone', $snapshot['phone'] ?? '') }}">
                                        @error('shipping_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="shipping_address" class="form-label">{{ __('messages.Address') }}</label>
                                        <textarea name="shipping_address" id="shipping_address" rows="2" class="form-control @error('shipping_address') is-invalid @enderror">{{ old('shipping_address', $snapshot['address'] ?? '') }}</textarea>
                                        @error('shipping_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="shipping_city" class="form-label">{{ __('messages.City') }}</label>
                                        <input type="text" name="shipping_city" id="shipping_city" class="form-control @error('shipping_city') is-invalid @enderror" value="{{ old('shipping_city', $snapshot['city'] ?? '') }}">
                                        @error('shipping_city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="shipping_state" class="form-label">{{ __('messages.State') }}</label>
                                        <input type="text" name="shipping_state" id="shipping_state" class="form-control @error('shipping_state') is-invalid @enderror" value="{{ old('shipping_state', $snapshot['state'] ?? '') }}">
                                        @error('shipping_state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            @else
                <hr class="my-4">
                <div class="alert alert-info mb-0">
                    {{ __('messages.Customer and shipping details can only be edited when the order is pending or processing.') }}
                </div>
            @endif
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('messages.Save') }}</button>
                <a href="{{ route('v1.admin.orders.show', $order) }}" class="btn btn-outline-secondary">{{ __('messages.Cancel') }}</a>
            </div>
        </div>
    </form>
@endsection
