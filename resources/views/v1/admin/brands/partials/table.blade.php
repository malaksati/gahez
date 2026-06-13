@if ($brands->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('messages.Image') }}</th>
                    <th>{{ __('messages.Name') }}</th>
                    <th>{{ __('messages.Products') }}</th>
                    <th class="text-end" style="width: 120px;">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($brands as $brand)
                    @php
                        $displayName = app()->getLocale() === 'ar'
                            ? ($brand->getTranslation('name', 'ar', false) ?: $brand->getTranslation('name', 'en'))
                            : ($brand->getTranslation('name', 'en', false) ?: $brand->getTranslation('name', 'ar'));
                    @endphp
                    <tr>
                        <td>
                            <img
                                src="{{ $brand->image }}"
                                alt="{{ $displayName }}"
                                class="img-thumbnail"
                                style="width: 50px; height: 50px; object-fit: cover;"
                            >
                        </td>
                        <td>
                            @include('v1.admin.partials.translatable-name-stack', ['model' => $brand])
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $brand->products_count }}</span>
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
@else
    @include('v1.admin.partials.table-empty', [
        'icon' => 'award',
        'message' => __('messages.No brands.'),
        'createUrl' => route('v1.admin.brands.create'),
        'createLabel' => __('messages.New brand'),
    ])
@endif
