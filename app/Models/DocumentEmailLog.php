<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentEmailLog extends Model
{
    protected $connection = 'legacy';
    protected $table = 'document_email_logs';
    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'account_id',
        'document_id',
        'document_name',
        'recipient',
        'sent_by',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}


