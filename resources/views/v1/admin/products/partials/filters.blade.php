<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('v1.admin.products.index') }}" class="row g-2 align-items-end" data-admin-list-filters>
            @if (request('view') === 'all')
                <input type="hidden" name="view" value="all">
            @endif
            @include('v1.admin.partials.filter-search-input', ['col' => 'col-md-3', 'placeholder' => __('messages.Search products')])
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('messages.Status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('messages.All') }}</option>
                    <option value="active" @selected(request('status') === 'active')>{{ __('messages.Active') }}</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>{{ __('messages.Inactive') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('messages.Type') }}</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">{{ __('messages.All') }}</option>
                    <option value="simple" @selected(request('type') === 'simple')>{{ __('messages.Simple') }}</option>
                    <option value="variable" @selected(request('type') === 'variable')>{{ __('messages.Variable') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('messages.Category') }}</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">{{ __('messages.All') }}</option>
                    @foreach ($filterCategories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                            {{ $category->getTranslation('name', app()->getLocale(), false) ?: $category->getTranslation('name', 'en') }}
                        </option>
                    @endforeach
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
            <div class="col-md-1">
                <label class="form-label small mb-1">{{ __('messages.Sort') }}</label>
                <select name="sort" class="form-select form-select-sm">
                    <option value="latest" @selected(request('sort', 'latest') === 'latest')>{{ __('messages.Newest') }}</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>{{ __('messages.Oldest') }}</option>
                </select>
            </div>
            <div class="col-12 col-md-auto d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>{{ __('messages.Apply filters') }}</button>
                @if (request('view') === 'all')
                    <a href="{{ route('v1.admin.products.index', request()->except('view', 'page')) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-grid me-1"></i>{{ __('messages.Show by category') }}
                    </a>
                @else
                    <a href="{{ route('v1.admin.products.index', array_merge(request()->query(), ['view' => 'all', 'page' => null])) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-list-ul me-1"></i>{{ __('messages.Show all products') }}
                    </a>
                @endif
                <a href="{{ route('v1.admin.products.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('messages.Reset') }}</a>
            </div>
        </form>
    </div>
</div>
