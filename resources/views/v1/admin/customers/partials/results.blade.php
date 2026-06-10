@include('v1.admin.partials.list-results', [
    'partial' => 'v1.admin.customers.partials.table',
    'data' => ['customers' => $customers],
])
