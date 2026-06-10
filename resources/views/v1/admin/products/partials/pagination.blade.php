@if ($products->hasPages())
    <div class="mt-4 px-3 pb-3">
        {{ $products->links() }}
    </div>
@endif
