<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $connection = 'legacy';
    protected $table = 'companies';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'number',
        'email',
        'telephone_number',
        'website',
        'created_on',
        'updated_on',
    ];
}
