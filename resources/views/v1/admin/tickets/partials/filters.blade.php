<x-admin-filters-card>
        <form method="GET" action="{{ route('v1.admin.tickets.index') }}" class="row g-2 align-items-end" data-admin-list-filters>
            @include('v1.admin.partials.filter-search-input', ['col' => 'col-md-3', 'placeholder' => __('messages.Search tickets')])
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('messages.Status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('messages.All') }}</option>
                    <option value="pending" @selected(request('status') === 'pending')>{{ __('messages.Pending') }}</option>
                    <option value="resolved" @selected(request('status') === 'resolved')>{{ __('messages.Resolved') }}</option>
                    <option value="closed" @selected(request('status') === 'closed')>{{ __('messages.Closed') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('messages.Type') }}</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">{{ __('messages.All') }}</option>
                    @foreach (\App\Models\Ticket::types() as $ticketType)
                        <option value="{{ $ticketType }}" @selected(request('type') === $ticketType)>
                            {{ \App\Models\Ticket::typeLabel($ticketType) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('messages.From') }}</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('messages.To') }}</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
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
                <a href="{{ route('v1.admin.tickets.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('messages.Reset') }}</a>
            </div>
        </form></x-admin-filters-card>