@php
    use App\Models\Category;
    use App\Models\Product;

    $locale = app()->getLocale();
@endphp

@if ($offers->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('messages.Name') }}</th>
                    <th>{{ __('messages.Discount type') }}</th>
                    <th>{{ __('messages.Discount value') }}</th>
                    <th>{{ __('messages.Applies to') }}</th>
                    <th>{{ __('messages.Validity') }}</th>
                    <th>{{ __('messages.Status') }}</th>
                    <th>{{ __('messages.Countdown') }}</th>
                    <th class="text-end" style="width: 160px;">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($offers as $offer)
                    <tr>
                        <td>
                            <strong>{{ $offer->getTranslation('name', $locale, false) ?: $offer->getTranslation('name', 'en') }}</strong>
                        </td>
                        <td>@include('v1.admin.partials.discount-type-badge', ['type' => $offer->type])</td>
                        <td>@include('v1.admin.partials.discount-value', ['type' => $offer->type, 'amount' => $offer->value, 'offer' => $offer])</td>
                        <td>
                            @if ($offer->offerable_type === Category::class)
                                <span class="badge bg-secondary">{{ __('messages.Category') }}</span>
                            @elseif ($offer->offerable_type === Product::class)
                                <span class="badge bg-secondary">{{ __('messages.Product') }}</span>
                            @endif
                            @if ($offer->offerable)
                                <div class="small text-muted mt-1">
                                    {{ $offer->offerable->getTranslation('name', $locale, false) ?: $offer->offerable->getTranslation('name', 'en') }}
                                </div>
                            @else
                                <div class="small text-muted mt-1">—</div>
                            @endif
                        </td>
                        <td>@include('v1.admin.partials.validity-period', ['model' => $offer])</td>
                        <td>@include('v1.admin.partials.status-column', ['model' => $offer])</td>
                        <td>
                            @if ($offer->shouldShowCountdown())
                                <span class="badge bg-info">{{ __('messages.Visible') }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @include('v1.admin.partials.table-actions', [
                                'notifyUrl' => route('v1.admin.offers.notify-customers', $offer),
                                'notifyEnabled' => $offer->validityStatus() === 'running',
                                'showUrl' => route('v1.admin.offers.show', $offer),
                                'editUrl' => route('v1.admin.offers.edit', $offer),
                                'destroyUrl' => route('v1.admin.offers.destroy', $offer),
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 px-3 pb-3">{{ $offers->links() }}</div>
@else
    @include('v1.admin.partials.table-empty', [
        'icon' => 'gift',
        'message' => __('messages.No offers.'),
        'createUrl' => route('v1.admin.offers.create'),
        'createLabel' => __('messages.New offer'),
    ])
@endif
