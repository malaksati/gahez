@php $locale = app()->getLocale(); @endphp

@if ($branches->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('messages.Name') }}</th>
                    <th>{{ __('messages.Address') }}</th>
                    <th>{{ __('messages.Phone') }}</th>
                    <th>{{ __('messages.Location') }}</th>
                    <th>{{ __('messages.Status') }}</th>
                    <th class="text-end" style="width: 120px;">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($branches as $branch)
                    <tr>
                        <td>
                            <strong>{{ $branch->getTranslation('name', $locale, false) ?: $branch->getTranslation('name', 'en') }}</strong>
                        </td>
                        <td class="text-muted small">{{ Str::limit($branch->address, 50) ?: '—' }}</td>
                        <td>{{ $branch->phone ?: '—' }}</td>
                        <td>
                            <a
                            href="https://www.google.com/maps?q={{ $branch->latitude }},{{ $branch->longitude }}"
                            target="_blank"
                            class="text-decoration-none"
                            ><i class="bi bi-geo-alt me-1"></i>View Map </a>
                        </td>
                        <td>@include('v1.admin.partials.active-badge', ['active' => $branch->is_active])</td>
                        <td class="text-end">
                            @include('v1.admin.partials.table-actions', [
                                'showUrl' => route('v1.admin.branches.show', $branch),
                                'editUrl' => route('v1.admin.branches.edit', $branch),
                                'destroyUrl' => route('v1.admin.branches.destroy', $branch),
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @include('v1.admin.partials.table-pagination', ['items' => $branches])
@else
    @include('v1.admin.partials.table-empty', [
        'icon' => 'geo-alt',
        'message' => __('messages.No branches.'),
        'createUrl' => route('v1.admin.branches.create'),
        'createLabel' => __('messages.New branch'),
    ])
@endif
