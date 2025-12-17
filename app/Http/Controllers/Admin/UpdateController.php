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
        
        // Calculate how many investors should receive this update
        $investorCount = 0;
        if ($update->project_id) {
            $project = Project::where('project_id', $update->project_id)->first();
            if ($project) {
                $investorCount = Investments::where('project_id', $project->id)
                    ->where('paid', 1)
                    ->with('account')
                    ->get()
                    ->pluck('account')
                    ->filter()
                    ->unique('email')
                    ->count();
            }
        }
        
        return view('admin.updates.show', compact('update', 'investorCount'));
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
            'images.*' => 'file|mimes:jpeg,png,jpg,gif,webp,pdf,doc,docx,xls,xlsx,txt,csv|max:20480',
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
            'images.*' => 'file|mimes:jpeg,png,jpg,gif,webp,pdf,doc,docx,xls,xlsx,txt,csv|max:20480',
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
        
        $result = $this->dispatchBulkEmails($update);
        
        // Handle array result (new format) or integer (backward compatibility)
        $emailcount = is_array($result) ? $result['sent'] : $result;
        $attempted = is_array($result) ? $result['attempted'] : 0;
        $failed = is_array($result) ? $result['failed'] : 0;
        
        // Provide more informative feedback when creating updates
        if ($emailcount == 0) {
            $project = Project::where('project_id', $update->project_id)->first();
            if (!$project) {
                return redirect()->route('admin.updates.index', ['project_id' => $update->project_id])
                    ->with('warning', 'Update posted but not sent: Project not found.');
            }
            
            $investorCount = Investments::where('project_id', $project->id)
                ->where('paid', 1)
                ->count();
            
            if ($investorCount == 0) {
                return redirect()->route('admin.updates.index', ['project_id' => $update->project_id])
                    ->with('warning', 'Update posted but not sent: No investors with paid investments found for this project.');
            } elseif ($attempted > 0 && $failed > 0) {
                // Investors exist with valid emails, but sending failed
                return redirect()->route('admin.updates.index', ['project_id' => $update->project_id])
                    ->with('error', "Update posted but emails failed to send. Attempted {$attempted} email(s), all failed. Please check logs and try again.");
            } elseif ($attempted == 0) {
                // No valid emails found
                return redirect()->route('admin.updates.index', ['project_id' => $update->project_id])
                    ->with('warning', 'Update posted but not sent: No valid investor emails found.');
            } else {
                // Fallback case
                return redirect()->route('admin.updates.index', ['project_id' => $update->project_id])
                    ->with('warning', 'Update posted but not sent: No emails were sent.');
            }
        }

        // Success message
        $message = 'Update posted and ' . $emailcount . ' investor' . ($emailcount !== 1 ? 's' : '') . ' notified.';
        if ($failed > 0) {
            $message .= " ({$failed} email(s) failed to send - check logs for details)";
        }
        
        return redirect()->route('admin.updates.index', ['project_id' => $update->project_id])
            ->with($failed > 0 ? 'warning' : 'success', $message);
    }

    protected function processUpdateImage($updateId, $imageFile, $description = null, $order = 0)
    {
        // Create directory if it doesn't exist
        $directory = 'updates/' . $updateId;
        Storage::disk('public')->makeDirectory($directory);

        // Generate unique filename
        $extension = strtolower($imageFile->getClientOriginalExtension());
        $fileName = time() . '_' . Str::random(8) . '.' . $extension;
        $filePath = $directory . '/' . $fileName;
        $mimeType = $imageFile->getMimeType();
        
        // Determine file type
        $fileType = 'document';
        if (str_starts_with($mimeType, 'image/') || in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
            $fileType = 'image';
        } elseif ($extension === 'pdf' || str_contains($mimeType, 'pdf')) {
            $fileType = 'pdf';
        } elseif (in_array($extension, ['doc', 'docx']) || str_contains($mimeType, 'word')) {
            $fileType = 'word';
        } elseif (in_array($extension, ['xls', 'xlsx']) || str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet')) {
            $fileType = 'excel';
        } elseif (in_array($extension, ['txt', 'csv'])) {
            $fileType = 'text';
        }

        // Only resize images
        if ($fileType === 'image') {
            // Resize image using GD (max width 1200px, maintain aspect ratio)
            $this->resizeImage($imageFile->getRealPath(), storage_path('app/public/' . $filePath), 1200);
            
            // Create thumbnail (400px width)
            $thumbnailPath = $directory . '/thumb_' . $fileName;
            $this->resizeImage($imageFile->getRealPath(), storage_path('app/public/' . $thumbnailPath), 400);
        } else {
            // For non-image files, just store them as-is
            $imageFile->storeAs('public/' . $directory, $fileName);
        }

        // Save to database
        UpdateImage::create([
            'update_id' => $updateId,
            'file_path' => $filePath,
            'file_name' => $imageFile->getClientOriginalName(),
            'file_type' => $fileType,
            'mime_type' => $mimeType,
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
        $result = $this->dispatchBulkEmails($update);
        
        // Handle array result (new format) or integer (backward compatibility)
        $emailcount = is_array($result) ? $result['sent'] : $result;
        $attempted = is_array($result) ? $result['attempted'] : 0;
        $failed = is_array($result) ? $result['failed'] : 0;
        
        // Provide more informative feedback
        if ($emailcount == 0) {
            $project = Project::where('project_id', $update->project_id)->first();
            if (!$project) {
                return redirect()->route('admin.updates.index')
                    ->with('error', 'Update not sent: Project not found for this update.');
            }
            
            $investorCount = Investments::where('project_id', $project->id)
                ->where('paid', 1)
                ->count();
            
            if ($investorCount == 0) {
                return redirect()->route('admin.updates.index')
                    ->with('warning', 'Update not sent: No investors with paid investments found for this project.');
            } elseif ($attempted > 0 && $failed > 0) {
                // Investors exist with valid emails, but sending failed
                return redirect()->route('admin.updates.index')
                    ->with('error', "Update not sent: Email delivery failed. Attempted {$attempted} email(s), all failed. Please check logs and try again.");
            } elseif ($attempted == 0) {
                // No valid emails found
                return redirect()->route('admin.updates.index')
                    ->with('warning', 'Update not sent: No valid investor emails found (0 investors notified).');
            } else {
                // Fallback case
                return redirect()->route('admin.updates.index')
                    ->with('warning', 'Update not sent: No emails were sent.');
            }
        }

        // Success message
        $message = $emailcount . ' investor' . ($emailcount !== 1 ? 's' : '') . ' notified.';
        if ($failed > 0) {
            $message .= " ({$failed} email(s) failed to send - check logs for details)";
        }
        
        return redirect()->route('admin.updates.index')
            ->with($failed > 0 ? 'warning' : 'success', $message);
    }

    protected function dispatchBulkEmails(Update $update)
    {
        // First, find the project by external project_id
        $project = Project::where('project_id', $update->project_id)->first();

        // If project doesn't exist, log error and skip sending emails
        if (!$project) {
            \Log::error("Project not found for update ID {$update->id} (project_id: {$update->project_id}). Cannot send update emails.");
            return 0;
        }

        // Investments table uses internal 'id', not external 'project_id'
        // So we need to find investments by the project's internal id
        $investments = Investments::where('project_id', $project->id)
            ->where('paid', 1)
            ->with('account')
            ->get();
        
        \Log::info("Found " . $investments->count() . " paid investments for project {$project->project_id} (internal id: {$project->id})");
        
        // Extract accounts and filter out nulls and accounts without emails
        $investorAccounts = $investments
            ->pluck('account')
            ->filter(function ($account) {
                return $account && !empty($account->email);
            })
            ->unique('email');

        // Log how many valid investor accounts found
        \Log::info("Found " . $investorAccounts->count() . " valid investor accounts with emails for project {$project->project_id}");
        
        // Log account details for debugging
        foreach ($investorAccounts as $idx => $account) {
            \Log::info("Investor account #{$idx}: ID {$account->id}, Email: {$account->email}");
        }
        
        if ($investorAccounts->count() === 0) {
            \Log::warning("No valid investor accounts found for project {$project->project_id}. No emails will be sent.");
            // Log why accounts might be missing
            $totalInvestments = $investments->count();
            $nullAccounts = $investments->filter(fn($inv) => !$inv->account)->count();
            $noEmailAccounts = $investments->filter(fn($inv) => $inv->account && empty($inv->account->email))->count();
            \Log::warning("Breakdown: {$totalInvestments} investments, {$nullAccounts} with null accounts, {$noEmailAccounts} with accounts but no email");
        }

        // Load images for the update before sending emails
        if (!$update->relationLoaded('images')) {
            $update->load('images');
        }

        // Send to investors using Postmark mailer
        $sentCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $attemptedCount = 0;
        
        foreach ($investorAccounts as $index => $investorAccount) {
            \Log::info("Processing investor account #{$index}: " . ($investorAccount ? "ID {$investorAccount->id}" : "NULL"));
            
            if (!$investorAccount) {
                \Log::warning("Skipping null investor account at index {$index}");
                $skippedCount++;
                continue;
            }
            
            if (!$investorAccount->email) {
                \Log::warning("Skipping investor account ID {$investorAccount->id} - no email address (email is: " . var_export($investorAccount->email, true) . ")");
                $skippedCount++;
                continue;
            }
            
            // This account has a valid email, so we're attempting to send
            $attemptedCount++;
            
            try {
                \Log::info("Attempting to send update email to {$investorAccount->email} (Account ID: {$investorAccount->id}) via Postmark");
                Mail::mailer('postmark')->to($investorAccount->email)->send(
                    new ProjectUpdateMail($investorAccount, $project, $update)
                );
                $sentCount++;
                \Log::info("Update email sent successfully to {$investorAccount->email} via Postmark");
            } catch (\Exception $e) {
                $failedCount++;
                \Log::error("Failed to send update email to {$investorAccount->email}: " . $e->getMessage());
                \Log::error("Stack trace: " . $e->getTraceAsString());
            }
        }
        
        \Log::info("Email sending summary: {$sentCount} sent, {$failedCount} failed, {$skippedCount} skipped, {$attemptedCount} attempted, " . $investorAccounts->count() . " total accounts found");
        
        \Log::info("Total emails sent: {$sentCount} out of " . $investorAccounts->count() . " investors");

        // Also send to Ben and Scott (internal) using Postmark mailer
        // Track how many internal emails we send so we can include them in the total count
        $internalSentCount = 0;
        $internalEmails = ['ben@rise-capital.uk', 'scott@rise-capital.uk'];
        foreach ($internalEmails as $email) {
            try {
                // Create a dummy account for internal emails
                $dummyAccount = new Account();
                $dummyAccount->email = $email;
                $dummyAccount->person = null;
                $dummyAccount->company = null;
                
                Mail::mailer('postmark')->to($email)->send(
                    new ProjectUpdateMail($dummyAccount, $project, $update)
                );
                $internalSentCount++;
                \Log::info("Update email sent successfully to {$email} via Postmark");
            } catch (\Exception $e) {
                \Log::error("Failed to send update email to {$email}: " . $e->getMessage());
                \Log::error("Stack trace: " . $e->getTraceAsString());
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

        // Mark update as sent
        $update->sent = 1;
        $update->save();

        // Return total number of recipients, including internal emails
        return $sentCount + $internalSentCount;
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
                
                Mail::mailer('postmark')->to($email)->send(
                    new ProjectUpdateMail($dummyAccount, $project, $update)
                );
                \Log::info("Test update email sent successfully to {$email} via Postmark");
            } catch (\Exception $e) {
                \Log::error("Failed to send test update email to {$email}: " . $e->getMessage());
                \Log::error("Stack trace: " . $e->getTraceAsString());
            }
        }

        return redirect()->route('admin.updates.index')
            ->with('success', 'Test email sent to ' . implode(', ', $emails));
    }

    // Shows a screen to confirm sending bulk emails for a specific update

    public function bulkEmailPreflight($id)
    {
        $update = Update::findOrFail($id);
        
        // Find the project by external project_id first
        $project = Project::where('project_id', $update->project_id)->first();
        
        $investorAccounts = collect();
        if ($project) {
            // Use the project's internal id to find investments
            $investorAccounts = Investments::where('project_id', $project->id)
                ->where('paid', 1)
                ->with('account')
                ->get()
                ->pluck('account')
                ->filter()
                ->unique('email');
        }

        return view('admin.updates.bulk_email_preflight', compact('update', 'investorAccounts', 'project'));
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
            Mail::mailer('postmark')->to('chris@jaevee.co.uk')->send(
                new ProjectUpdateMail($dummyAccount, $dummyProject, $dummyUpdate)
            );
            return 'Test email sent via Postmark.';
        } catch (\Exception $e) {
            \Log::error('Failed to send test update email: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return 'Failed to send test email: ' . $e->getMessage();
        }
    }

    // Resend update emails to all investors
    public function resend($id)
    {
        $update = Update::findOrFail($id);
        
        $emailCount = $this->dispatchBulkEmails($update);
        
        // Update sent_on timestamp to reflect the resend
        $update->sent_on = now();
        $update->save();
        
        return redirect()->route('admin.updates.index', ['project_id' => $update->project_id])
            ->with('success', "Update emails resent successfully. {$emailCount} investors notified.");
    }
}
