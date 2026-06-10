@extends('layouts.app')

@section('title', __('messages.Variant options'))
@section('subtitle', __('messages.Manage variant options'))

@section('page-actions')
    <a href="{{ route('v1.admin.variant-options.export') }}" class="btn btn-outline-primary">
        <i class="bi bi-download me-1"></i>{{ __('messages.Export') }}
    </a>
    <a href="{{ route('v1.admin.variant-options.import') }}" class="btn btn-outline-success">
        <i class="bi bi-upload me-1"></i>{{ __('messages.Import') }}
    </a>
    <a href="{{ route('v1.admin.variant-options.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>{{ __('messages.New option') }}
    </a>
@endsection

@section('content')
    <x-import-export-layout
        :show-logs="true"
        :import-batches="$importBatches"
        :export-batches="$exportBatches"
        :show-route-prefix="$showRoutePrefix"
        :download-route-prefix="$downloadRoutePrefix"
    >
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0 table-scroll-x">
                @include('v1.admin.variant-options.partials.table', ['variantOptions' => $variantOptions])
            </div>
        </div>
    </x-import-export-layout>
@endsection
