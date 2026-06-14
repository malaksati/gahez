<x-admin-filters-card>
        <form method="GET" action="{{ route('v1.admin.variants.index') }}" class="row g-2 align-items-end" data-admin-list-filters>
            @include('v1.admin.partials.filter-search-input', ['placeholder' => __('messages.Search variants')])
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('messages.Status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('messages.All') }}</option>
                    <option value="active" @selected(request('status') === 'active')>{{ __('messages.Active') }}</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>{{ __('messages.Inactive') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('messages.Required') }}</label>
                <select name="required" class="form-select form-select-sm">
                    <option value="">{{ __('messages.All') }}</option>
                    <option value="1" @selected(request('required') === '1')>{{ __('messages.Yes') }}</option>
                    <option value="0" @selected(request('required') === '0')>{{ __('messages.No') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('messages.Sort') }}</label>
                <select name="sort" class="form-select form-select-sm">
                    <option value="latest" @selected(request('sort', 'latest') === 'latest')>{{ __('messages.Newest') }}</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>{{ __('messages.Oldest') }}</option>
                </select>
            </div>
            <div class="col-12 col-md-auto d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>{{ __('messages.Apply filters') }}</button>
                <a href="{{ route('v1.admin.variants.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('messages.Reset') }}</a>
            </div>
        </form></x-admin-filters-card>