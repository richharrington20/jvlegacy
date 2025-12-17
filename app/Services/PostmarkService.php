<?php

namespace App\Services;

use App\Models\EmailLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PostmarkService
{
    protected string $apiToken;
    protected string $baseUrl = 'https://api.postmarkapp.com';

    public function __construct()
    {
        $this->apiToken = config('services.postmark.token');
    }

    /**
     * Get delivery status for a message from Postmark
     */
    public function getMessageStatus(string $messageId): ?array
    {
        if (!$this->apiToken) {
            Log::warning('Postmark API token not configured');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'X-Postmark-Server-Token' => $this->apiToken,
            ])->get("{$this->baseUrl}/messages/outbound/{$messageId}/details");

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("Failed to fetch Postmark message status: {$response->body()}");
            return null;
        } catch (\Exception $e) {
            Log::error("Exception fetching Postmark message status: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get delivery status for multiple messages
     */
    public function getBulkMessageStatus(array $messageIds): array
    {
        $results = [];
        foreach ($messageIds as $messageId) {
            $results[$messageId] = $this->getMessageStatus($messageId);
        }
        return $results;
    }

    /**
     * Update email log with Postmark delivery status
     */
    public function updateEmailLogStatus(EmailLog $emailLog): bool
    {
        if (!$emailLog->postmark_message_id) {
            return false;
        }

        $status = $this->getMessageStatus($emailLog->postmark_message_id);
        
        if (!$status) {
            return false;
        }

        // Update status based on Postmark response
        $emailLog->postmark_response = $status;
        
        // Map Postmark status to our status
        if (isset($status['MessageEvents'])) {
            $events = $status['MessageEvents'];
            
            // Check for delivery
            $delivered = collect($events)->firstWhere('Type', 'Delivery');
            if ($delivered) {
                $emailLog->status = 'delivered';
                $emailLog->delivered_at = isset($delivered['ReceivedAt']) 
                    ? \Carbon\Carbon::parse($delivered['ReceivedAt']) 
                    : now();
            }
            
            // Check for bounces
            $bounced = collect($events)->firstWhere('Type', 'Bounce');
            if ($bounced) {
                $emailLog->status = 'bounced';
                $emailLog->bounced_at = isset($bounced['ReceivedAt']) 
                    ? \Carbon\Carbon::parse($bounced['ReceivedAt']) 
                    : now();
            }
            
            // Check for spam complaints
            $spam = collect($events)->firstWhere('Type', 'SpamComplaint');
            if ($spam) {
                $emailLog->status = 'spam_complaint';
            }
            
            // Track opens and clicks
            $opens = collect($events)->where('Type', 'Open');
            $emailLog->open_count = $opens->count();
            if ($opens->count() > 0) {
                $firstOpen = $opens->first();
                if (!$emailLog->opened_at && isset($firstOpen['ReceivedAt'])) {
                    $emailLog->opened_at = \Carbon\Carbon::parse($firstOpen['ReceivedAt']);
                }
            }
            
            $clicks = collect($events)->where('Type', 'Click');
            $emailLog->click_count = $clicks->count();
            if ($clicks->count() > 0) {
                $firstClick = $clicks->first();
                if (!$emailLog->clicked_at && isset($firstClick['ReceivedAt'])) {
                    $emailLog->clicked_at = \Carbon\Carbon::parse($firstClick['ReceivedAt']);
                }
            }
        }
        
        $emailLog->save();
        return true;
    }

    /**
     * Resend an email via Postmark
     */
    public function resendEmail(EmailLog $emailLog): array
    {
        // This would need to reconstruct the original email
        // For now, return structure for the controller to handle
        return [
            'success' => false,
            'message' => 'Resend functionality requires original email reconstruction',
        ];
    }
}

