<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Mark one notification belonging to the authenticated user as read.
     */
    public function markAsRead(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        $userNotification = $request->user()
            ->notifications()
            ->whereKey($notification->getKey())
            ->firstOrFail();

        $userNotification->markAsRead();

        return back();
    }
}
