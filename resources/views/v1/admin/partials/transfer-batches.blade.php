@props([
    'batches' => collect(),
    'showRoutePrefix',
    'downloadRoutePrefix',
])

@if ($batches->isNotEmpty())
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h2 class="h6 mb-3">{{ __('messages.Recent import export jobs') }}</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('messages.Direction') }}</th>
                            <th>{{ __('messages.Status') }}</th>
                            <th>{{ __('messages.Progress') }}</th>
                            <th>{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($batches as $batch)
                            <tr>
                                <td>{{ $batch->id }}</td>
                                <td><span class="badge bg-light text-dark border text-capitalize">{{ $batch->direction }}</span></td>
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
                                    @if ($batch->direction === 'import')
                                        @num($batch->processed_rows) / @num($batch->total_rows)
                                        · {{ __('messages.Success') }}: @num($batch->success_count)
                                        · {{ __('messages.Failed') }}: @num($batch->failed_count)
                                    @else
                                        @num($batch->total_rows) {{ __('messages.rows') }}
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route($showRoutePrefix.'.show', $batch) }}" class="btn btn-sm btn-outline-secondary">{{ __('messages.Details') }}</a>
                                    @if ($batch->hasDownloadableFile())
                                        <a href="{{ route($downloadRoutePrefix.'.download', $batch) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
