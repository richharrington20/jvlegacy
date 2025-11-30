<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UpdateImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpdateImageController extends Controller
{
    public function delete($imageId)
    {
        $image = UpdateImage::findOrFail($imageId);
        $updateId = $image->update_id;
        
        // Soft delete
        $image->deleted = true;
        $image->save();
        
        return redirect()->back()->with('success', 'Image deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'required|integer|exists:legacy.update_images,id',
        ]);

        foreach ($validated['image_ids'] as $order => $imageId) {
            UpdateImage::where('id', $imageId)->update(['display_order' => $order]);
        }

        return response()->json(['success' => true]);
    }

    public function updateDescription(Request $request, $imageId)
    {
        $validated = $request->validate([
            'description' => 'nullable|string|max:500',
        ]);

        $image = UpdateImage::findOrFail($imageId);
        $image->description = $validated['description'];
        $image->save();

        return response()->json(['success' => true, 'description' => $image->description]);
    }
}

