@php $locale = app()->getLocale(); @endphp

@if ($variants->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('messages.Name') }}</th>
                    <th>{{ __('messages.Variant options') }}</th>
                    <th>{{ __('messages.Required') }}</th>
                    <th>{{ __('messages.Status') }}</th>
                    <th class="text-end" style="width: 120px;">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($variants as $variant)
                    <tr>
                        <td>
                            <strong>{{ $variant->getTranslation('name', $locale, false) ?: $variant->getTranslation('name', 'en') }}</strong>
                        </td>
                        <td>
                            @if ($variant->options->isNotEmpty())
                                <span class="badge bg-info text-dark">{{ $variant->options->count() }}</span>
                                <small class="text-muted d-block">
                                    {{ $variant->options->take(3)->map(fn ($opt) => $opt->getTranslation('name', $locale, false) ?: $opt->getTranslation('name', 'en'))->implode(', ') }}
                                    @if ($variant->options->count() > 3)
                                        …
                                    @endif
                                </small>
                            @else
                                <span class="text-muted">{{ __('messages.No options') }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($variant->is_required)
                                <span class="badge bg-warning text-dark">{{ __('messages.Required') }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>@include('v1.admin.partials.active-badge', ['active' => $variant->is_active])</td>
                        <td class="text-end">
                            @include('v1.admin.partials.table-actions', [
                                'showUrl' => route('v1.admin.variants.show', $variant),
                                'editUrl' => route('v1.admin.variants.edit', $variant),
                                'destroyUrl' => route('v1.admin.variants.destroy', $variant),
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 px-3 pb-3">{{ $variants->links() }}</div>
@else
    @include('v1.admin.partials.table-empty', [
        'icon' => 'list-ul',
        'message' => __('messages.No variants.'),
        'createUrl' => route('v1.admin.variants.create'),
        'createLabel' => __('messages.New variant'),
    ])
@endif
