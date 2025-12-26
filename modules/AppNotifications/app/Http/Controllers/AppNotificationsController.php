<?php

namespace Modules\AppNotifications\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\AdminNotifications\Facades\Notification;

class AppNotificationsController extends Controller
{
    public function index()
    {
        $notifications = Notification::getLatest(Auth::id(), 20);
        return response()->json(['data' => $notifications]);
    }

    public function markAsRead($id)
    {
        $success = \Notifier::markAsRead(auth()->id(), $id);

        return response()->json([
            'status'  => $success ? 1 : 0,
            'data'    => view('appnotifications::components.notification-items')->render(),
            'count'   => \Notifier::countUnread(auth()->id()),
        ]);
    }

    public function markAllRead()
    {
        \Notifier::markAllAsRead(auth()->id());

        return response()->json([
            'status'  => 1,
            'data'    => view('appnotifications::components.notification-items')->render(),
            'count'   => 0, 
        ]);
    }

    public function archiveAll()
    {
        \Notifier::archiveAll(auth()->id());

        return response()->json([
            'status'  => 1,
            'data'    => view('appnotifications::components.notification-items')->render(),
            'count'   => 0,
        ]);

    }
}