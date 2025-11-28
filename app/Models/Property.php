<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $connection = 'legacy';
    protected $table = 'properties';
    public $timestamps = false;

    protected $casts = [
        'purchase_completion_date' => 'date',
        'purchase_exchange_date' => 'date',
    ];
}


