@extends('layouts.app')

@php($page = 'settings')

@section('title', __('messages.Settings'))
@section('subtitle', __('messages.Manage your application preferences and configuration'))

@section('page-actions')
    <button type="submit" form="settings-form" class="btn btn-primary">
        <i class="bi bi-check-lg me-2"></i>{{ __('messages.Save changes') }}
    </button>
@endsection

@section('content')
    <div class="settings-page">
        <div class="settings-layout">
            <form
                id="settings-form"
                action="{{ route('v1.admin.settings.update') }}"
                method="POST"
                enctype="multipart/form-data"
                class="settings-form"
                data-settings-upload
            >
                @csrf

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h2 class="h6 text-uppercase text-muted mb-3">{{ __('messages.General') }}</h2>

                        <div class="mb-3">
                            <label for="app_name" class="form-label">{{ __('messages.App name') }}</label>
                            <input
                                type="text"
                                class="form-control @error('app_name') is-invalid @enderror"
                                id="app_name"
                                name="app_name"
                                value="{{ old('app_name', setting('app_name')) }}"
                                required
                            >
                            @error('app_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <label for="currency" class="form-label">{{ __('messages.Currency') }}</label>
                            <input
                                type="text"
                                class="form-control @error('currency') is-invalid @enderror"
                                id="currency"
                                name="currency"
                                value="{{ old('currency', setting('currency', config('app.currency', 'USD'))) }}"
                                maxlength="10"
                                required
                                placeholder="{{ __('messages.Currency placeholder') }}"
                            >
                            <small class="text-muted">{{ __('messages.Currency settings hint') }}</small>
                            @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h2 class="h6 text-uppercase text-muted mb-3">{{ __('messages.Cashback & points') }}</h2>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="cashback_percentage" class="form-label">{{ __('messages.Cashback percentage') }}</label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        class="form-control @error('cashback_percentage') is-invalid @enderror"
                                        id="cashback_percentage"
                                        name="cashback_percentage"
                                        value="{{ old('cashback_percentage', setting('cashback_percentage', 0)) }}"
                                    >
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">{{ __('messages.Cashback percentage hint') }}</small>
                                @error('cashback_percentage')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="point_to_value" class="form-label">{{ __('messages.Point to value') }}</label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-control @error('point_to_value') is-invalid @enderror"
                                        id="point_to_value"
                                        name="point_to_value"
                                        value="{{ old('point_to_value', setting('point_to_value', 1)) }}"
                                    >
                                    <span class="input-group-text">{{ setting('currency', app_currency()) }}</span>
                                </div>
                                <small class="text-muted">{{ __('messages.Point to value hint') }}</small>
                                @error('point_to_value')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h2 class="h6 text-uppercase text-muted mb-3">{{ __('messages.Reports') }}</h2>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="report_hero_order_amount" class="form-label">{{ __('messages.Hero order amount') }}</label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-control @error('report_hero_order_amount') is-invalid @enderror"
                                        id="report_hero_order_amount"
                                        name="report_hero_order_amount"
                                        value="{{ old('report_hero_order_amount', setting('report_hero_order_amount', 100)) }}"
                                    >
                                    <span class="input-group-text">{{ setting('currency', app_currency()) }}</span>
                                </div>
                                <small class="text-muted">{{ __('messages.Hero order amount hint') }}</small>
                                @error('report_hero_order_amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="report_lower_value_order_amount" class="form-label">{{ __('messages.Lower value order amount') }}</label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-control @error('report_lower_value_order_amount') is-invalid @enderror"
                                        id="report_lower_value_order_amount"
                                        name="report_lower_value_order_amount"
                                        value="{{ old('report_lower_value_order_amount', setting('report_lower_value_order_amount', 20)) }}"
                                    >
                                    <span class="input-group-text">{{ setting('currency', app_currency()) }}</span>
                                </div>
                                <small class="text-muted">{{ __('messages.Lower value order amount hint') }}</small>
                                @error('report_lower_value_order_amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h6 text-uppercase text-muted mb-3">{{ __('messages.Branding') }}</h2>

                        @include('v1.admin.settings.partials.image-upload', [
                            'fieldId' => 'app_logo',
                            'label' => __('messages.App logo'),
                            'hint' => __('messages.App logo favicon hint'),
                            'existingPath' => setting('app_logo'),
                        ])
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/admin-settings.js')
@endpush
