<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $connection = 'legacy';
    protected $table = 'email_logs';
    
    public $timestamps = true;

    protected $fillable = [
        'message_id',
        'email_type',
        'recipient_email',
        'recipient_name',
        'recipient_account_id',
        'subject',
        'html_content',
        'text_content',
        'status',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'bounced_at',
        'postmark_message_id',
        'postmark_response',
        'error_message',
        'project_id',
        'update_id',
        'sent_by',
        'metadata',
        'open_count',
        'click_count',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'bounced_at' => 'datetime',
        'metadata' => 'array',
        'postmark_response' => 'array',
        'open_count' => 'integer',
        'click_count' => 'integer',
    ];

    // Relationships
    public function recipientAccount()
    {
        return $this->belongsTo(Account::class, 'recipient_account_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function update()
    {
        return $this->belongsTo(Update::class, 'update_id');
    }

    public function sentByUser()
    {
        return $this->belongsTo(Account::class, 'sent_by');
    }

    // Status helpers
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'bounced', 'spam_complaint']);
    }

    public function canResend(): bool
    {
        return in_array($this->status, ['failed', 'bounced', 'spam_complaint']);
    }

    // Scopes
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'bounced', 'spam_complaint']);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('email_type', $type);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('sent_at', '>=', now()->subDays($days));
    }
}

