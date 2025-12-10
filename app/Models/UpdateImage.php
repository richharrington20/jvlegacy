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
        'file_type',
        'mime_type',
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
        if (empty($this->file_path)) {
            return '';
        }
        return asset('storage/' . $this->file_path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        // Only create thumbnails for images
        if ($this->file_type !== 'image') {
            return $this->url;
        }
        
        // Return resized version if it exists, otherwise original
        if (empty($this->file_path)) {
            return $this->url;
        }
        
        $pathInfo = pathinfo($this->file_path);
        if (!isset($pathInfo['dirname']) || !isset($pathInfo['basename'])) {
            return $this->url;
        }
        
        $thumbnailPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
        
        // Check if thumbnail exists using Storage
        if (Storage::disk('public')->exists($thumbnailPath)) {
            return asset('storage/' . $thumbnailPath);
        }
        
        // Fallback to original image
        return $this->url;
    }

    /**
     * Get the file type category (image, document, etc.)
     */
    public function getFileTypeCategoryAttribute(): string
    {
        if ($this->file_type) {
            return $this->file_type;
        }
        
        // Determine from mime type or extension
        $mimeType = $this->mime_type ?? '';
        $extension = '';
        
        if (!empty($this->file_path)) {
            $extension = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
        }
        
        if (str_starts_with($mimeType, 'image/') || in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
            return 'image';
        }
        
        if (str_starts_with($mimeType, 'application/pdf') || $extension === 'pdf') {
            return 'pdf';
        }
        
        if (str_contains($mimeType, 'word') || in_array($extension, ['doc', 'docx'])) {
            return 'word';
        }
        
        if (str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet') || in_array($extension, ['xls', 'xlsx'])) {
            return 'excel';
        }
        
        if (in_array($extension, ['txt', 'csv'])) {
            return 'text';
        }
        
        return 'document';
    }

    /**
     * Get icon class for the file type
     */
    public function getIconAttribute(): string
    {
        $category = $this->file_type_category;
        
        return match($category) {
            'image' => 'fas fa-image text-blue-500',
            'pdf' => 'fas fa-file-pdf text-red-500',
            'word' => 'fas fa-file-word text-blue-600',
            'excel' => 'fas fa-file-excel text-green-600',
            'text' => 'fas fa-file-alt text-gray-500',
            default => 'fas fa-file text-gray-400',
        };
    }

    /**
     * Check if file is an image
     */
    public function getIsImageAttribute(): bool
    {
        return $this->file_type_category === 'image';
    }
}

