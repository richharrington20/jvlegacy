<?php

namespace App\Http\Controllers;

use App\Models\Update;
use Illuminate\Http\Request;

class UpdateShowController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $update = Update::on('legacy')->with('images')->findOrFail($id);
        // Optionally, add authorization logic here
        return response()->json([
            'id' => $update->id,
            'project_id' => $update->project_id,
            'sent_on' => $update->sent_on ? $update->sent_on->format('d M Y H:i') : null,
            'comment' => $update->comment,
            'category' => $update->category,
            'images' => $update->images->filter(function ($image) {
                // Only return actual images - filter by file_type or check if it's an image
                try {
                    $fileType = $image->file_type ?? '';
                    // If file_type is set and it's not 'image', skip it
                    if ($fileType !== '' && $fileType !== 'image') {
                        return false;
                    }
                    // If file_type is empty, assume it's an image (legacy behavior)
                    return !empty($image->file_path);
                } catch (\Throwable $e) {
                    return false;
                }
            })->map(function ($image) {
                try {
                    return [
                        'url' => $image->url ?? '',
                        'thumbnail_url' => $image->thumbnail_url ?? '',
                        'description' => $image->description ?? '',
                    ];
                } catch (\Throwable $e) {
                    return null;
                }
            })->filter(function ($image) {
                // Filter out null values and images with no URL
                return $image !== null && !empty($image['url']);
            })->values(),
        ]);
    }
}
