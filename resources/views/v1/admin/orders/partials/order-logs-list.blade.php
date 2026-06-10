@if ($order->logs->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>{{ __('messages.ID') }}</th>
                    <th>{{ __('messages.Order') }}</th>
                    <th>{{ __('messages.User') }}</th>
                    <th>{{ __('messages.Type') }}</th>
                    <th>{{ __('messages.From') }}</th>
                    <th>{{ __('messages.To') }}</th>
                    <th>{{ __('messages.Payload') }}</th>
                    <th>{{ __('messages.Created at') }}</th>
                    <th>{{ __('messages.Updated at') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->logs as $log)
                    <tr>
                        <td><small><code>#{{ $log->id }}</code></small></td>
                        <td><small><code>#{{ $log->order_id ?? '—' }}</code></small></td>
                        <td><small>{{ $log->user?->name ?? __('messages.System') }}</small></td>
                        <td><small>{{ $log->formattedType() }}</small></td>
                        <td><small>{{ $log->formattedStatus($log->from_status) }}</small></td>
                        <td><small>{{ $log->formattedStatus($log->to_status) }}</small></td>
                        <td>
                            @if ($formattedPayload = $log->formattedPayload())
                                <pre class="small mb-0 text-muted" style="max-width: 220px; white-space: pre-wrap;">{{ $formattedPayload }}</pre>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                        <td><small class="text-muted">{{ $log->created_at?->format('M d, Y H:i') ?? '—' }}</small></td>
                        <td><small class="text-muted">{{ $log->updated_at?->format('M d, Y H:i') ?? '—' }}</small></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <p class="text-muted mb-0 p-3">{{ __('messages.No logs yet for this order.') }}</p>
@endif
