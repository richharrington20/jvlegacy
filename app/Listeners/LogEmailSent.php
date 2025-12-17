<?php

namespace App\Listeners;

use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;

class LogEmailSent
{
    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        try {
            $message = $event->message;
            $mailable = $event->data;
            
            // Extract recipient
            $recipient = $this->extractRecipient($message);
            if (!$recipient) {
                return;
            }

            // Extract subject
            $subject = $message->getSubject() ?? 'No Subject';

            // Determine email type from mailable class
            $emailType = $this->determineEmailType($mailable);

            // Extract Postmark message ID from response if available
            $postmarkMessageId = null;
            try {
                $headers = $event->sent->getOriginalMessage()->getHeaders();
                $pmHeader = $headers->get('X-PM-Message-Id');
                if ($pmHeader !== null) {
                    $postmarkMessageId = $pmHeader->getValue();
                }
            } catch (\Exception $e) {
                // Postmark message ID not available, continue without it
            }

            // Get HTML and text content
            $htmlContent = null;
            $textContent = null;
            
            foreach ($message->getChildren() as $part) {
                if ($part->getContentType() === 'text/html') {
                    $htmlContent = $part->getBody();
                } elseif ($part->getContentType() === 'text/plain') {
                    $textContent = $part->getBody();
                }
            }

            // Extract metadata from mailable
            $metadata = $this->extractMetadata($mailable);

            // Create email log entry
            EmailLog::create([
                'email_type' => $emailType,
                'recipient_email' => $recipient['email'],
                'recipient_name' => $recipient['name'] ?? null,
                'recipient_account_id' => $metadata['account_id'] ?? null,
                'subject' => $subject,
                'html_content' => $htmlContent,
                'text_content' => $textContent,
                'status' => 'sent',
                'sent_at' => now(),
                'postmark_message_id' => $postmarkMessageId,
                'project_id' => $metadata['project_id'] ?? null,
                'update_id' => $metadata['update_id'] ?? null,
                'sent_by' => auth('investor')->id() ?? null,
                'metadata' => $metadata,
            ]);

        } catch (\Exception $e) {
            // Don't fail email sending if logging fails
            Log::error('Failed to log email: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    protected function extractRecipient($message): ?array
    {
        $to = $message->getTo();
        if (empty($to)) {
            return null;
        }

        $email = array_key_first($to);
        $name = $to[$email] ?? null;

        return [
            'email' => $email,
            'name' => $name,
        ];
    }

    protected function determineEmailType($mailable): string
    {
        $className = get_class($mailable);
        
        $typeMap = [
            'App\Mail\ProjectUpdateMail' => 'project_update',
            'App\Mail\ProjectDocumentsMail' => 'project_documents',
            'App\Mail\WelcomeInvestorMail' => 'welcome',
            'App\Mail\AccountShareNotificationMail' => 'account_share',
            'App\Mail\PasswordResetMail' => 'password_reset',
            'App\Mail\SupportTicketConfirmationMail' => 'support_ticket',
        ];

        return $typeMap[$className] ?? 'unknown';
    }

    protected function extractMetadata($mailable): array
    {
        $metadata = [];

        // Extract account ID if available
        if (isset($mailable->account) && isset($mailable->account->id)) {
            $metadata['account_id'] = $mailable->account->id;
        }

        // Extract project ID if available
        if (isset($mailable->project)) {
            if (isset($mailable->project->project_id)) {
                $metadata['project_id'] = $mailable->project->project_id;
            } elseif (isset($mailable->project->id)) {
                $metadata['project_id'] = $mailable->project->id;
            }
        }

        // Extract update ID if available
        if (isset($mailable->update) && isset($mailable->update->id)) {
            $metadata['update_id'] = $mailable->update->id;
        }

        return $metadata;
    }
}

