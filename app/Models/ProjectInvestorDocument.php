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

    /**
     * Get the icon class for this document type
     */
    public function getIconAttribute(): string
    {
        $name = strtolower($this->name ?? '');
        
        // Map document names/types to Font Awesome icons
        if (str_contains($name, 'shareholder')) {
            return 'fas fa-file-contract text-blue-600';
        } elseif (str_contains($name, 'loan')) {
            return 'fas fa-file-signature text-green-600';
        } elseif (str_contains($name, 'certificate') || str_contains($name, 'cert')) {
            return 'fas fa-certificate text-yellow-600';
        } elseif (str_contains($name, 'statement')) {
            return 'fas fa-file-invoice text-purple-600';
        } elseif (str_contains($name, 'agreement')) {
            return 'fas fa-handshake text-indigo-600';
        } else {
            // Default PDF icon
            return 'far fa-file-pdf text-red-600';
        }
    }
}


