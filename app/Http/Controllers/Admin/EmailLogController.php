<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Services\PostmarkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailLogController extends Controller
{
    protected PostmarkService $postmarkService;

    public function __construct(PostmarkService $postmarkService)
    {
        $this->postmarkService = $postmarkService;
    }

    public function index(Request $request)
    {
        $query = EmailLog::query();

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('email_type')) {
            $query->where('email_type', $request->email_type);
        }

        if ($request->filled('recipient_email')) {
            $query->where('recipient_email', 'like', '%' . $request->recipient_email . '%');
        }

        if ($request->filled('date_from')) {
            $query->where('sent_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('sent_at', '<=', $request->date_to . ' 23:59:59');
        }

        $emailLogs = $query->orderByDesc('sent_at')
            ->with(['recipientAccount', 'project', 'update', 'sentByUser'])
            ->paginate(50);

        // Get unique email types for filter
        $emailTypes = EmailLog::distinct('email_type')
            ->pluck('email_type')
            ->sort()
            ->values();

        return view('admin.email-logs.index', [
            'emailLogs' => $emailLogs,
            'emailTypes' => $emailTypes,
            'filters' => $request->only(['status', 'email_type', 'recipient_email', 'date_from', 'date_to']),
        ]);
    }

    public function show($id)
    {
        $emailLog = EmailLog::with(['recipientAccount', 'project', 'update', 'sentByUser'])
            ->findOrFail($id);

        // Try to fetch latest status from Postmark if we have a message ID
        $postmarkStatus = null;
        if ($emailLog->postmark_message_id) {
            $postmarkStatus = $this->postmarkService->getMessageStatus($emailLog->postmark_message_id);
            if ($postmarkStatus) {
                // Update the log with latest status
                $this->postmarkService->updateEmailLogStatus($emailLog);
                $emailLog->refresh();
            }
        }

        return view('admin.email-logs.show', [
            'emailLog' => $emailLog,
            'postmarkStatus' => $postmarkStatus,
        ]);
    }

    public function updateStatus($id)
    {
        $emailLog = EmailLog::findOrFail($id);

        if (!$emailLog->postmark_message_id) {
            return redirect()->route('admin.email-logs.show', $id)
                ->with('error', 'No Postmark message ID found for this email.');
        }

        $updated = $this->postmarkService->updateEmailLogStatus($emailLog);

        if ($updated) {
            return redirect()->route('admin.email-logs.show', $id)
                ->with('success', 'Email status updated from Postmark.');
        }

        return redirect()->route('admin.email-logs.show', $id)
            ->with('error', 'Failed to update email status from Postmark.');
    }

    public function resend($id)
    {
        $emailLog = EmailLog::findOrFail($id);

        if (!$emailLog->canResend()) {
            return redirect()->route('admin.email-logs.show', $id)
                ->with('error', 'This email cannot be resent. Only failed, bounced, or spam complaint emails can be resent.');
        }

        // Reconstruct and resend the email based on type
        try {
            $result = $this->resendEmailByType($emailLog);

            if ($result['success']) {
                return redirect()->route('admin.email-logs.show', $id)
                    ->with('success', 'Email resent successfully.');
            }

            return redirect()->route('admin.email-logs.show', $id)
                ->with('error', $result['message'] ?? 'Failed to resend email.');
        } catch (\Exception $e) {
            \Log::error("Failed to resend email log ID {$id}: " . $e->getMessage());
            return redirect()->route('admin.email-logs.show', $id)
                ->with('error', 'An error occurred while resending the email: ' . $e->getMessage());
        }
    }

    protected function resendEmailByType(EmailLog $emailLog): array
    {
        switch ($emailLog->email_type) {
            case 'project_update':
                return $this->resendProjectUpdateEmail($emailLog);
            case 'project_documents':
                return $this->resendProjectDocumentsEmail($emailLog);
            default:
                return [
                    'success' => false,
                    'message' => 'Resend not yet implemented for this email type.',
                ];
        }
    }

    protected function resendProjectUpdateEmail(EmailLog $emailLog): array
    {
        if (!$emailLog->update_id) {
            return ['success' => false, 'message' => 'Update ID not found in email log.'];
        }

        $update = \App\Models\Update::with(['project', 'images'])->find($emailLog->update_id);
        if (!$update) {
            return ['success' => false, 'message' => 'Update not found.'];
        }

        $account = $emailLog->recipientAccount;
        if (!$account) {
            return ['success' => false, 'message' => 'Recipient account not found.'];
        }

        try {
            Mail::mailer('postmark')->to($emailLog->recipient_email)->send(
                new \App\Mail\ProjectUpdateMail($account, $update->project, $update)
            );

            return ['success' => true, 'message' => 'Email resent successfully.'];
        } catch (\Exception $e) {
            \Log::error("Failed to resend project update email: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()];
        }
    }

    protected function resendProjectDocumentsEmail(EmailLog $emailLog): array
    {
        // Similar implementation for project documents
        return ['success' => false, 'message' => 'Resend for project documents not yet implemented.'];
    }

    public function bulkUpdateStatus()
    {
        // Update status for all emails with Postmark message IDs
        $emailLogs = EmailLog::whereNotNull('postmark_message_id')
            ->where('status', '!=', 'delivered')
            ->where('sent_at', '>=', now()->subDays(7)) // Only check recent emails
            ->get();

        $updated = 0;
        foreach ($emailLogs as $emailLog) {
            if ($this->postmarkService->updateEmailLogStatus($emailLog)) {
                $updated++;
            }
        }

        return redirect()->route('admin.email-logs.index')
            ->with('success', "Updated status for {$updated} email(s).");
    }
}

