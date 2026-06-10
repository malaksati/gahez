@include('v1.admin.partials.list-results', [
    'partial' => 'v1.admin.tickets.partials.table',
    'data' => ['tickets' => $tickets],
])
