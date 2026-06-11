@extends('layouts.app')

@php
    use App\Models\Category;
    use App\Models\Product;

    $page = 'offers';
    $locale = app()->getLocale();
    $name = $offer->getTranslation('name', $locale, false) ?: $offer->getTranslation('name', 'en');
    $badges = view('v1.admin.partials.active-badge', ['active' => $offer->is_active])->render();

    $offerableLabel = '—';
    $offerableLink = null;
    if ($offer->offerable) {
        if ($offer->offerable_type === Category::class) {
            $offerableLabel = $offer->offerable->getTranslation('name', $locale, false) ?: $offer->offerable->getTranslation('name', 'en');
            $offerableLink = route('v1.admin.categories.show', $offer->offerable);
        } elseif ($offer->offerable_type === Product::class) {
            $offerableLabel = $offer->offerable->getTranslation('name', $locale, false) ?: $offer->offerable->getTranslation('name', 'en');
            $offerableLink = route('v1.admin.products.show', $offer->offerable);
        }
    }
@endphp

@section('title', $name)
@section('heading', __('messages.Offer details'))

@section('content')
    @include('v1.admin.partials.show-header', [
        'indexRoute' => 'v1.admin.offers.index',
        'indexLabel' => __('messages.Offers'),
        'title' => $name,
        'badges' => $badges,
        'editRoute' => route('v1.admin.offers.edit', $offer),
        'editLabel' => __('messages.Edit offer'),
        'destroyRoute' => route('v1.admin.offers.destroy', $offer),
    ])

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ __('messages.Details') }}</h5>
                    <div class="row g-3">
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Name').' ('.__('messages.English').')', 'value' => e($offer->getTranslation('name', 'en') ?: '—')])
                        @include('v1.admin.partials.show-field', ['label' => __('messages.Name').' ('.__('messages.Arabic').')', 'value' => e($offer->getTranslation('name', 'ar') ?: '—'), 'dir' => 'rtl'])
                        <div class="col-md-6">
                            <small class="text-muted d-block">{{ __('messages.Discount type') }}</small>
                            <p class="mb-0">@include('v1.admin.partials.discount-type-badge', ['type' => $offer->type])</p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">{{ __('messages.Discount value') }}</small>
                            <p class="mb-0">@include('v1.admin.partials.discount-value', ['type' => $offer->type, 'amount' => $offer->value, 'offer' => $offer])</p>
                        </div>
                        @if ($offer->min_cart_amount)
                            @include('v1.admin.partials.show-field', [
                                'label' => __('messages.Minimum cart amount'),
                                'value' => format_local_number((float) $offer->min_cart_amount, 2).' '.display_currency(),
                            ])
                        @endif
                        @if ($offer->max_discounted_quantity)
                            @include('v1.admin.partials.show-field', [
                                'label' => __('messages.Max discounted quantity'),
                                'value' => (string) $offer->max_discounted_quantity,
                            ])
                        @endif
                        @if ($offer->ends_when_out_of_stock)
                            @include('v1.admin.partials.show-field', [
                                'label' => __('messages.End offer when out of stock'),
                                'value' => __('messages.Yes'),
                            ])
                        @endif
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Applies to'),
                            'value' => $offer->offerable_type === Category::class
                                ? '<span class="badge bg-secondary">'.__('messages.Category').'</span>'
                                : ($offer->offerable_type === Product::class
                                    ? '<span class="badge bg-secondary">'.__('messages.Product').'</span>'
                                    : '—'),
                        ])
                        @include('v1.admin.partials.show-field', [
                            'label' => __('messages.Name'),
                            'value' => $offerableLink
                                ? '<a href="'.e($offerableLink).'">'.e($offerableLabel).'</a>'
                                : e($offerableLabel),
                        ])
                        @if ($offer->type === 'threshold_gift' && $offer->rewardProducts->isNotEmpty())
                            <div class="col-12">
                                <small class="text-muted d-block">{{ __('messages.Gift product choices') }}</small>
                                <ul class="mb-0">
                                    @foreach ($offer->rewardProducts as $reward)
                                        <li>
                                            @if ($reward->product)
                                                <a href="{{ route('v1.admin.products.show', $reward->product) }}">
                                                    {{ $reward->product->getTranslation('name', $locale, false) ?: $reward->product->getTranslation('name', 'en') }}
                                                </a>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <small class="text-muted d-block">{{ __('messages.Validity') }}</small>
                            <p class="mb-0">@include('v1.admin.partials.validity-period', ['model' => $offer])</p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">{{ __('messages.Status') }}</small>
                            <p class="mb-0">@include('v1.admin.partials.active-badge', ['active' => $offer->is_active])</p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">{{ __('messages.Show countdown on storefront') }}</small>
                            <p class="mb-0">
                                @if ($offer->shouldShowCountdown())
                                    <span class="badge bg-info">{{ __('messages.Yes') }}</span>
                                @elseif ($offer->show_countdown && ! $offer->end_date)
                                    <span class="badge bg-warning text-dark">{{ __('messages.Needs end date') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('messages.No') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            @include('v1.admin.partials.show-timestamps', ['model' => $offer])
        </div>
    </div>
@endsection
