<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\InvestorNotification;
use Illuminate\Http\Request;

class InvestorNotificationController extends Controller
{
    public function markRead(Request $request, InvestorNotification $notification)
    {
        abort_unless($notification->account_id === $request->user('investor')->id, 403);

        $notification->update(['read_at' => now()]);

        return back()->with('status', 'Notification marked as read.');
    }

    public function markAllRead(Request $request)
    {
        InvestorNotification::where('account_id', $request->user('investor')->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('status', 'All notifications marked as read.');
    }
}


