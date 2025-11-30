<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Update extends Model
{
    protected $connection = 'legacy';
    protected $table = 'project_log';

    // Disable Timestamps:
    public $timestamps = false;

    protected $guarded = [];
    
    protected $fillable = [
        'project_id',
        'category',
        'comment',
        'sent_on',
        'sent_by',
        'sent',
        'deleted',
        'image_path', // Store path to uploaded image
    ];

    // Casts (adjust types to your actual schema)
    protected $casts = [
        'category' => 'integer',
        'sent_on' => 'datetime',
        'comment' => 'string',
        'deleted' => 'integer',
    ];

    // Only return records with category = 3
//    protected static function booted(): void
//    {
//        static::addGlobalScope('updatesOnly', function ($query) {
//            $query->where('category', 3);
//        });
//    }

    // Optional: if you want to reference the associated project
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function images()
    {
        return $this->hasMany(UpdateImage::class, 'update_id')
            ->where('deleted', false)
            ->orderBy('display_order');
    }

    // Optional: scope for recent updates
    public function scopeRecent($query, $limit = 5)
    {
        return $query->orderByDesc('sent_on')->limit($limit);
    }

    // Scope to exclude deleted records
    public function scopeNotDeleted($query)
    {
        return $query->where('deleted', 0);
    }


    public function getCommentAttribute($value)
    {
        return is_resource($value) ? stream_get_contents($value) : $value;
    }

    public function getCommentPreviewAttribute()
    {
        $text = strip_tags($this->comment ?? '');
        $words = preg_split('/\s+/', $text, 21);
        if (count($words) > 20) {
            array_pop($words);
            $text = implode(' ', $words) . '...';
        }
        return $text;
    }
}

