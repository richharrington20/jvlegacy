<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    protected $connection = 'legacy';
    protected $table = 'support_tickets';
    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'project_id',
        'account_id',
        'subject',
        'message',
        'status',
        'created_on',
        'updated_on',
    ];

    protected $casts = [
        'created_on' => 'datetime',
        'updated_on' => 'datetime',
        'deleted' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_id)) {
                $ticket->ticket_id = 'TKT-' . strtoupper(Str::random(8));
            }
            if (empty($ticket->created_on)) {
                $ticket->created_on = now();
            }
            if (empty($ticket->updated_on)) {
                $ticket->updated_on = now();
            }
        });

        static::updating(function ($ticket) {
            $ticket->updated_on = now();
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function replies()
    {
        return $this->hasMany(SupportTicketReply::class, 'ticket_id')
            ->where('deleted', false)
            ->orderBy('created_on', 'asc');
    }
}


