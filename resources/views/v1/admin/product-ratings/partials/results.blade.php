@include('v1.admin.partials.list-results', [
    'partial' => 'v1.admin.product-ratings.partials.table',
    'data' => ['ratings' => $ratings],
])
