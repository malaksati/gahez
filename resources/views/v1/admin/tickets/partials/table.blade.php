@php use Illuminate\Support\Str; @endphp
@if ($tickets->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('messages.ID') }}</th>
                    <th>{{ __('messages.Subject') }}</th>
                    <th>{{ __('messages.Customer') }}</th>
                    <th>{{ __('messages.Status') }}</th>
                    <th>{{ __('messages.Messages') }}</th>
                    <th>{{ __('messages.Created at') }}</th>
                    <th class="text-end" style="width: 100px;">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tickets as $ticket)
                    @php
                        $statusBadge = match ($ticket->status) {
                            'pending' => 'warning',
                            'resolved' => 'success',
                            default => 'secondary',
                        };
                    @endphp
                    <tr>
                        <td><strong>#{{ $ticket->id }}</strong></td>
                        <td>
                            {{ Str::limit($ticket->subject, 50) }}
                            @if ($ticket->attachments && count($ticket->attachments) > 0)
                                <i class="bi bi-paperclip text-muted ms-1" title="{{ __('messages.Attachments') }}"></i>
                            @endif
                        </td>
                        <td>{{ $ticket->user?->name ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $statusBadge }} text-capitalize">
                                {{ __('messages.'.$ticket->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $ticket->messages_count ?? $ticket->messages->count() }}</span>
                        </td>
                        <td class="small text-muted">{{ $ticket->created_at?->format('M d, Y H:i') }}</td>
                        <td class="text-end">
                            @include('v1.admin.partials.table-actions', [
                                'showUrl' => route('v1.admin.tickets.show', $ticket),
                                'editUrl' => route('v1.admin.tickets.edit', $ticket),
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 px-3 pb-3">{{ $tickets->links() }}</div>
@else
    @include('v1.admin.partials.table-empty', [
        'icon' => 'headset',
        'message' => __('messages.No tickets.'),
    ])
@endif
