<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $connection = 'legacy';
    protected $table = 'support_tickets';

    protected $fillable = [
        'project_id',
        'account_id',
        'subject',
        'message',
        'status',
    ];
}


