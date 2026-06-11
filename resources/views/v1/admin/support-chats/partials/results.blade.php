@include('v1.admin.partials.list-results', [
    'partial' => 'v1.admin.support-chats.partials.table',
    'data' => ['supports' => $supports],
])
