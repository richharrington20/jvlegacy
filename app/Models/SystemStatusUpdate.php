<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemStatusUpdate extends Model
{
    protected $connection = 'legacy';
    protected $table = 'system_status_updates';
    public $timestamps = false;

    protected $fillable = [
        'status_id',
        'account_id',
        'message',
        'is_fixed',
        'fixed_by',
        'fixed_on',
        'created_on',
        'updated_on',
        'deleted',
    ];

    protected $casts = [
        'is_fixed' => 'boolean',
        'fixed_on' => 'datetime',
        'created_on' => 'datetime',
        'updated_on' => 'datetime',
        'deleted' => 'boolean',
    ];

    public function status()
    {
        return $this->belongsTo(SystemStatus::class, 'status_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function fixedBy()
    {
        return $this->belongsTo(Account::class, 'fixed_by');
    }
}

