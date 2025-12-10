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

    /**
     * Safely extract file extension without using pathinfo array access
     */
    private function getFileExtension(string $filePath): string
    {
        if (empty($filePath)) {
            return '';
        }

        // Find last dot position
        $lastDot = strrpos($filePath, '.');
        if ($lastDot === false) {
            return '';
        }

        // Get extension after last dot
        $extension = substr($filePath, $lastDot + 1);
        if (!is_string($extension) || empty($extension)) {
            return '';
        }

        return strtolower($extension);
    }

    /**
     * Safely get directory and basename without using pathinfo array access
     */
    private function getPathParts(string $filePath): array
    {
        $result = ['dirname' => '', 'basename' => ''];
        
        if (empty($filePath)) {
            return $result;
        }

        // Find last slash position
        $lastSlash = strrpos($filePath, '/');
        
        if ($lastSlash === false) {
            // No directory separator, basename is the whole path
            $result['basename'] = $filePath;
            $result['dirname'] = '.';
        } else {
            $result['dirname'] = substr($filePath, 0, $lastSlash);
            $result['basename'] = substr($filePath, $lastSlash + 1);
        }

        return $result;
    }

    public function getUrlAttribute(): string
    {
        try {
            if (empty($this->file_path) || !is_string($this->file_path)) {
                return '';
            }
            
            $filePath = trim($this->file_path);
            if ($filePath === '') {
                return '';
            }
            
            return asset('storage/' . $filePath);
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function getThumbnailUrlAttribute(): string
    {
        try {
            // Only create thumbnails for images
            $fileTypeCategory = $this->file_type_category;
            if ($fileTypeCategory !== 'image') {
                return $this->url;
            }
        } catch (\Throwable $e) {
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
            // Use our safe path parsing instead of pathinfo
            $pathParts = $this->getPathParts($filePath);
            
            if (empty($pathParts['dirname']) || empty($pathParts['basename'])) {
                return $this->url;
            }
            
            $thumbnailPath = $pathParts['dirname'] . '/thumb_' . $pathParts['basename'];
            
            // Check if thumbnail exists using Storage
            if (Storage::disk('public')->exists($thumbnailPath)) {
                return asset('storage/' . $thumbnailPath);
            }
        } catch (\Throwable $e) {
            // If anything goes wrong, fall back to original URL
        }
        
        // Fallback to original image
        return $this->url;
    }

    /**
     * Get the file type category (image, document, etc.)
     */
    public function getFileTypeCategoryAttribute(): string
    {
        try {
            if ($this->file_type) {
                return $this->file_type;
            }
        } catch (\Throwable $e) {
            // Continue to determine from mime type or extension
        }
        
        // Determine from mime type or extension
        $mimeType = '';
        try {
            $mimeType = $this->mime_type ?? '';
            if (!is_string($mimeType)) {
                $mimeType = '';
            }
        } catch (\Throwable $e) {
            $mimeType = '';
        }
        
        $extension = '';
        
        // Safely extract extension from file_path without using pathinfo
        if (!empty($this->file_path) && is_string($this->file_path)) {
            $filePath = trim($this->file_path);
            if ($filePath !== '') {
                try {
                    $extension = $this->getFileExtension($filePath);
                } catch (\Throwable $e) {
                    $extension = '';
                }
            }
        }
        
        // Ensure extension is a string
        if (!is_string($extension)) {
            $extension = '';
        }
        
        // Check mime type first, then extension
        if (!empty($mimeType)) {
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
        try {
            $category = $this->file_type_category;
            
            return match($category) {
                'image' => 'fas fa-image text-blue-500',
                'pdf' => 'fas fa-file-pdf text-red-500',
                'word' => 'fas fa-file-word text-blue-600',
                'excel' => 'fas fa-file-excel text-green-600',
                'text' => 'fas fa-file-alt text-gray-500',
                default => 'fas fa-file text-gray-400',
            };
        } catch (\Throwable $e) {
            return 'fas fa-file text-gray-400';
        }
    }

    /**
     * Check if file is an image
     */
    public function getIsImageAttribute(): bool
    {
        try {
            return $this->file_type_category === 'image';
        } catch (\Throwable $e) {
            return false;
        }
    }
}
