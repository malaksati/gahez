@extends('layouts.app')

@section('title', __('messages.Import products'))
@section('subtitle', __('messages.Import products from Excel file'))

@section('page-actions')
    <a href="{{ route('v1.admin.products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>{{ __('messages.Back to products') }}
    </a>
@endsection

@section('content')
    <x-import-export-layout>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h2 class="h6 mb-0"><i class="bi bi-info-circle me-2"></i>{{ __('messages.Import instructions') }}</h2>
            </div>
            <div class="card-body">
                <ol class="mb-0 small">
                    <li class="mb-2">
                        <a href="{{ route('v1.admin.products.import.template') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download"></i> {{ __('messages.Download Excel template') }}
                        </a>
                    </li>
                    <li class="mb-2">
                        <code>name_en</code>, <code>name_ar</code>, <code>type</code>, <code>sku</code>, <code>price</code>,
                        <code>stock</code> (leave empty for unlimited stock), <code>is_in_stock</code> (1/0 when stock is empty),
                        <code>unit_code</code>, <code>unit_factor</code>,
                        <code>discount</code>, <code>discount_type</code>, <code>thumbnail</code>,
                        <code>brand_id</code>, <code>category_ids</code> (names or IDs like <code>21|24</code>), flags <code>is_active</code>…
                    </li>
                    <li>{{ __('messages.Import products order note') }}</li>
                </ol>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h2 class="h6 mb-0"><i class="bi bi-upload me-2"></i>{{ __('messages.Upload Excel file') }}</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('v1.admin.products.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="file" class="form-label fw-semibold">{{ __('messages.Select Excel file') }}</label>
                        <input type="file" id="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls,.csv" required>
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>{{ __('messages.Import products') }}</button>
                </form>
            </div>
        </div>

        <div class="row g-2">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-2"><h3 class="h6 mb-0">{{ __('messages.Available brands') }}</h3></div>
                    <div class="card-body d-flex flex-wrap gap-1">
                        @foreach ($brands as $brand)
                            <span class="badge bg-light text-dark border small">{{ $brand->id }} — {{ $brand->getTranslation('name', 'en') }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-2"><h3 class="h6 mb-0">{{ __('messages.Available categories') }}</h3></div>
                    <div class="card-body d-flex flex-wrap gap-1">
                        @foreach ($allCategories as $category)
                            <span class="badge bg-light text-dark border small">{{ $category->id }} — {{ $category->getTranslation('name', 'en') }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </x-import-export-layout>
@endsection
