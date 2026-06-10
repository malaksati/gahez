<?php

namespace App\V1\Http\Controllers\Web\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends AdminController
{
    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->limit(5)->get();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $notifications->map(fn (DatabaseNotification $notification) => $this->formatNotification($notification))->values(),
        ]);
    }

    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->paginate(20);

        return view('v1.admin.notifications.index', compact('notifications'));
    }

    public function show(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->where('id', $notification)->firstOrFail();
        $item->markAsRead();

        $url = $item->data['url'] ?? route('v1.admin.notifications.index');

        return redirect()->to($url);
    }

    public function markAllRead(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('messages.All notifications marked as read.'),
                'unread_count' => 0,
            ]);
        }

        return $this->redirectBackWithSuccess(__('messages.All notifications marked as read.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatNotification(DatabaseNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'title' => $notification->data['title'] ?? __('messages.Notification'),
            'message' => $notification->data['message'] ?? __('messages.New notification'),
            'url' => $notification->data['url'] ?? route('v1.admin.notifications.show', $notification->id),
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
            'created_at_human' => $notification->created_at?->diffForHumans(),
        ];
    }
}
