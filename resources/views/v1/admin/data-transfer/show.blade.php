@extends('layouts.app')

@section('title', $title)
@section('subtitle', __('messages.Import export job details'))

@section('page-actions')
    <a href="{{ route($backRoute) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>{{ __('messages.Back') }}
    </a>
    @if ($batch->hasDownloadableFile())
        <a href="{{ route($downloadRoute, $batch) }}" class="btn btn-primary" data-batch-download>
            <i class="bi bi-download me-1"></i>{{ __('messages.Download file') }}
        </a>
    @endif
@endsection

@section('content')
    <div
        id="data-transfer-batch"
        class="data-transfer-batch"
        data-status-url="{{ route('v1.admin.data-transfer.batches.status', $batch) }}"
        @unless($batch->isFinished()) data-poll="true" @endunless
    >
        @unless($batch->isFinished())
            <div class="alert alert-info d-none mb-3" data-batch-worker-warning role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ __('messages.Import job waiting for worker.') }}
            </div>
        @endunless

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">{{ __('messages.Status') }}</dt>
                    <dd class="col-sm-9 text-capitalize">
                        <span data-batch-field="status">{{ $batch->status }}</span>
                        <span
                            class="data-transfer-batch__processing ms-2"
                            data-batch-processing
                            @if($batch->isFinished()) hidden @endif
                        >
                            <span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span>
                            <span class="visually-hidden">{{ __('messages.Loading...') }}</span>
                        </span>
                    </dd>
                    <dt class="col-sm-3">{{ __('messages.Direction') }}</dt>
                    <dd class="col-sm-9 text-capitalize" data-batch-field="direction">{{ $batch->direction }}</dd>
                    <dt class="col-sm-3">{{ __('messages.Progress') }}</dt>
                    <dd class="col-sm-9">
                        <span data-batch-field="processed_rows">{{ $batch->processed_rows }}</span>
                        /
                        <span data-batch-field="total_rows">{{ $batch->total_rows > 0 ? $batch->total_rows : '—' }}</span>
                    </dd>
                    <dt class="col-sm-3">{{ __('messages.Success') }}</dt>
                    <dd class="col-sm-9" data-batch-field="success_count">{{ $batch->success_count }}</dd>
                    <dt class="col-sm-3">{{ __('messages.Failed') }}</dt>
                    <dd class="col-sm-9" data-batch-field="failed_count">{{ $batch->failed_count }}</dd>
                    <dt class="col-sm-3">{{ __('messages.Skipped') }}</dt>
                    <dd class="col-sm-9" data-batch-field="skipped_count">{{ $batch->skipped_count ?? 0 }}</dd>
                    <dt class="col-sm-3">{{ __('messages.Message') }}</dt>
                    <dd class="col-sm-9" data-batch-field="message">{{ $batch->message ?: '—' }}</dd>
                    @if ($batch->direction === 'export' && $batch->status === 'completed' && ! $batch->hasDownloadableFile())
                        <dt class="col-sm-3">{{ __('messages.Export file') }}</dt>
                        <dd class="col-sm-9 text-muted">{{ __('messages.Export file is no longer available.') }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        @if ($batch->rowLogs->isNotEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0">{{ __('messages.Validation errors') }} ({{ $batch->rowLogs->count() }})</h2>
                </div>
                <div class="card-body p-0">
                    <div class="table-scroll-x">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('messages.Row') }}</th>
                                    <th>{{ __('messages.Errors') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($batch->rowLogs as $log)
                                    <tr>
                                        <td>{{ $log->row_number }}</td>
                                        <td>
                                            <ul class="mb-0 small">
                                                @foreach ($log->errors as $field => $messages)
                                                    <li><strong>{{ $field }}:</strong> {{ is_array($messages) ? implode(', ', $messages) : $messages }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@unless($batch->isFinished())
    @push('scripts')
        <script>
            (function () {
                const root = document.getElementById('data-transfer-batch');
                if (!root || root.dataset.poll !== 'true') return;

                const statusUrl = root.dataset.statusUrl;
                if (!statusUrl) return;

                let pollTimer = null;

                const stopPolling = () => {
                    if (!pollTimer) return;
                    clearInterval(pollTimer);
                    pollTimer = null;
                };

                const updateFields = (data) => {
                    root.querySelectorAll('[data-batch-field]').forEach((element) => {
                        const field = element.dataset.batchField;
                        if (field === 'total_rows') {
                            element.textContent = data.total_rows > 0 ? String(data.total_rows) : '—';
                            return;
                        }
                        if (data[field] === undefined || data[field] === null || data[field] === '') {
                            if (field === 'message') element.textContent = '—';
                            return;
                        }
                        element.textContent = String(data[field]);
                    });

                    const indicator = root.querySelector('[data-batch-processing]');
                    if (indicator) {
                        indicator.hidden = data.is_finished || !['pending', 'processing'].includes(data.status);
                    }

                    const warning = root.querySelector('[data-batch-worker-warning]');
                    if (warning) {
                        warning.classList.toggle('d-none', !data.is_stale_pending);
                    }
                };

                const poll = async () => {
                    try {
                        const response = await fetch(statusUrl, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (!response.ok) return;

                        const data = await response.json();
                        updateFields(data);

                        if (data.is_finished) {
                            stopPolling();
                            window.location.reload();
                        }
                    } catch (error) {
                        console.error('Data transfer batch polling failed', error);
                    }
                };

                pollTimer = window.setInterval(poll, 2000);
                poll();

                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        stopPolling();
                        return;
                    }
                    poll();
                    if (!pollTimer) pollTimer = window.setInterval(poll, 2000);
                });
            })();
        </script>
    @endpush
@endunless
