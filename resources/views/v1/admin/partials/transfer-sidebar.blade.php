@props([
    'importBatches' => collect(),
    'exportBatches' => collect(),
    'showRoutePrefix',
    'downloadRoutePrefix',
])

<div class="card border-0 shadow-sm sidebar-panel mb-3">
    <div class="card-header bg-white py-2">
        <h2 class="h6 mb-0"><i class="bi bi-upload me-1"></i>{{ __('messages.Import logs') }}</h2>
    </div>
    <div class="card-body p-0">
        <div class="table-scroll-x">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.Status') }}</th>
                        <th>{{ __('messages.Progress') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($importBatches as $batch)
                        <tr>
                            <td>{{ $batch->id }}</td>
                            <td>
                                @php
                                    $statusClass = match ($batch->status) {
                                        'completed' => 'success',
                                        'failed' => 'danger',
                                        'processing' => 'warning',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }} text-capitalize">{{ $batch->status }}</span>
                            </td>
                            <td class="small text-muted">
                                {{ $batch->processed_rows }}/{{ $batch->total_rows }}
                                <br>
                                <span class="text-success">{{ $batch->success_count }}</span> /
                                <span class="text-danger">{{ $batch->failed_count }}</span>
                            </td>
                            <td>
                                <a href="{{ route($showRoutePrefix.'.show', $batch) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('messages.Details') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted small text-center py-3">{{ __('messages.No import jobs yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm sidebar-panel">
    <div class="card-header bg-white py-2">
        <h2 class="h6 mb-0"><i class="bi bi-download me-1"></i>{{ __('messages.Export logs') }}</h2>
    </div>
    <div class="card-body p-0">
        <div class="table-scroll-x">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.Status') }}</th>
                        <th>{{ __('messages.Rows') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exportBatches as $batch)
                        <tr>
                            <td>{{ $batch->id }}</td>
                            <td>
                                @php
                                    $statusClass = match ($batch->status) {
                                        'completed' => 'success',
                                        'failed' => 'danger',
                                        'processing' => 'warning',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }} text-capitalize">{{ $batch->status }}</span>
                            </td>
                            <td class="small text-muted">{{ $batch->total_rows }}</td>
                            <td class="text-nowrap">
                                @if ($batch->hasDownloadableFile())
                                    <a href="{{ route($downloadRoutePrefix.'.download', $batch) }}" class="btn btn-sm btn-outline-primary" title="{{ __('messages.Download file') }}">
                                        <i class="bi bi-download"></i>
                                    </a>
                                @endif
                                <a href="{{ route($showRoutePrefix.'.show', $batch) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('messages.Details') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted small text-center py-3">{{ __('messages.No export jobs yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
