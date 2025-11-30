<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountShare extends Model
{
    protected $connection = 'legacy';
    protected $table = 'account_shares';
    public $timestamps = false;

    protected $fillable = [
        'primary_account_id',
        'shared_account_id',
        'status',
        'invited_by',
        'invited_on',
        'accepted_on',
        'revoked_on',
        'created_on',
        'updated_on',
        'deleted',
    ];

    protected $casts = [
        'invited_on' => 'datetime',
        'accepted_on' => 'datetime',
        'revoked_on' => 'datetime',
        'created_on' => 'datetime',
        'updated_on' => 'datetime',
        'deleted' => 'boolean',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_REVOKED = 'revoked';

    public function primaryAccount()
    {
        return $this->belongsTo(Account::class, 'primary_account_id');
    }

    public function sharedAccount()
    {
        return $this->belongsTo(Account::class, 'shared_account_id');
    }

    public function inviter()
    {
        return $this->belongsTo(Account::class, 'invited_by');
    }
}

