@extends('layouts.app')

@php
    $page = 'products';
    $locale = app()->getLocale();
    $currency = app_currency();
    $productName = $product->getTranslation('name', $locale, false) ?: $product->getTranslation('name', 'en');
    $sellableUnits = $product->productUnits->where('is_active', true)->values();
    $hasUnitPricing = $sellableUnits->isNotEmpty();
    $defaultProductUnit = $hasUnitPricing ? $product->defaultProductUnit() : null;
    $displayPrice = $hasUnitPricing && $defaultProductUnit
        ? (float) $defaultProductUnit->price
        : (float) $product->price;
    $unitFinal = $hasUnitPricing && $defaultProductUnit
        ? (float) $defaultProductUnit->final_price
        : (float) $product->price;
    $displayFinalPrice = $product->hasDiscount()
        ? (float) $product->final_price
        : $unitFinal;
    $displayHasDiscount = ($hasUnitPricing && $defaultProductUnit && $defaultProductUnit->discount > 0)
        || $product->hasDiscount();
@endphp

@section('title', $productName)
@section('heading', __('messages.Product details'))

@section('content')
    <div class="mb-4">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('v1.admin.dashboard') }}">{{ __('messages.Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('v1.admin.products.index') }}">{{ __('messages.Products') }}</a></li>
                <li class="breadcrumb-item active">{{ $productName }}</li>
            </ol>
        </nav>

        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h2 class="h4 mb-2">{{ $productName }}</h2>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $product->is_active ? __('messages.Active') : __('messages.Inactive') }}
                    </span>
                    @if ($product->is_featured)
                        <span class="badge bg-warning text-dark">{{ __('messages.Featured') }}</span>
                    @endif
                    @if ($product->is_approved)
                        <span class="badge bg-success">{{ __('messages.Approved') }}</span>
                    @else
                        <span class="badge bg-warning text-dark">{{ __('messages.Pending') }}</span>
                    @endif
                    @if ($product->is_new)
                        <span class="badge bg-info">{{ __('messages.New product') }}</span>
                    @endif
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('v1.admin.products.edit', $product) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>{{ __('messages.Edit product') }}
                </a>
                <form action="{{ route('v1.admin.products.destroy', $product) }}" method="POST"
                    data-confirm-message="{{ __('messages.Are you sure you want to delete?') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>{{ __('messages.Delete') }}
                    </button>
                </form>
                <a href="{{ route('v1.admin.products.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>{{ __('messages.Back') }}
                </a>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center g-4">
                <div class="col-md-3 text-center">
                    <img src="{{ $product->thumbnail }}" alt="{{ $productName }}"
                        class="img-fluid rounded border"
                        style="max-width: 200px; max-height: 200px; object-fit: cover;">
                </div>
                <div class="col-md-9">
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-3">
                            <small class="text-muted d-block">
                                {{ $hasUnitPricing ? __('messages.Default unit price') : __('messages.Price') }}
                            </small>
                            <strong class="fs-5">{{ number_format($displayPrice, 2) }}{{ $currency ? ' '.$currency : '' }}</strong>
                            @if ($hasUnitPricing && $defaultProductUnit)
                                <div class="small text-muted">{{ $defaultProductUnit->displayUnitName($locale) }}</div>
                            @endif
                            @if ($displayHasDiscount)
                                <div class="small text-success">
                                    {{ __('messages.Final price') }}:
                                    {{ number_format($displayFinalPrice, 2) }}{{ $currency ? ' '.$currency : '' }}
                                </div>
                            @endif
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <small class="text-muted d-block">{{ __('messages.Stock') }}</small>
                            @if ($hasUnitPricing)
                                <strong class="d-block text-body">
                                    {{ $sellableUnits->count() }} {{ __('messages.Sellable units') }}
                                </strong>
                                @if ($defaultProductUnit)
                                    <div class="small {{ $defaultProductUnit->isInStock() ? 'text-success' : 'text-danger' }}">
                                        {{ __('messages.Default') }}:
                                        @if ($defaultProductUnit->tracksStock())
                                            {{ $defaultProductUnit->stock > 0 ? $defaultProductUnit->stock.' '.__('messages.In stock') : __('messages.Out of stock') }}
                                        @else
                                            {{ $defaultProductUnit->is_in_stock ? __('messages.Available') : __('messages.Out of stock') }}
                                        @endif
                                    </div>
                                @endif
                            @else
                                <strong class="d-block {{ $product->isInStock() ? 'text-success' : 'text-danger' }}">
                                    @if ($product->tracksStock())
                                        {{ $product->stock > 0 ? $product->stock.' '.__('messages.In stock') : __('messages.Out of stock') }}
                                    @else
                                        {{ $product->is_in_stock ? __('messages.Available') : __('messages.Out of stock') }}
                                        <span class="text-muted small">({{ __('messages.Untracked quantity') }})</span>
                                    @endif
                                </strong>
                            @endif
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <small class="text-muted d-block">{{ __('messages.Product units') }}</small>
                            <strong>{{ $product->formattedUnitsSummary($locale) ?: '—' }}</strong>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <small class="text-muted d-block">{{ __('messages.Type') }}</small>
                            <span class="badge bg-light text-dark border">{{ ucfirst($product->type ?? 'simple') }}</span>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <small class="text-muted d-block">{{ __('messages.Brand') }}</small>
                            <strong>
                                {{ $product->displayBrandName($locale) ?: '—' }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-details" type="button">
                        <i class="bi bi-info-circle me-1"></i>{{ __('messages.Details') }}
                    </button>
                </li>
                @if ($product->images->isNotEmpty())
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-images" type="button">
                            <i class="bi bi-images me-1"></i>{{ __('messages.Product images') }}
                            <span class="badge bg-primary ms-1">{{ $product->images->count() }}</span>
                        </button>
                    </li>
                @endif
                @if ($product->isVariable())
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-variants" type="button">
                            <i class="bi bi-tags me-1"></i>{{ __('messages.Variants') }}
                            <span class="badge bg-primary ms-1">{{ $product->variants->count() }}</span>
                        </button>
                    </li>
                @endif
                @if ($product->categories->isNotEmpty())
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-categories" type="button">
                            <i class="bi bi-grid me-1"></i>{{ __('messages.Categories') }}
                            <span class="badge bg-primary ms-1">{{ $product->categories->count() }}</span>
                        </button>
                    </li>
                @endif
                @if ($product->relatedProducts->isNotEmpty())
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-related" type="button">
                            <i class="bi bi-link-45deg me-1"></i>{{ __('messages.Related products') }}
                            <span class="badge bg-primary ms-1">{{ $product->relatedProducts->count() }}</span>
                        </button>
                    </li>
                @endif
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-details" role="tabpanel">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">{{ __('messages.Product information') }}</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <small class="text-muted">{{ __('messages.Name') }} ({{ __('messages.English') }})</small>
                                    <p class="mb-0 fw-semibold">{{ $product->getTranslation('name', 'en') ?: '—' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">{{ __('messages.Name') }} ({{ __('messages.Arabic') }})</small>
                                    <p class="mb-0 fw-semibold" dir="rtl">{{ $product->getTranslation('name', 'ar') ?: '—' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">{{ __('messages.SKU') }}</small>
                                    <p class="mb-0"><code>{{ $product->sku }}</code></p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">{{ __('messages.Slug') }}</small>
                                    <p class="mb-0"><code>{{ $product->slug }}</code></p>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">{{ __('messages.Description') }} ({{ __('messages.English') }})</small>
                                    <p class="mb-2">{{ $product->getTranslation('description', 'en') ?: '—' }}</p>
                                    <small class="text-muted">{{ __('messages.Description') }} ({{ __('messages.Arabic') }})</small>
                                    <p class="mb-0" dir="rtl">{{ $product->getTranslation('description', 'ar') ?: '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">{{ __('messages.Pricing information') }}</h5>

                            @if ($hasUnitPricing)
                                <p class="text-muted small mb-3">{{ __('messages.Product pricing units show hint') }}</p>
                                @if ((float) $product->price > 0)
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">{{ __('messages.Base price') }}</small>
                                            <strong>{{ number_format((float) $product->price, 2) }}{{ $currency ? ' '.$currency : '' }}</strong>
                                            <div class="form-text mb-0">{{ __('messages.Product base price reference hint') }}</div>
                                        </div>
                                        @if ($product->hasDiscount())
                                            <div class="col-md-4">
                                                <small class="text-muted d-block">{{ __('messages.Product level discount') }}</small>
                                                <strong class="text-success">
                                                    @if ($product->discount_type === 'percentage')
                                                        {{ $product->discount }}%
                                                    @else
                                                        {{ number_format((float) $product->discount, 2) }}{{ $currency ? ' '.$currency : '' }}
                                                    @endif
                                                </strong>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @include('v1.admin.products.partials.show-pricing-units', [
                                    'product' => $product,
                                    'locale' => $locale,
                                    'currency' => $currency,
                                ])
                            @else
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">{{ __('messages.Base price') }}</small>
                                        <strong>{{ number_format((float) $product->price, 2) }}{{ $currency ? ' '.$currency : '' }}</strong>
                                    </div>
                                    @if ($product->hasDiscount())
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">{{ __('messages.Discount value') }}</small>
                                            <strong class="text-success">
                                                @if ($product->discount_type === 'percentage')
                                                    {{ $product->discount }}%
                                                @else
                                                    {{ number_format((float) $product->discount, 2) }}{{ $currency ? ' '.$currency : '' }}
                                                @endif
                                            </strong>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">{{ __('messages.Final price') }}</small>
                                            <strong class="text-success">{{ number_format($product->final_price, 2) }}{{ $currency ? ' '.$currency : '' }}</strong>
                                        </div>
                                    @endif
                                    @if ($product->isSimple())
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">{{ __('messages.Stock') }}</small>
                                            <strong class="{{ $product->isInStock() ? 'text-success' : 'text-danger' }}">
                                                @if ($product->tracksStock())
                                                    {{ $product->stock ?? 0 }}
                                                @else
                                                    {{ $product->is_in_stock ? __('messages.Available') : __('messages.Out of stock') }}
                                                @endif
                                            </strong>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($product->images->isNotEmpty())
                    <div class="tab-pane fade" id="tab-images" role="tabpanel">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title mb-3">{{ __('messages.Product images') }}</h5>
                                    <div class="row g-3">
                                        @foreach ($product->images as $image)
                                            <div class="col-md-4 col-lg-3">
                                                <img src="{{ $image->image_path }}" alt=""
                                                    class="img-fluid rounded border w-100"
                                                    style="height: 180px; object-fit: cover;">
                                            </div>
                                        @endforeach
                                    </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($product->isVariable())
                    <div class="tab-pane fade" id="tab-variants" role="tabpanel">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title mb-3">{{ __('messages.Product variants') }}</h5>
                                @include('v1.admin.products.partials.show-variants-tab', [
                                    'product' => $product,
                                    'locale' => $locale,
                                    'currency' => $currency,
                                ])
                            </div>
                        </div>
                    </div>
                @endif

                @if ($product->categories->isNotEmpty())
                    <div class="tab-pane fade" id="tab-categories" role="tabpanel">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title mb-3">{{ __('messages.Categories') }}</h5>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($product->categories as $category)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">
                                            {{ $category->getTranslation('name', $locale, false) ?: $category->getTranslation('name', 'en') }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($product->relatedProducts->isNotEmpty())
                    <div class="tab-pane fade" id="tab-related" role="tabpanel">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title mb-3">{{ __('messages.Related products') }}</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>{{ __('messages.Name') }}</th>
                                                <th>{{ __('messages.SKU') }}</th>
                                                <th>{{ __('messages.Price') }}</th>
                                                <th class="text-end">{{ __('messages.Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($product->relatedProducts as $relation)
                                                @php $related = $relation->relatedProduct; @endphp
                                                @if ($related)
                                                    <tr>
                                                        <td>
                                                            {{ $related->getTranslation('name', $locale, false) ?: $related->getTranslation('name', 'en') }}
                                                        </td>
                                                        <td><code class="small">{{ $related->sku }}</code></td>
                                                        <td>{{ number_format((float) $related->price, 2) }}{{ $currency ? ' '.$currency : '' }}</td>
                                                        <td class="text-end">
                                                            <a href="{{ route('v1.admin.products.show', $related) }}"
                                                                class="btn btn-sm btn-outline-secondary">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">{{ __('messages.Status and settings') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>{{ __('messages.Active') }}</span>
                            @include('v1.admin.partials.active-badge', ['active' => $product->is_active])
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>{{ __('messages.Featured') }}</span>
                            <span class="badge {{ $product->is_featured ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                {{ $product->is_featured ? __('messages.Yes') : __('messages.No') }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>{{ __('messages.Approved') }}</span>
                            <span class="badge {{ $product->is_approved ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $product->is_approved ? __('messages.Yes') : __('messages.No') }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>{{ __('messages.Bookable') }}</span>
                            <span class="badge {{ $product->is_bookable ? 'bg-success' : 'bg-secondary' }}">
                                {{ $product->is_bookable ? __('messages.Yes') : __('messages.No') }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">{{ __('messages.Statistics') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">{{ __('messages.Total images') }}</span>
                        <strong>{{ $product->images->count() }}</strong>
                    </div>
                    @if ($product->isVariable())
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">{{ __('messages.Total variants') }}</span>
                            <strong>{{ $product->variants->count() }}</strong>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">{{ __('messages.Categories') }}</span>
                        <strong>{{ $product->categories->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">{{ __('messages.Related products') }}</span>
                        <strong>{{ $product->relatedProducts->count() }}</strong>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">{{ __('messages.Timestamps') }}</h5>
                </div>
                <div class="card-body">
                    <small class="text-muted d-block">{{ __('messages.Created at') }}</small>
                    <p class="mb-3">{{ $product->created_at?->format('M d, Y H:i') }} <span class="text-muted">({{ $product->created_at?->diffForHumans() }})</span></p>
                    <small class="text-muted d-block">{{ __('messages.Updated at') }}</small>
                    <p class="mb-0">{{ $product->updated_at?->format('M d, Y H:i') }} <span class="text-muted">({{ $product->updated_at?->diffForHumans() }})</span></p>
                </div>
            </div>
        </div>
    </div>
@endsection
