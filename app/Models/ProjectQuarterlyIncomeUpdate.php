<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectQuarterlyIncomeUpdate extends Model
{
    protected $connection = 'legacy';
    protected $table = 'project_quarterly_income_updates';
    public $timestamps = false;

    protected $casts = [
        'due_on' => 'date',
        'submitted_on' => 'datetime',
    ];
}


