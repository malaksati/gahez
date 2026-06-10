@php $locale = app()->getLocale(); @endphp

@if ($variantOptions->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('messages.Name') }}</th>
                    <th>{{ __('messages.Variant') }}</th>
                    <th>{{ __('messages.Code') }}</th>
                    <th class="text-end" style="width: 120px;">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($variantOptions as $option)
                    <tr>
                        <td>
                            <strong>{{ $option->getTranslation('name', $locale, false) ?: $option->getTranslation('name', 'en') }}</strong>
                        </td>
                        <td>
                            @if ($option->variant)
                                {{ $option->variant->getTranslation('name', $locale, false) ?: $option->variant->getTranslation('name', 'en') }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><code class="small">{{ $option->code ?: '—' }}</code></td>
                        <td class="text-end">
                            @include('v1.admin.partials.table-actions', [
                                'showUrl' => route('v1.admin.variant-options.show', $option),
                                'editUrl' => route('v1.admin.variant-options.edit', $option),
                                'destroyUrl' => route('v1.admin.variant-options.destroy', $option),
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @include('v1.admin.partials.table-pagination', ['items' => $variantOptions])
@else
    @include('v1.admin.partials.table-empty', [
        'icon' => 'ui-checks',
        'message' => __('messages.No variant options.'),
        'createUrl' => route('v1.admin.variant-options.create'),
        'createLabel' => __('messages.New option'),
    ])
@endif
