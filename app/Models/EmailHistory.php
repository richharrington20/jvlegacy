<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailHistory extends Model
{
    protected $connection = 'legacy';
    protected $table = 'email_history';
    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'email_type',
        'subject',
        'recipient',
        'project_id',
        'related_id',
        'sent_by',
        'sent_at',
        'created_on',
        'deleted',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'created_on' => 'datetime',
        'deleted' => 'boolean',
    ];

    const TYPE_DOCUMENT = 'document';
    const TYPE_PROJECT_UPDATE = 'project_update';
    const TYPE_SUPPORT_TICKET = 'support_ticket';
    const TYPE_SYSTEM_STATUS = 'system_status';
    const TYPE_PAYOUT = 'payout';
    const TYPE_ACCOUNT_SHARE = 'account_share';

    const TYPE_LABELS = [
        self::TYPE_DOCUMENT => 'Document Email',
        self::TYPE_PROJECT_UPDATE => 'Project Update',
        self::TYPE_SUPPORT_TICKET => 'Support Ticket',
        self::TYPE_SYSTEM_STATUS => 'System Status',
        self::TYPE_PAYOUT => 'Payout Notification',
        self::TYPE_ACCOUNT_SHARE => 'Account Sharing',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function sender()
    {
        return $this->belongsTo(Account::class, 'sent_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->email_type] ?? ucfirst(str_replace('_', ' ', $this->email_type));
    }

    public function getIconAttribute(): string
    {
        return match($this->email_type) {
            self::TYPE_DOCUMENT => 'fas fa-file-alt text-blue-500',
            self::TYPE_PROJECT_UPDATE => 'fas fa-bullhorn text-green-500',
            self::TYPE_SUPPORT_TICKET => 'fas fa-headset text-purple-500',
            self::TYPE_SYSTEM_STATUS => 'fas fa-info-circle text-yellow-500',
            self::TYPE_PAYOUT => 'fas fa-pound-sign text-emerald-500',
            self::TYPE_ACCOUNT_SHARE => 'fas fa-share-alt text-indigo-500',
            default => 'fas fa-envelope text-gray-500',
        };
    }
}

