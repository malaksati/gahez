@extends('layouts.app')

@section('title', __('messages.Import variants'))
@section('subtitle', __('messages.Import variants from Excel file'))

@section('page-actions')
    <a href="{{ route('v1.admin.variants.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>{{ __('messages.Back to variants') }}
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
                        <a href="{{ route('v1.admin.variants.import.template') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download"></i> {{ __('messages.Download Excel template') }}
                        </a>
                    </li>
                    <li class="mb-2">
                        <code>id</code> ({{ __('messages.Optional') }} — {{ __('messages.for updates') }}),
                        <code>name_en</code>, <code>name_ar</code>, <code>is_required</code>, <code>is_active</code>
                    </li>
                    <li class="mb-2">
                        {{ __('messages.Variants import options note') }}
                        <code>option_id</code>, <code>option_name_en</code>, <code>option_name_ar</code>, <code>option_code</code>
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
                <form action="{{ route('v1.admin.variants.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="file" class="form-label fw-semibold">{{ __('messages.Select Excel file') }}</label>
                        <input type="file" id="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls,.csv" required>
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>{{ __('messages.Import variants') }}</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2"><h3 class="h6 mb-0">{{ __('messages.Available variants') }}</h3></div>
            <div class="card-body d-flex flex-wrap gap-1">
                @forelse ($allVariants as $variant)
                    <span class="badge bg-light text-dark border small">{{ $variant->id }} — {{ $variant->getTranslation('name', 'en') }}</span>
                @empty
                    <p class="text-muted small mb-0">{{ __('messages.No variants yet') }}</p>
                @endforelse
            </div>
        </div>
    </x-import-export-layout>
@endsection
