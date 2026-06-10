@php
    $locale = app()->getLocale();
@endphp

@if ($ratings->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h2 class="h6 mb-0">{{ __('messages.Product ratings') }}</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('messages.Product') }}</th>
                            <th>{{ __('messages.Customer') }}</th>
                            <th>{{ __('messages.Rating') }}</th>
                            <th>{{ __('messages.Comment') }}</th>
                            <th>{{ __('messages.Visible') }}</th>
                            <th>{{ __('messages.Date') }}</th>
                            <th class="text-end">{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ratings as $rating)
                            <tr>
                                <td>{{ $rating->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $rating->product?->getTranslation('name', $locale, false) ?: '—' }}</div>
                                    <small class="text-muted">#{{ $rating->product_id }}</small>
                                </td>
                                <td>
                                    {{ $rating->user?->name ?? '—' }}
                                    <div><small class="text-muted">#{{ $rating->user_id }}</small></div>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">{{ (int) $rating->rating }}/5</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ Str::limit($rating->comment, 80) ?: '—' }}</span>
                                </td>
                                <td>
                                    @if ($rating->is_visible)
                                        <span class="badge bg-success">{{ __('messages.Visible') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('messages.Hidden') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $rating->created_at?->format('Y-m-d H:i') ?? '—' }}</small>
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('v1.admin.product-ratings.toggle-visibility', $rating) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm(@json($rating->is_visible ? __('messages.Confirm hide this rating?') : __('messages.Confirm show this rating?')))">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ ! $rating->is_visible ? 'btn-success' : 'btn-danger' }}">
                                            @if ($rating->is_visible)
                                                <i class="bi bi-eye-slash me-1"></i>{{ __('messages.Hide') }}
                                            @else
                                                <i class="bi bi-eye me-1"></i>{{ __('messages.Show') }}
                                            @endif
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 px-3 pb-3">{{ $ratings->links() }}</div>
        </div>
    </div>
@else
    @include('v1.admin.partials.table-empty', ['icon' => 'star', 'message' => __('messages.No product ratings found.')])
@endif
