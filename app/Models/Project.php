<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    // Status constants
    const STATUS_NOT_SUBMITTED        = 0;
    const STATUS_PENDING_REVIEW       = 1;
    const STATUS_UNDER_REVIEW         = 2;
    const STATUS_VALIDATED            = 3;
    const STATUS_RECOMMENDED          = 4;
    const STATUS_AIP_ISSUED           = 5;
    const STATUS_AIP_SIGNED           = 6;
    const STATUS_REJECTED             = 7;
    const STATUS_PENDING_SET_UP       = 20;
    const STATUS_PENDING_COMPLIANCE   = 21;
    const STATUS_PENDING_EQUITY       = 22;
    const STATUS_PENDING_PURCHASE     = 23;
    const STATUS_PENDING_CONSTRUCTION = 24;
    const STATUS_UNDER_CONSTRUCTION   = 25;
    const STATUS_PENDING_SALE         = 26;
    const STATUS_PENDING_REMORTGAGE   = 27;
    const STATUS_PENDING_LET          = 28;
    const STATUS_ON_MARKET            = 29;
    const STATUS_SOLD_STC             = 30;
    const STATUS_SOLD                 = 31;
    const STATUS_LET                  = 32;
    const STATUS_REMORTGAGED          = 33;
    const STATUS_EXTERNAL             = 90;
    const STATUS_CANCELLED            = 99;

    protected $connection = 'legacy';
    protected $table = 'projects';
    public $timestamps = false;

    protected $primaryKey = 'project_id'; // <- Important
    public $incrementing = false; // if it's a string or non-auto ID

    protected $casts = [
        'created_on' => 'datetime',
        'updated_on' => 'datetime',
        'submitted_on' => 'datetime',
        'under_review_on' => 'datetime',
        'validated_on' => 'datetime',
        'recommended_on' => 'datetime',
        'aip_issued_on' => 'datetime',
        'aip_signed_on' => 'datetime',
        'set_up_completed_on' => 'datetime',
        'compliant_on' => 'datetime',
        'launched_on' => 'datetime',
        'completed_on' => 'datetime',
    ];

    public function updates()
    {
        return $this->hasMany(\App\Models\Update::class, 'project_id', 'project_id');
    }

    public function investorDocuments()
    {
        return $this->hasMany(ProjectInvestorDocument::class, 'proposal_id', 'id')
            ->where('deleted', 0)
            ->orderBy('document_id');
    }

    public function property()
    {
        return $this->hasOne(Property::class, 'proposal_id', 'id');
    }

    public function getExpectedPayoutDateAttribute()
    {
        $term = optional($this->property)->investment_turnaround_time;

        if (!$term) {
            return null;
        }

        $start = optional($this->property)->purchase_completion_date
            ?? $this->launched_on
            ?? $this->set_up_completed_on
            ?? $this->created_on;

        if (!$start) {
            return null;
        }

        return $start->copy()->addMonths((int) $term);
    }
}
