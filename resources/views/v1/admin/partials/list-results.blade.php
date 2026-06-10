{{-- Wrap table partial: @include('v1.admin.partials.list-results', ['partial' => 'v1.admin.categories.partials.table', 'data' => ['categories' => $categories]]) --}}
<div class="card">
    <div class="card-body p-0">
        @include($partial, $data)
    </div>
</div>
