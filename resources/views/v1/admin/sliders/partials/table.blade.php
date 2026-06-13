@if ($sliders->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('messages.ID') }}</th>
                    <th>{{ __('messages.Type') }}</th>
                    <th>{{ __('messages.Image') }}</th>
                    <th>{{ __('messages.Created at') }}</th>
                    <th>{{ __('messages.Updated at') }}</th>
                    <th class="text-end" style="width: 120px;">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sliders as $slider)
                    <tr>
                        <td>{{ $slider->id }}</td>
                        <td>
                            <span class="badge text-bg-secondary">{{ \App\Models\Slider::typeLabel($slider->type) }}</span>
                        </td>
                        <td>
                            @if ($slider->image)
                                <img src="{{ asset('storage/'.$slider->image) }}" alt="" class="rounded" height="48" style="object-fit: cover; max-width: 120px;">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $slider->created_at?->format('M d, Y H:i') }}</td>
                        <td class="small text-muted">{{ $slider->updated_at?->format('M d, Y H:i') }}</td>
                        <td class="text-end">
                            @include('v1.admin.partials.table-actions', [
                                'showUrl' => route('v1.admin.sliders.show', $slider),
                                'editUrl' => route('v1.admin.sliders.edit', $slider),
                                'destroyUrl' => route('v1.admin.sliders.destroy', $slider),
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 px-3 pb-3">{{ $sliders->links() }}</div>
@else
    @include('v1.admin.partials.table-empty', [
        'icon' => 'images',
        'message' => __('messages.No sliders.'),
        'createUrl' => route('v1.admin.sliders.create'),
        'createLabel' => __('messages.New slider'),
    ])
@endif
