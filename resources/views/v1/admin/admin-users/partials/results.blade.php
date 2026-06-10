@include('v1.admin.partials.list-results', [
    'partial' => 'v1.admin.admin-users.partials.table',
    'data' => ['admins' => $admins],
])
