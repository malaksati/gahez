<x-admin-filters-card>
        <form method="GET" action="{{ route('v1.admin.support-chats.index') }}" class="row g-2 align-items-end" data-admin-list-filters>
            @include('v1.admin.partials.filter-search-input', ['col' => 'col-md-3', 'placeholder' => __('messages.Search support chats')])
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('messages.Status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('messages.All') }}</option>
                    <option value="open" @selected(request('status') === 'open')>{{ __('messages.open') }}</option>
                    <option value="closed" @selected(request('status') === 'closed')>{{ __('messages.closed') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('messages.Assigned agent') }}</label>
                <select name="assigned_admin_id" class="form-select form-select-sm">
                    <option value="">{{ __('messages.All') }}</option>
                    @foreach ($admins as $admin)
                        <option value="{{ $admin->id }}" @selected((string) request('assigned_admin_id') === (string) $admin->id)>
                            {{ $admin->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1 d-block">&nbsp;</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="unassigned" value="1" id="unassigned-filter" @checked(request()->boolean('unassigned'))>
                    <label class="form-check-label small" for="unassigned-filter">{{ __('messages.Unassigned only') }}</label>
                </div>
            </div>
            <div class="col-12 col-md-auto d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>{{ __('messages.Apply filters') }}</button>
                <a href="{{ route('v1.admin.support-chats.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('messages.Reset') }}</a>
            </div>
        </form></x-admin-filters-card>