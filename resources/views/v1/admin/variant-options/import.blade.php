@extends('layouts.app')

@section('title', __('messages.Import variant options'))
@section('subtitle', __('messages.Import variant options from Excel file'))

@section('page-actions')
    <a href="{{ route('v1.admin.variant-options.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>{{ __('messages.Back to variant options') }}
    </a>
@endsection

@section('content')
    <x-import-export-layout :show-logs="true"
        :import-batches="$importBatches"
        :export-batches="$exportBatches"
        show-route-prefix="v1.admin.variant-options.import-export"
        download-route-prefix="v1.admin.variant-options.import-export"
    >
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h2 class="h6 mb-0"><i class="bi bi-info-circle me-2"></i>{{ __('messages.Import instructions') }}</h2>
            </div>
            <div class="card-body">
                <ol class="mb-0 small">
                    <li class="mb-2">
                        <a href="{{ route('v1.admin.variant-options.import.template') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download"></i> {{ __('messages.Download Excel template') }}
                        </a>
                    </li>
                    <li class="mb-2">
                        <code>variant_id</code>, <code>code</code> ({{ __('messages.unique') }}),
                        <code>name_en</code>, <code>name_ar</code>, <code>id</code> ({{ __('messages.Optional') }})
                    </li>
                    <li>{{ __('messages.Import variant options order note') }}</li>
                </ol>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h2 class="h6 mb-0"><i class="bi bi-upload me-2"></i>{{ __('messages.Upload Excel file') }}</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('v1.admin.variant-options.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="file" class="form-label fw-semibold">{{ __('messages.Select Excel file') }}</label>
                        <input type="file" id="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls,.csv" required>
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>{{ __('messages.Import variant options') }}</button>
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
