<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Account extends Authenticatable
{
    protected $connection = 'legacy';
    protected $table = 'accounts';
    public $timestamps = false;

    protected $fillable = [
        'person_id',
        'company_id',
        'type_id',
        'client_id',
        'email',
        'password',
        'created_on',
        'updated_on',
        'experience',
        'eula',
        'privacy',
        'status',
        'kyc_status',
        'credit',
        'deleted',
    ];

    public function getAuthIdentifierName()
    {
        return 'email'; // or 'username' depending on your DB
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function investments()
    {
        return $this->hasMany(Investments::class, 'account_id');
    }

    public function type()
    {
        return $this->belongsTo(AccountType::class, 'type_id');
    }

    public function documents()
    {
        return $this->hasMany(AccountDocument::class, 'account_id')
            ->where('deleted', false);
    }

    public function sharedAccounts()
    {
        return $this->hasMany(AccountShare::class, 'primary_account_id')
            ->where('status', AccountShare::STATUS_ACTIVE)
            ->where('deleted', false);
    }

    public function sharedWithMe()
    {
        return $this->hasMany(AccountShare::class, 'shared_account_id')
            ->where('status', AccountShare::STATUS_ACTIVE)
            ->where('deleted', false);
    }


    public function getNameAttribute(): string
    {
        if ($this->person) {
            return $this->person->first_name . ' ' . $this->person->last_name;
        } elseif ($this->company) {
            return $this->company->name;
        }

        return 'Unknown';
    }

    public function getTypeNameAttribute()
    {
        return $this->type->name ?? '—';
    }

    public function getTypeIconAttribute(): string
    {
        if ($this->person) {
            return '<span title="Individual" class="text-blue-500">[Person]</span>';
        } elseif ($this->company) {
            return '<span title="Company" class="text-green-500">[Company]</span>';
        }

        return '<span title="Unknown" class="text-gray-500">[?]</span>';
    }
}
