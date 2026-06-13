{{-- Wrap table partial: @include('v1.admin.partials.list-results', ['partial' => '...', 'data' => [...], 'paginator' => $items]) --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-0">
        @include($partial, $data)
    </div>
    @if (isset($paginator) && $paginator->total() > 0)
        <div class="card-footer bg-white border-top py-3 px-3">
            {{ $paginator->onEachSide(1)->withQueryString()->links() }}
        </div>
    @endif
</div>
