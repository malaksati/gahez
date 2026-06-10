<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('v1.admin.categories.index') }}" class="row g-2 align-items-end" data-admin-list-filters>
            @include('v1.admin.partials.filter-search-input', ['placeholder' => __('messages.Search categories')])
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('messages.Status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('messages.All') }}</option>
                    <option value="active" @selected(request('status') === 'active')>{{ __('messages.Active') }}</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>{{ __('messages.Inactive') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('messages.Featured') }}</label>
                <select name="featured" class="form-select form-select-sm">
                    <option value="">{{ __('messages.All') }}</option>
                    <option value="1" @selected(request('featured') === '1')>{{ __('messages.Yes') }}</option>
                    <option value="0" @selected(request('featured') === '0')>{{ __('messages.No') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('messages.Parent category') }}</label>
                <select name="parent_id" class="form-select form-select-sm">
                    <option value="">{{ __('messages.All') }}</option>
                    <option value="root" @selected(request('parent_id') === 'root')>{{ __('messages.Root only') }}</option>
                    @foreach ($parentCategories as $parent)
                        <option value="{{ $parent->id }}" @selected((string) request('parent_id') === (string) $parent->id)>
                            {{ $parent->getTranslation('name', app()->getLocale(), false) ?: $parent->getTranslation('name', 'en') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('messages.Sort') }}</label>
                <select name="sort" class="form-select form-select-sm">
                    <option value="sort_order" @selected(request('sort', 'sort_order') === 'sort_order')>{{ __('messages.Sort order') }}</option>
                    <option value="latest" @selected(request('sort') === 'latest')>{{ __('messages.Newest') }}</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>{{ __('messages.Oldest') }}</option>
                    <option value="name_asc" @selected(request('sort') === 'name_asc')>{{ __('messages.Name A-Z') }}</option>
                    <option value="name_desc" @selected(request('sort') === 'name_desc')>{{ __('messages.Name Z-A') }}</option>
                </select>
            </div>
            <div class="col-12 col-md-auto d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>{{ __('messages.Apply filters') }}</button>
                <a href="{{ route('v1.admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('messages.Reset') }}</a>
            </div>
        </form>
    </div>
</div>
