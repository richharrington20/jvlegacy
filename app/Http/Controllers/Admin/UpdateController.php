<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ProjectUpdateMail;
use App\Models\Account;
use App\Models\Project;
use App\Models\Update;
use App\Models\UpdateImage;
use App\Models\Investments;
use App\Models\InvestorNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UpdateController extends Controller
{
    public function index(Request $request)
    {
        $query = Update::notDeleted();

        // Filter by project_id if provided
        $selectedProjectId = $request->input('project_id');
        if ($selectedProjectId) {
            $query->where('project_id', $selectedProjectId);
        }

        // Filter by category if provided
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $updates = $query->orderByDesc('sent_on')->paginate(20);

        // Order projects numerically by external project_id so the dropdown is in numeric order
        $projects = Project::orderBy('project_id')->get();

        return view('admin.updates.index', [
            'updates' => $updates,
            'projects' => $projects,
            'filters' => $request->only(['project_id', 'category']),
            'selectedProjectId' => $selectedProjectId, // Pass selected project ID to view
        ]);
    }

    public function show($id)
    {
        $update = Update::with(['project', 'images'])->findOrFail($id);
        return view('admin.updates.show', compact('update'));
    }

    public function edit($id)
    {
        $update = Update::with(['project', 'images'])->findOrFail($id);
        // Use numeric order by project_id for consistency with the create form
        $projects = Project::orderBy('project_id')->get();
        return view('admin.updates.edit', compact('update', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:legacy.projects,project_id',
            'category' => 'nullable|integer',
            'comment' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'image_descriptions' => 'nullable|array',
            'image_descriptions.*' => 'nullable|string|max:500',
            'existing_image_ids' => 'nullable|array',
            'existing_image_descriptions' => 'nullable|array',
            'existing_image_orders' => 'nullable|array',
        ]);

        $update = Update::findOrFail($id);
        $update->project_id = $validated['project_id'];
        $update->category = $validated['category'] ?? 3;
        $update->comment = $validated['comment'];
        $update->save();

        // Update existing images
        if ($request->has('existing_image_ids')) {
            $existingIds = $request->input('existing_image_ids', []);
            $existingDescriptions = $request->input('existing_image_descriptions', []);
            $existingOrders = $request->input('existing_image_orders', []);
            
            foreach ($existingIds as $index => $imageId) {
                $image = UpdateImage::find($imageId);
                if ($image && $image->update_id == $update->id) {
                    $image->description = $existingDescriptions[$index] ?? null;
                    $image->display_order = $existingOrders[$index] ?? $index;
                    $image->save();
                }
            }
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $descriptions = $validated['image_descriptions'] ?? [];
            $maxOrder = UpdateImage::where('update_id', $update->id)->max('display_order') ?? -1;
            
            foreach ($images as $index => $image) {
                $this->processUpdateImage(
                    $update->id, 
                    $image, 
                    $descriptions[$index] ?? null, 
                    $maxOrder + $index + 1
                );
            }
        }

        return redirect()->route('admin.updates.index', ['project_id' => $update->project_id])
            ->with('success', 'Update updated successfully.');
    }

    public function destroy($id)
    {
        $update = Update::findOrFail($id);
        $update->deleted = 1;
        $update->save();

        return redirect()->route('admin.updates.index')
            ->with('success', 'Update deleted successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:legacy.projects,project_id',
            'category' => 'nullable|integer',
            'comment' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'image_descriptions' => 'nullable|array',
            'image_descriptions.*' => 'nullable|string|max:500',
        ]);

        $update = new Update();
        $update->project_id = $validated['project_id'];
        $update->category = $validated['category'] ?? 3;
        $update->comment = $validated['comment'];
        $update->sent_on = now();
        $update->save();

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $descriptions = $validated['image_descriptions'] ?? [];
            
            foreach ($images as $index => $image) {
                $this->processUpdateImage($update->id, $image, $descriptions[$index] ?? null, $index);
            }
        }
        
        $emailcount = $this->dispatchBulkEmails($update);

        return redirect()->route('admin.updates.index', ['project_id' => $update->project_id])
            ->with('success', 'Update posted and ' . $emailcount . ' investors notified.');
    }

    protected function processUpdateImage($updateId, $imageFile, $description = null, $order = 0)
    {
        // Create directory if it doesn't exist
        $directory = 'updates/' . $updateId;
        Storage::disk('public')->makeDirectory($directory);

        // Generate unique filename
        $extension = $imageFile->getClientOriginalExtension();
        $fileName = time() . '_' . Str::random(8) . '.' . $extension;
        $filePath = $directory . '/' . $fileName;

        // Resize image using GD (max width 1200px, maintain aspect ratio)
        $this->resizeImage($imageFile->getRealPath(), storage_path('app/public/' . $filePath), 1200);
        
        // Create thumbnail (400px width)
        $thumbnailPath = $directory . '/thumb_' . $fileName;
        $this->resizeImage($imageFile->getRealPath(), storage_path('app/public/' . $thumbnailPath), 400);

        // Save to database
        UpdateImage::create([
            'update_id' => $updateId,
            'file_path' => $filePath,
            'file_name' => $imageFile->getClientOriginalName(),
            'file_size' => $imageFile->getSize(),
            'description' => $description,
            'display_order' => $order,
            'created_on' => now(),
        ]);
    }

    protected function resizeImage($sourcePath, $destinationPath, $maxWidth)
    {
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Calculate new dimensions
        if ($sourceWidth <= $maxWidth) {
            $newWidth = $sourceWidth;
            $newHeight = $sourceHeight;
        } else {
            $newWidth = $maxWidth;
            $newHeight = (int)(($sourceHeight / $sourceWidth) * $maxWidth);
        }

        // Create image resource from source
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }

        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG and GIF
        if ($mimeType == 'image/png' || $mimeType == 'image/gif') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Resize
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);

        // Save
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($newImage, $destinationPath, 85);
                break;
            case 'image/png':
                imagepng($newImage, $destinationPath, 8);
                break;
            case 'image/gif':
                imagegif($newImage, $destinationPath);
                break;
            case 'image/webp':
                imagewebp($newImage, $destinationPath, 85);
                break;
        }

        imagedestroy($sourceImage);
        imagedestroy($newImage);

        return true;
    }

    // Separate function for sending bulk emails
    public function sendBulkEmails($id)
    {
        $update = Update::findOrFail($id);
        $emailcount = $this->dispatchBulkEmails($update);

        return redirect()->route('admin.updates.index')
            ->with('success', $emailcount . ' investors notified.');
    }

    protected function dispatchBulkEmails(Update $update)
    {
        $investorAccounts = Investments::where('project_id', $update->project_id)
            ->with('account')
            ->get()
            ->pluck('account')
            ->filter()
            ->unique('email');

        $project = Project::find($update->project_id);

        // Send to investors
        foreach ($investorAccounts as $investorAccount) {
            try {
                Mail::to($investorAccount->email)->send(
                    new ProjectUpdateMail($investorAccount, $project, $update)
                );
            } catch (\Exception $e) {
                \Log::error("Failed to send update email to {$investorAccount->email}: " . $e->getMessage());
            }
        }

        // Also send to Ben and Scott (internal)
        $internalEmails = ['ben@rise-capital.uk', 'scott@rise-capital.uk'];
        foreach ($internalEmails as $email) {
            try {
                // Create a dummy account for internal emails
                $dummyAccount = new Account();
                $dummyAccount->email = $email;
                $dummyAccount->person = null;
                $dummyAccount->company = null;
                
                Mail::to($email)->send(
                    new ProjectUpdateMail($dummyAccount, $project, $update)
                );
            } catch (\Exception $e) {
                \Log::error("Failed to send update email to {$email}: " . $e->getMessage());
            }
        }

        $notificationMessage = Str::limit(strip_tags($update->comment), 200);
        foreach ($investorAccounts as $investorAccount) {
            InvestorNotification::firstOrCreate(
                [
                    'account_id' => $investorAccount->id,
                    'source_type' => 'update',
                    'source_id' => $update->id,
                ],
                [
                    'project_id' => $update->project_id,
                    'type' => 'update',
                    'message' => $notificationMessage,
                    'link' => url('/investor/dashboard') . '#project-' . $update->project_id,
                ]
            );
        }

        // Count emails sent (including internal recipients)
        $investorCount = $investorAccounts->count();
        $internalCount = count($internalEmails);

        return $investorCount + $internalCount;
    }

    // Function to send update email to just Ben, Scott and Chris

    public function sendSelectiveEmails(Update $update)
    {

        $mailData = [
            'content' => $update->comment,
            'url' => url('/investor/dashboard'),
        ];

        $project = Project::find($update->project_id);
        
        // Only send to Ben, Scott and Chris
        $emails = ['ben@rise-capital.uk', 'scott@rise-capital.uk', 'chris@jaevee.co.uk'];

        // if we are local, only send to chris
        if (app()->environment('local')) {
            $emails = ['chris@jaevee.co.uk'];
        }

        foreach ($emails as $email) {
            try {
                $dummyAccount = new Account();
                $dummyAccount->email = $email;
                $dummyAccount->person = null;
                $dummyAccount->company = null;
                
                Mail::to($email)->send(
                    new ProjectUpdateMail($dummyAccount, $project, $update)
                );
            } catch (\Exception $e) {
                \Log::error("Failed to send test update email to {$email}: " . $e->getMessage());
            }
        }

        return redirect()->route('admin.updates.index')
            ->with('success', 'Test email sent to ' . implode(', ', $emails));
    }

    // Shows a screen to confirm sending bulk emails for a specific update

    public function bulkEmailPreflight($id)
    {
        $update = Update::findOrFail($id);

        $investorAccounts = Investments::where('project_id', $update->project_id)
            ->with('account')
            ->get()
            ->pluck('account')
            ->filter()
            ->unique('email');

        return view('admin.updates.bulk_email_preflight', compact('update', 'investorAccounts'));
    }

    // Function to send a test email
    public function sendTestEmail()
    {
        $dummyAccount = new Account();
        $dummyAccount->email = 'chris@jaevee.co.uk';
        $dummyAccount->person = null;
        $dummyAccount->company = null;
        
        $dummyProject = new Project();
        $dummyProject->name = 'Test Project';
        
        $dummyUpdate = new Update();
        $dummyUpdate->comment = 'This is a test email.';
        $dummyUpdate->sent_on = now();

        try {
            Mail::to('chris@jaevee.co.uk')->send(
                new ProjectUpdateMail($dummyAccount, $dummyProject, $dummyUpdate)
            );
            return 'Test email sent.';
        } catch (\Exception $e) {
            \Log::error('Failed to send test update email: ' . $e->getMessage());
            return 'Failed to send test email.';
        }
    }
}
