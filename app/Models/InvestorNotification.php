<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestorNotification extends Model
{
    protected $connection = 'legacy';
    protected $table = 'investor_notifications';

    protected $fillable = [
        'account_id',
        'project_id',
        'type',
        'message',
        'link',
        'source_type',
        'source_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}


