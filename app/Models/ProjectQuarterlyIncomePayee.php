<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectQuarterlyIncomePayee extends Model
{
    protected $connection = 'legacy';
    protected $table = 'project_quarterly_income_payees';
    public $timestamps = false;

    protected $casts = [
        'paid_on' => 'datetime',
    ];

    public function quarterlyUpdate()
    {
        return $this->belongsTo(ProjectQuarterlyIncomeUpdate::class, 'quarterly_update_id');
    }
}


