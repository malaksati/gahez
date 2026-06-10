@extends('layouts.app')

@section('title', __('messages.Orders'))
@section('subtitle', __('messages.Manage orders'))

@section('content')
    <div class="d-flex justify-content-between mb-3 align-items-center flex-wrap gap-2">
        <form action="{{ route('v1.admin.orders.index') }}" method="GET" class="d-flex gap-2 mb-0 flex-grow-1" style="max-width: 500px;">
            @foreach($filters ?? [] as $key => $value)
                @if($key !== 'search' && $key !== 'show_all')
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <input type="text" name="search" id="liveSearchInput" class="form-control form-control-sm" placeholder="{{ __('messages.Search by name or phone...') }}" value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary btn-sm d-none d-md-inline-block"><i class="bi bi-search"></i></button>
            
            @if(request()->has('show_all'))
                <a href="{{ route('v1.admin.orders.index', request()->except('show_all')) }}" class="btn btn-secondary btn-sm text-nowrap">
                    {{ __('messages.Show Active Orders') }}
                </a>
            @else
                <a href="{{ route('v1.admin.orders.index', array_merge(request()->query(), ['show_all' => 1])) }}" class="btn btn-outline-secondary btn-sm text-nowrap">
                    {{ __('messages.Show All Orders') }}
                </a>
            @endif
        </form>

        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#orderFiltersOffcanvas">
                <i class="bi bi-sliders me-1"></i>{{ __('messages.Filter') }}
            </button>
            <a href="{{ route('v1.admin.orders.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>{{ __('messages.New Order') }}
            </a>
        </div>
    </div>

    @include('v1.admin.orders.partials.filters', ['filters' => $filters ?? []])

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0" id="ordersTableContainer">
            @include('v1.admin.orders.partials.table', ['orders' => $orders])
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('liveSearchInput');
        const tableContainer = document.getElementById('ordersTableContainer');

        if (!searchInput || !tableContainer) {
            return;
        }

        let searchTimeout;

        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(async () => {
                const url = new URL(window.location.href);
                const term = searchInput.value.trim();

                if (term) {
                    url.searchParams.set('search', term);
                } else {
                    url.searchParams.delete('search');
                }

                try {
                    const response = await fetch(url.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            Accept: 'text/html',
                        },
                    });

                    if (!response.ok) {
                        throw new Error(`Search failed: ${response.status}`);
                    }

                    tableContainer.innerHTML = await response.text();
                    window.history.pushState({}, '', url);
                } catch (error) {
                    console.error('Orders live search failed', error);
                }
            }, 400);
        });
    });
</script>
@endpush
