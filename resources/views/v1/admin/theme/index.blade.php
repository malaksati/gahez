@extends('layouts.app')

@section('title', __('messages.Store theme'))
@section('subtitle', __('messages.Customize colors, layouts, and typography for the customer app'))

@section('page-actions')
    <button type="submit" form="theme-form" class="btn btn-primary">
        <i class="bi bi-check-lg me-2"></i>{{ __('messages.Save changes') }}
    </button>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <form id="theme-form" action="{{ route('v1.admin.theme.update') }}" method="POST" class="card border-0 shadow-sm">
                @csrf
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">{{ __('messages.Colors') }}</h2>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="store_primary_color" class="form-label">{{ __('messages.Primary color') }}</label>
                            <input type="color" class="form-control form-control-color w-100 @error('store_primary_color') is-invalid @enderror"
                                id="store_primary_color" name="store_primary_color"
                                value="{{ old('store_primary_color', $theme['primary_color']) }}">
                            @error('store_primary_color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="store_secondary_color" class="form-label">{{ __('messages.Secondary color') }}</label>
                            <input type="color" class="form-control form-control-color w-100 @error('store_secondary_color') is-invalid @enderror"
                                id="store_secondary_color" name="store_secondary_color"
                                value="{{ old('store_secondary_color', $theme['secondary_color']) }}">
                            @error('store_secondary_color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h2 class="h6 text-uppercase text-muted mb-3">{{ __('messages.Layout') }}</h2>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="store_category_layout" class="form-label">{{ __('messages.Category layout') }}</label>
                            <select class="form-select @error('store_category_layout') is-invalid @enderror" id="store_category_layout" name="store_category_layout">
                                @foreach ($layoutOptions as $layout)
                                    <option value="{{ $layout }}" @selected(old('store_category_layout', $theme['category_layout']) === $layout)>
                                        {{ $layout === 'horizontal' ? __('messages.Horizontal') : __('messages.Vertical') }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('messages.Category layout hint') }}</small>
                            @error('store_category_layout')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="store_product_layout" class="form-label">{{ __('messages.Product layout') }}</label>
                            <select class="form-select @error('store_product_layout') is-invalid @enderror" id="store_product_layout" name="store_product_layout">
                                @foreach ($layoutOptions as $layout)
                                    <option value="{{ $layout }}" @selected(old('store_product_layout', $theme['product_layout']) === $layout)>
                                        {{ $layout === 'horizontal' ? __('messages.Horizontal') : __('messages.Vertical') }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('messages.Product layout hint') }}</small>
                            @error('store_product_layout')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h2 class="h6 text-uppercase text-muted mb-3">{{ __('messages.Typography') }}</h2>
                    <div class="mb-0">
                        <label for="store_font_family" class="form-label">{{ __('messages.Font family') }}</label>
                        <select class="form-select @error('store_font_family') is-invalid @enderror" id="store_font_family" name="store_font_family">
                            @foreach ($fontOptions as $font)
                                <option value="{{ $font }}" @selected(old('store_font_family', $theme['font_family']) === $font)>{{ $font }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ __('messages.Font family hint') }}</small>
                        @error('store_font_family')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            </form>
{{-- 
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-2">{{ __('messages.Frontend integration') }}</h2>
                    <p class="mb-2">{{ __('messages.Store theme api hint') }}</p>
                    <code class="d-block p-2 bg-light rounded">GET /api/v1/store/config</code>
                    <p class="mt-3 mb-0 small text-muted">{{ __('messages.Store theme admin only hint') }}</p>
                </div>
            </div> --}}
        </div>
    </div>
@endsection
