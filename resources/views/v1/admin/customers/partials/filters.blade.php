<x-admin-filters-card>
        <form method="GET" action="{{ route('v1.admin.customers.index') }}" class="row g-2 align-items-end" data-admin-list-filters>
            @include('v1.admin.partials.filter-search-input', ['col' => 'col-md-6', 'placeholder' => __('messages.Search by name, email or phone')])
            <div class="col-12 col-md-auto d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>{{ __('messages.Apply filters') }}</button>
                <a href="{{ route('v1.admin.customers.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('messages.Reset') }}</a>
            </div>
        </form></x-admin-filters-card>