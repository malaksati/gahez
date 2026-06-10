@include('v1.admin.partials.list-results', [
    'partial' => 'v1.admin.branches.partials.table',
    'data' => ['branches' => $branches],
])
