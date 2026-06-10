@extends('layouts.app')

@php
    $page = 'products';
@endphp

@section('title', __('messages.Products'))
@section('subtitle', __('messages.Manage products'))

@section('page-actions')
    <a href="{{ route('v1.admin.products.export', request()->query()) }}" class="btn btn-outline-primary">
        <i class="bi bi-download me-1"></i>{{ __('messages.Export') }}
    </a>
    <a href="{{ route('v1.admin.products.import') }}" class="btn btn-outline-success">
        <i class="bi bi-upload me-1"></i>{{ __('messages.Import') }}
    </a>
    <a href="{{ route('v1.admin.products.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>{{ __('messages.New product') }}
    </a>
@endsection

@section('content')
    <x-import-export-layout>
        @include('v1.admin.products.partials.filters')
        <div data-admin-list-results>
            @include('v1.admin.products.partials.results', [
                'products' => $products,
                'uncategorizedCount' => $uncategorizedCount ?? 0,
            ])
        </div>
    </x-import-export-layout>
@endsection

@push('scripts')
    <script>
        window.__productsIndexLabels = {
            confirmDelete: @json(__('messages.Are you sure you want to delete?')),
            cannotUndo: @json(__('messages.This action cannot be undone.')),
            deleteFailed: @json('Failed to delete product.'),
        };

        document.addEventListener('input', (event) => {
            const input = event.target;

            if (!input.matches('[data-category-product-search]')) {
                return;
            }

            const section = input.closest('.accordion-item') ?? input.closest('[data-product-list-search]');
            const query = input.value.trim().toLowerCase();

            section?.querySelectorAll('[data-product-search]').forEach((row) => {
                const haystack = row.dataset.productSearch || '';
                row.style.display = haystack.includes(query) ? '' : 'none';
            });
        });
    </script>
@endpush
