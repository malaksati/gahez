@include('v1.admin.partials.list-results', [
    'partial' => 'v1.admin.brands.partials.table',
    'data' => ['brands' => $brands],
    'paginator' => $brands,
])
