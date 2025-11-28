<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ProjectInvestorDocument extends Model
{
    protected $connection = 'legacy';
    protected $table = 'project_investor_documents';
    public $timestamps = false;

    protected $fillable = [
        'proposal_id',
        'document_id',
        'name',
        'hash',
        'created_on',
    ];

    protected $casts = [
        'created_on' => 'datetime',
    ];

    public function getUrlAttribute(): string
    {
        $hash = sha1('jaevee');
        $timestamp = Carbon::now()->timestamp;

        return sprintf(
            '%s/document/investor/%so%so%s',
            config('app.sys_url'),
            $hash,
            $timestamp,
            $this->hash
        );
    }
}


