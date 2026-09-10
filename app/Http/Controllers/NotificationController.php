<?php

namespace App\Http\Controllers;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $title = 'Notifications';
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->paginate(15);
        $totalNotificationCount = $user->notifications()->count();
        $unreadNotificationCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact(
            'title',
            'notifications',
            'totalNotificationCount',
            'unreadNotificationCount'
        ));
    }

    public function markAsRead(DatabaseNotification $notification): RedirectResponse
    {
        abort_unless(
            $notification->notifiable_type === config('auth.providers.users.model')
                && (int) $notification->notifiable_id === (int) Auth::id(),
            404
        );

        $notification->markAsRead();

        return redirect()->route('notifications.index');
    }

    public function summary()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $notifications = $user->notifications()->latest()->limit(5)->get();

        return response()->json([
            'total' => $user->notifications()->count(),
            'unread' => $user->unreadNotifications()->count(),
            'items' => $notifications->map(function (DatabaseNotification $notification) {
                $data = $notification->data ?? [];
                $status = $data['status'] ?? 'Alert';
                $device = $data['device'] ?? 'Unknown device';
                $sensor = $data['sensor'] ?? 'Unknown sensor';
                $message = $data['message'] ?? '';

                return [
                    'id' => $notification->id,
                    'status' => $status,
                    'device' => $device,
                    'sensor' => $sensor,
                    'message' => $message,
                    'read_at' => $notification->read_at,
                    'created_at_human' => $notification->created_at->diffForHumans(),
                    'read_url' => route('notifications.read', $notification),
                    'read_icon' => $notification->read_at ? 'bx-envelope-open' : 'bx-error-circle',
                    'read_class' => $notification->read_at ? '' : 'notification-unread',
                    'color_class' => strtolower((string) $status) === 'up' ? 'success' : 'danger',
                ];
            })->values(),
        ]);
    }

    public function markAllAsRead(): RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back();
    }

    public function deleteAll(): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->notifications()->delete();

        return redirect()->route('notifications.index');
    }
}
