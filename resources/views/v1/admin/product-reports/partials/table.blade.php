@php
    $locale = app()->getLocale();
@endphp

@if ($reports->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h2 class="h6 mb-0">{{ __('messages.Product reports') }}</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('messages.Product') }}</th>
                            <th>{{ __('messages.Customer') }}</th>
                            <th>{{ __('messages.Reason') }}</th>
                            <th>{{ __('messages.Description') }}</th>
                            <th>{{ __('messages.Status') }}</th>
                            <th>{{ __('messages.Handled by') }}</th>
                            <th>{{ __('messages.Date') }}</th>
                            <th class="text-end">{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $report)
                            @php
                                $status = $report->status ?? 'pending';
                                $statusBadge = match ($status) {
                                    'reviewed' => 'bg-success',
                                    'ignored' => 'bg-secondary',
                                    default => 'bg-warning text-dark',
                                };
                                $statusLabel = match ($status) {
                                    'reviewed' => __('messages.Reviewed'),
                                    'ignored' => __('messages.Ignored'),
                                    default => __('messages.Pending'),
                                };
                            @endphp
                            <tr>
                                <td>{{ $report->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $report->product?->getTranslation('name', $locale, false) ?: '—' }}</div>
                                    <small class="text-muted">#{{ $report->product_id }}</small>
                                </td>
                                <td>
                                    {{ $report->user?->name ?? __('messages.Guest') }}
                                    <div><small class="text-muted">#{{ $report->user_id ?? '—' }}</small></div>
                                </td>
                                <td>{{ $report->reason ?: '—' }}</td>
                                <td class="text-muted">{{ Str::limit($report->description, 80) ?: '—' }}</td>
                                <td><span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span></td>
                                <td>
                                    {{ $report->handler?->name ?? '—' }}
                                    @if ($report->handled_at)
                                        <div><small class="text-muted">{{ $report->handled_at->format('Y-m-d H:i') }}</small></div>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $report->created_at?->format('Y-m-d H:i') ?? '—' }}</small>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        @if ($status !== 'reviewed')
                                            <form action="{{ route('v1.admin.product-reports.update-status', [$report, 'reviewed']) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm(@json(__('messages.Confirm mark report as reviewed?')))">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="bi bi-check2-circle me-1"></i>{{ __('messages.Mark as reviewed') }}
                                                </button>
                                            </form>
                                        @endif
                                        @if ($status !== 'ignored')
                                            <form action="{{ route('v1.admin.product-reports.update-status', [$report, 'ignored']) }}"
                                                  method="POST"
                                                  class="d-inline ms-1"
                                                  onsubmit="return confirm(@json(__('messages.Confirm ignore this report?')))">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-slash-circle me-1"></i>{{ __('messages.Ignore') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 px-3 pb-3">{{ $reports->links() }}</div>
        </div>
    </div>
@else
    @include('v1.admin.partials.table-empty', ['icon' => 'flag', 'message' => __('messages.No product reports found.')])
@endif
