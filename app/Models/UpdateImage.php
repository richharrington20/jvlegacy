<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateImage extends Model
{
    protected $connection = 'legacy';
    protected $table = 'update_images';
    public $timestamps = false;

    protected $fillable = [
        'update_id',
        'file_path',
        'file_name',
        'file_size',
        'description',
        'display_order',
        'created_on',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'file_size' => 'integer',
        'created_on' => 'datetime',
        'deleted' => 'boolean',
    ];

    public function update()
    {
        return $this->belongsTo(Update::class, 'update_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        // Return resized version if it exists, otherwise original
        $pathInfo = pathinfo($this->file_path);
        $thumbnailPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
        
        if (file_exists(storage_path('app/public/' . $thumbnailPath))) {
            return asset('storage/' . $thumbnailPath);
        }
        
        return $this->url;
    }
}

