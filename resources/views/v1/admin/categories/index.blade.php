@extends('layouts.app')

@php
    $page = 'categories';
@endphp

@section('title', __('messages.Categories'))
@section('subtitle', __('messages.Manage product categories.'))

@section('page-actions')
    <a href="{{ route('v1.admin.categories.export', request()->query()) }}" class="btn btn-outline-primary">
        <i class="bi bi-download me-1"></i>{{ __('messages.Export') }}
    </a>
    <a href="{{ route('v1.admin.categories.import') }}" class="btn btn-outline-success">
        <i class="bi bi-upload me-1"></i>{{ __('messages.Import') }}
    </a>
    <a href="{{ route('v1.admin.categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>{{ __('messages.New category') }}
    </a>
@endsection

@section('content')
    <x-import-export-layout>
        @include('v1.admin.categories.partials.filters')
        <div data-admin-list-results>
            @include('v1.admin.categories.partials.results')
        </div>
    </x-import-export-layout>
@endsection

@push('scripts')
<script>
    window.__categoriesIndexLabels = {
        confirmDelete: @json(__('messages.Are you sure you want to delete?')),
        confirmDeleteWithChildren: @json(__('messages.This category has :count subcategories. Deleting it will also delete all subcategories.')),
        cannotUndo: @json(__('messages.This action cannot be undone.')),
        deleteFailed: @json(__('messages.Failed to delete category.')),
    };
</script>
@endpush
