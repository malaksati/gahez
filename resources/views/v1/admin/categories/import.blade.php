@extends('layouts.app')

@section('title', __('messages.Import categories'))
@section('subtitle', __('messages.Import categories from Excel file'))

@section('page-actions')
    <a href="{{ route('v1.admin.categories.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>{{ __('messages.Back to categories') }}
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
                        <strong>{{ __('messages.Download template') }}:</strong>
                        <a href="{{ route('v1.admin.categories.import.template') }}" class="btn btn-sm btn-outline-primary ms-1">
                            <i class="bi bi-download"></i> {{ __('messages.Download Excel template') }}
                        </a>
                    </li>
                    <li class="mb-2">
                        <strong>{{ __('messages.Fill the template') }}:</strong>
                        <code>name_en</code>, <code>name_ar</code>, <code>slug</code>, <code>image</code>,
                        <code>is_active</code>, <code>is_featured</code>, <code>sort_order</code>, <code>parent_id</code>, <code>parent_slug</code>
                    </li>
                    <li>{{ __('messages.Import file formats note') }}</li>
                </ol>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h2 class="h6 mb-0"><i class="bi bi-upload me-2"></i>{{ __('messages.Upload Excel file') }}</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('v1.admin.categories.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="file" class="form-label fw-semibold">{{ __('messages.Select Excel file') }}</label>
                        <input type="file" id="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls,.csv" required>
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>{{ __('messages.Import categories') }}</button>
                        <a href="{{ route('v1.admin.categories.index') }}" class="btn btn-outline-secondary">{{ __('messages.Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h2 class="h6 mb-0">{{ __('messages.Available parent categories') }}</h2>
            </div>
            <div class="card-body d-flex flex-wrap gap-2">
                @forelse ($allCategories as $category)
                    <span class="badge bg-light text-dark border">{{ $category->id }} — {{ $category->getTranslation('name', app()->getLocale(), false) ?: $category->getTranslation('name', 'en') }} ({{ $category->slug }})</span>
                @empty
                    <p class="text-muted small mb-0">{{ __('messages.No parent categories yet') }}</p>
                @endforelse
            </div>
        </div>
    </x-import-export-layout>
@endsection
