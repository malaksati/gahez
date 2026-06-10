@if ($items instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $items->hasPages())
    <div class="mt-4 px-3 pb-3">{{ $items->links() }}</div>
@endif
