<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Mail\SupportTicketConfirmationMail;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class InvestorSupportController extends Controller
{
    public function index(Request $request)
    {
        $account = $request->user('investor');
        
        $tickets = SupportTicket::where('account_id', $account->id)
            ->where('deleted', false)
            ->with(['project', 'replies'])
            ->orderByDesc('created_on')
            ->get();

        return response()->json($tickets);
    }

    public function show($ticketId)
    {
        $account = request()->user('investor');
        
        $ticket = SupportTicket::where('ticket_id', $ticketId)
            ->where('account_id', $account->id)
            ->where('deleted', false)
            ->with(['project', 'replies.account'])
            ->firstOrFail();

        return response()->json($ticket);
    }

    public function store(Request $request, $projectId = null)
    {
        $account = $request->user('investor');

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'project_id' => 'nullable|exists:legacy.projects,project_id',
        ]);

        // Find project by project_id if provided
        $project = null;
        if ($validated['project_id'] ?? $projectId) {
            $projectIdToUse = $validated['project_id'] ?? $projectId;
            $project = Project::where('project_id', $projectIdToUse)->first();
        }

        $ticket = SupportTicket::create([
            'project_id' => $project ? $project->id : null,
            'account_id' => $account->id,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'open',
            'created_on' => now(),
            'updated_on' => now(),
        ]);

        // Create initial reply from the user
        SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'account_id' => $account->id,
            'message' => $validated['message'],
            'is_from_support' => false,
            'is_system' => false,
            'created_on' => now(),
        ]);

        // Create system reply (auto-confirmation)
        $systemMessage = "Thank you for contacting us. We've received your support request (Ticket ID: {$ticket->ticket_id}) and our team will get back to you as soon as possible. You'll receive email updates when we respond.";
        
        SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'account_id' => null,
            'message' => $systemMessage,
            'is_from_support' => false,
            'is_system' => true,
            'created_on' => now(),
        ]);

        // Send confirmation email
        try {
            Mail::to($account->email)->send(
                new SupportTicketConfirmationMail($account, $ticket)
            );
        } catch (\Exception $e) {
            Log::error('Failed to send support ticket confirmation email: ' . $e->getMessage());
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'ticket' => $ticket->load('replies'),
                'message' => "Support ticket created successfully! Your ticket ID is: {$ticket->ticket_id}"
            ]);
        }

        return back()->with('status', "Support ticket created successfully! Your ticket ID is: {$ticket->ticket_id}");
    }

    public function reply(Request $request, $ticketId)
    {
        $account = $request->user('investor');
        
        $ticket = SupportTicket::where('ticket_id', $ticketId)
            ->where('account_id', $account->id)
            ->where('deleted', false)
            ->firstOrFail();

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $reply = SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'account_id' => $account->id,
            'message' => $validated['message'],
            'is_from_support' => false,
            'is_system' => false,
            'created_on' => now(),
        ]);

        // Update ticket status
        if ($ticket->status === 'closed') {
            $ticket->status = 'open';
            $ticket->updated_on = now();
            $ticket->save();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'reply' => $reply,
            ]);
        }

        return back()->with('status', 'Reply sent successfully!');
    }
}


