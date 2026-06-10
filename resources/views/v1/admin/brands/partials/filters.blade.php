<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('v1.admin.brands.index') }}" class="row g-2 align-items-end" data-admin-list-filters>
            @include('v1.admin.partials.filter-search-input', ['col' => 'col-md-6', 'placeholder' => __('messages.Search brands')])
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('messages.Sort') }}</label>
                <select name="sort" class="form-select form-select-sm">
                    <option value="latest" @selected(request('sort', 'latest') === 'latest')>{{ __('messages.Newest') }}</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>{{ __('messages.Oldest') }}</option>
                    <option value="name_asc" @selected(request('sort') === 'name_asc')>{{ __('messages.Name A-Z') }}</option>
                    <option value="name_desc" @selected(request('sort') === 'name_desc')>{{ __('messages.Name Z-A') }}</option>
                </select>
            </div>
            <div class="col-12 col-md-auto d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>{{ __('messages.Apply filters') }}</button>
                <a href="{{ route('v1.admin.brands.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('messages.Reset') }}</a>
            </div>
        </form>
    </div>
</div>
