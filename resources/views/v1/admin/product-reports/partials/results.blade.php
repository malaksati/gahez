@include('v1.admin.partials.list-results', [
    'partial' => 'v1.admin.product-reports.partials.table',
    'data' => ['reports' => $reports],
])
