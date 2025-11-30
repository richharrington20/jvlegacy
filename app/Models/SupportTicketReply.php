<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicketReply extends Model
{
    protected $connection = 'legacy';
    protected $table = 'support_ticket_replies';
    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'account_id',
        'message',
        'is_from_support',
        'is_system',
        'created_on',
    ];

    protected $casts = [
        'is_from_support' => 'boolean',
        'is_system' => 'boolean',
        'deleted' => 'boolean',
        'created_on' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}

