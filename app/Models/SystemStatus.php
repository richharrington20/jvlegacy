<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemStatus extends Model
{
    protected $connection = 'legacy';
    protected $table = 'system_status';
    public $timestamps = false;

    protected $fillable = [
        'title',
        'message',
        'status_type',
        'is_active',
        'show_on_login',
        'created_by',
        'created_on',
        'updated_on',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_on_login' => 'boolean',
        'created_on' => 'datetime',
        'updated_on' => 'datetime',
        'deleted' => 'boolean',
    ];

    const TYPE_INFO = 'info';
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';
    const TYPE_MAINTENANCE = 'maintenance';

    const TYPE_MAP = [
        self::TYPE_INFO => 'Info',
        self::TYPE_SUCCESS => 'Success',
        self::TYPE_WARNING => 'Warning',
        self::TYPE_ERROR => 'Error',
        self::TYPE_MAINTENANCE => 'Maintenance',
    ];

    public function creator()
    {
        return $this->belongsTo(Account::class, 'created_by');
    }

    public function updates()
    {
        return $this->hasMany(\App\Models\SystemStatusUpdate::class, 'status_id')
            ->where('deleted', false)
            ->orderByDesc('created_on');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('deleted', false);
    }

    public function scopeForLogin($query)
    {
        return $query->where('show_on_login', true)->where('is_active', true)->where('deleted', false);
    }
}

