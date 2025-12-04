<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    public function updateRecord()
    {
        return $this->belongsTo(Update::class, 'update_id');
    }

    public function getUrlAttribute(): string
    {
        // Use asset() since storage symlink exists
        // file_path format: updates/499/filename.jpg
        return asset('storage/' . $this->file_path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        // Return resized version if it exists, otherwise original
        $pathInfo = pathinfo($this->file_path);
        $thumbnailPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
        
        // Check if thumbnail exists using Storage
        if (Storage::disk('public')->exists($thumbnailPath)) {
            return asset('storage/' . $thumbnailPath);
        }
        
        // Fallback to original image
        return $this->url;
    }
}

