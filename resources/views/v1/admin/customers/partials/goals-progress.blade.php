@php
    $locale = app()->getLocale();
    $currency = display_currency();
@endphp

<div class="card border-0 shadow-sm mb-4 mt-3">
    <div class="card-header bg-transparent border-bottom">
        <h6 class="mb-0">{{ __('messages.Goal progress') }}</h6>
    </div>
    <div class="card-body">
        @if (count($goalProgress) > 0)
            <div class="row g-3">
                @foreach ($goalProgress as $row)
                    @php
                        $goal = $row['goal'];
                        $name = $goal->getTranslation('name', $locale, false) ?: $goal->getTranslation('name', 'en');
                        $percent = (float) $row['progress_percent'];
                        $isAchieved = (bool) $row['is_achieved'];
                        $barClass = $isAchieved ? 'bg-success' : 'bg-primary';
                    @endphp
                    <div class="col-12">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <strong>{{ $name }}</strong>
                                    <span class="badge bg-light text-dark border ms-1">
                                        {{ __('messages.Goal period '.$goal->period_type) }}
                                    </span>
                                </div>
                                @if ($isAchieved)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>{{ __('messages.Achieved') }}
                                    </span>
                                @endif
                            </div>

                            <div class="progress mb-2" style="height: 8px;">
                                <div
                                    class="progress-bar {{ $barClass }}"
                                    role="progressbar"
                                    style="width: {{ $percent }}%"
                                    aria-valuenow="{{ $percent }}"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                ></div>
                            </div>

                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">{{ __('messages.Progress') }}</span>
                                <strong>{{ format_local_number($percent, 0) }}%</strong>
                            </div>

                            <div class="small text-muted mb-1">
                                {{ format_local_number((float) $row['order_total'], 2) }} {{ $currency }}
                                /
                                {{ format_local_number((float) $row['min_order_total'], 2) }} {{ $currency }}
                                {{ __('messages.Target') }}
                            </div>

                            <div class="small text-muted mb-1">
                                {{ __('messages.Reward') }}:
                                <strong class="text-body">{{ format_local_number((float) $row['reward_amount'], 2) }} {{ $currency }}</strong>
                            </div>

                            <div class="row g-2 mt-1">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">{{ __('messages.Current period') }}</small>
                                    <span class="small">
                                        <i class="bi bi-calendar-range me-1"></i>
                                        {{ \Illuminate\Support\Carbon::parse($row['period_start'])->format('M d') }}
                                        —
                                        {{ \Illuminate\Support\Carbon::parse($row['period_end'])->format('M d, Y') }}
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">{{ __('messages.Awarded at') }}</small>
                                    @if ($isAchieved && $row['achieved_at'])
                                        <span class="small text-success">
                                            <i class="bi bi-wallet2 me-1"></i>
                                            {{ \Illuminate\Support\Carbon::parse($row['achieved_at'])->format('M d, Y H:i') }}
                                        </span>
                                    @else
                                        <span class="small text-muted">—</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-muted py-3">
                <i class="bi bi-bullseye fs-2 d-block mb-2 text-opacity-50"></i>
                {{ __('messages.No active goals.') }}
            </div>
        @endif
    </div>
</div>
