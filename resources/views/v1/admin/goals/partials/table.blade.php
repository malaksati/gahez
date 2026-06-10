@php
    $locale = app()->getLocale();
    $currency = app_currency();
@endphp

@if ($goals->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('messages.Name') }}</th>
                    <th>{{ __('messages.Period') }}</th>
                    <th>{{ __('messages.Target') }}</th>
                    <th>{{ __('messages.Reward') }}</th>
                    <th>{{ __('messages.Validity') }}</th>
                    <th>{{ __('messages.Active') }}</th>
                    <th class="text-end" style="width: 140px;">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($goals as $goal)
                    <tr>
                        <td>
                            <strong>{{ $goal->getTranslation('name', $locale, false) ?: $goal->getTranslation('name', 'en') }}</strong>
                        </td>
                        <td>{{ __('messages.Goal period '.$goal->period_type) }}</td>
                        <td>{{ number_format((float) $goal->min_order_total, 2) }} {{ $currency }}</td>
                        <td>{{ number_format((float) $goal->reward_amount, 2) }} {{ $currency }}</td>
                        <td>@include('v1.admin.partials.validity-period', ['model' => $goal])</td>
                        <td>
                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input goal-toggle-active-btn"
                                    type="checkbox"
                                    id="goalToggle{{ $goal->id }}"
                                    data-toggle-url="{{ route('v1.admin.goals.toggle-active', $goal) }}"
                                    @checked($goal->is_active)
                                >
                            </div>
                        </td>
                        <td class="text-end">
                            @include('v1.admin.partials.table-actions', [
                                'showUrl' => route('v1.admin.goals.show', $goal),
                                'editUrl' => route('v1.admin.goals.edit', $goal),
                                'destroyUrl' => route('v1.admin.goals.destroy', $goal),
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 px-3 pb-3">{{ $goals->links() }}</div>
@else
    @include('v1.admin.partials.table-empty', [
        'icon' => 'bullseye',
        'message' => __('messages.No goals.'),
        'createUrl' => route('v1.admin.goals.create'),
        'createLabel' => __('messages.New goal'),
    ])
@endif
