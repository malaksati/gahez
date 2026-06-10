@php $locale = app()->getLocale(); @endphp

@if ($brands->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('messages.Name') }}</th>
                    <th class="text-end" style="width: 120px;">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($brands as $brand)
                    <tr>
                        <td>
                            <strong>{{ $brand->getTranslation('name', $locale, false) ?: $brand->getTranslation('name', 'en') }}</strong>
                            <div class="small text-muted" dir="rtl">{{ $brand->getTranslation('name', 'ar') }}</div>
                        </td>
                        <td class="text-end">
                            @include('v1.admin.partials.table-actions', [
                                'showUrl' => route('v1.admin.brands.show', $brand),
                                'editUrl' => route('v1.admin.brands.edit', $brand),
                                'destroyUrl' => route('v1.admin.brands.destroy', $brand),
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 px-3 pb-3">{{ $brands->links() }}</div>
@else
    @include('v1.admin.partials.table-empty', [
        'icon' => 'award',
        'message' => __('messages.No brands.'),
        'createUrl' => route('v1.admin.brands.create'),
        'createLabel' => __('messages.New brand'),
    ])
@endif
