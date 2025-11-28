<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class InvestorSupportController extends Controller
{
    public function store(Request $request, $projectId)
    {
        $account = $request->user('investor');

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        SupportTicket::create([
            'project_id' => $projectId,
            'account_id' => $account->id,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ]);

        // TODO: Notify support team via email/Slack when creds available

        return back()->with('status', 'Support request submitted. We\'ll reply via email.');
    }
}


