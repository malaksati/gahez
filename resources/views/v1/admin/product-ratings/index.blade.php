@extends('layouts.app')

@section('title', __('messages.Product ratings'))
@section('subtitle', __('messages.Manage product ratings'))

@section('content')
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('v1.admin.product-ratings.index') }}" class="row g-2 align-items-end" data-admin-list-filters>
                @include('v1.admin.partials.filter-search-input', ['col' => 'col-md-3', 'placeholder' => __('messages.Search')])
                <div class="col-md-2">
                    <label class="form-label small mb-1">{{ __('messages.Rating') }}</label>
                    <select name="rating" class="form-select form-select-sm">
                        <option value="">{{ __('messages.All') }}</option>
                        @for ($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" @selected((string) request('rating') === (string) $i)>{{ $i }} ★</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">{{ __('messages.Visibility') }}</label>
                    <select name="visibility" class="form-select form-select-sm">
                        <option value="">{{ __('messages.All') }}</option>
                        <option value="visible" @selected(request('visibility') === 'visible')>{{ __('messages.Visible') }}</option>
                        <option value="hidden" @selected(request('visibility') === 'hidden')>{{ __('messages.Hidden') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">{{ __('messages.Product') }}</label>
                    <select name="product_id" class="form-select form-select-sm">
                        <option value="">{{ __('messages.All') }}</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((string) request('product_id') === (string) $product->id)>
                                {{ $product->getTranslation('name', app()->getLocale(), false) ?: $product->getTranslation('name', 'en') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('messages.Apply filters') }}</button>
                    <a href="{{ route('v1.admin.product-ratings.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('messages.Reset') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div data-admin-list-results>
        @include('v1.admin.product-ratings.partials.results', ['ratings' => $ratings])
    </div>
@endsection
