@extends('layouts.app')

@section('title', __('messages.Product reports'))
@section('subtitle', __('messages.Manage product reports'))

@section('content')
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('v1.admin.product-reports.index') }}" class="row g-2 align-items-end" data-admin-list-filters>
                @include('v1.admin.partials.filter-search-input', ['placeholder' => __('messages.Search')])
                <div class="col-md-3">
                    <label class="form-label small mb-1">{{ __('messages.Status') }}</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">{{ __('messages.All') }}</option>
                        <option value="pending" @selected(request('status') === 'pending')>{{ __('messages.Pending') }}</option>
                        <option value="reviewed" @selected(request('status') === 'reviewed')>{{ __('messages.Reviewed') }}</option>
                        <option value="ignored" @selected(request('status') === 'ignored')>{{ __('messages.Ignored') }}</option>
                    </select>
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('messages.Apply filters') }}</button>
                    <a href="{{ route('v1.admin.product-reports.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('messages.Reset') }}</a>
                </div>
            </form>
        </div>
    </div>
    <div data-admin-list-results>
        @include('v1.admin.product-reports.partials.results', ['reports' => $reports])
    </div>
@endsection
