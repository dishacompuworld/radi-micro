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
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->paginate(15);
        $totalNotificationCount = $user->notifications()->count();
        $unreadNotificationCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact(
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

        return back();
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
