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
        try {
            // Use asset() since storage symlink exists
            // file_path format: updates/499/filename.jpg
            if (empty($this->file_path) || !is_string($this->file_path)) {
                return '';
            }
            $filePath = trim($this->file_path);
            if ($filePath === '') {
                return '';
            }
            return asset('storage/' . $filePath);
        } catch (\Exception $e) {
            return '';
        } catch (\Error $e) {
            return '';
        }
    }

    public function getThumbnailUrlAttribute(): string
    {
        // Only create thumbnails for images - use file_type_category to be more reliable
        try {
            $fileTypeCategory = $this->file_type_category;
            if ($fileTypeCategory !== 'image') {
                return $this->url;
            }
        } catch (\Exception $e) {
            return $this->url;
        } catch (\Error $e) {
            return $this->url;
        }
        
        // Return resized version if it exists, otherwise original
        if (empty($this->file_path) || !is_string($this->file_path)) {
            return $this->url;
        }
        
        $filePath = trim($this->file_path);
        if ($filePath === '') {
            return $this->url;
        }
        
        try {
            // Use @ to suppress warnings and check result carefully
            $pathInfo = @pathinfo($filePath);
            
            // Ensure pathinfo returned a valid array
            if (!is_array($pathInfo)) {
                return $this->url;
            }
            
            // Check for required keys before accessing - use array_key_exists for safety
            if (!array_key_exists('dirname', $pathInfo) || !array_key_exists('basename', $pathInfo)) {
                return $this->url;
            }
            
            // Ensure values are strings and not empty
            $dirname = isset($pathInfo['dirname']) && is_string($pathInfo['dirname']) ? $pathInfo['dirname'] : '';
            $basename = isset($pathInfo['basename']) && is_string($pathInfo['basename']) ? $pathInfo['basename'] : '';
            
            if ($dirname === '' || $basename === '') {
                return $this->url;
            }
            
            $thumbnailPath = $dirname . '/thumb_' . $basename;
            
            // Check if thumbnail exists using Storage
            if (Storage::disk('public')->exists($thumbnailPath)) {
                return asset('storage/' . $thumbnailPath);
            }
        } catch (\Exception $e) {
            // If anything goes wrong, fall back to original URL
        } catch (\Error $e) {
            // Also catch PHP errors
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
        
        // Safely extract extension from file_path
        if (!empty($this->file_path) && is_string($this->file_path)) {
            $filePath = trim($this->file_path);
            if ($filePath !== '') {
                try {
                    // Use PATHINFO_EXTENSION flag directly to get just the extension
                    $ext = @pathinfo($filePath, PATHINFO_EXTENSION);
                    if (is_string($ext) && $ext !== '') {
                        $extension = strtolower($ext);
                    }
                } catch (\Exception $e) {
                    // Silently fail and use empty extension
                    $extension = '';
                } catch (\Error $e) {
                    // Also catch PHP errors
                    $extension = '';
                }
            }
        }
        
        // Ensure extension is a string before using in_array
        if (!is_string($extension)) {
            $extension = '';
        }
        
        // Check mime type first, then extension
        if (!empty($mimeType) && is_string($mimeType)) {
            if (str_starts_with($mimeType, 'image/')) {
                return 'image';
            }
            if (str_starts_with($mimeType, 'application/pdf')) {
                return 'pdf';
            }
            if (str_contains($mimeType, 'word')) {
                return 'word';
            }
            if (str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet')) {
                return 'excel';
            }
        }
        
        // Check extension if we have one
        if ($extension !== '') {
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true)) {
                return 'image';
            }
            if ($extension === 'pdf') {
                return 'pdf';
            }
            if (in_array($extension, ['doc', 'docx'], true)) {
                return 'word';
            }
            if (in_array($extension, ['xls', 'xlsx'], true)) {
                return 'excel';
            }
            if (in_array($extension, ['txt', 'csv'], true)) {
                return 'text';
            }
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

