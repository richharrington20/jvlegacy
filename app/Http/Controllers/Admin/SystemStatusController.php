<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemStatus;
use App\Models\SystemStatusUpdate;
use Illuminate\Http\Request;

class SystemStatusController extends Controller
{
    public function index()
    {
        try {
            $statuses = SystemStatus::where('deleted', false)
                ->orderByDesc('created_on')
                ->paginate(20);
        } catch (\Exception $e) {
            // Table doesn't exist yet
            $statuses = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('admin.system-status.index', compact('statuses'));
    }

    public function create()
    {
        return view('admin.system-status.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'message' => 'required|string|min:1',
                'status_type' => 'required|in:info,success,warning,error,maintenance',
                'is_active' => 'nullable|in:0,1',
                'show_on_login' => 'nullable|in:0,1',
            ], [
                'message.required' => 'The message field is required.',
                'message.min' => 'The message field must contain at least one character.',
            ]);

            // Convert checkbox values to boolean
            $isActive = $request->has('is_active') && $request->is_active == '1';
            $showOnLogin = $request->has('show_on_login') && $request->show_on_login == '1';

            // Deactivate all other statuses if this one is active and should show on login
            if ($isActive && $showOnLogin) {
                try {
                    SystemStatus::where('show_on_login', true)
                        ->where('is_active', true)
                        ->update(['is_active' => false]);
                } catch (\Exception $e) {
                    // Table might not exist yet, ignore
                }
            }

            // Clean the message - remove empty paragraphs and whitespace
            $message = trim($validated['message']);
            // Remove empty paragraphs like <p><br></p> or <p></p>
            $message = preg_replace('/<p[^>]*>(\s|&nbsp;|<br\s*\/?>)*<\/p>/i', '', $message);
            $message = trim($message);
            
            // Validate that we have actual content after cleaning
            if (empty($message) || strip_tags($message) === '') {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['message' => 'The message field must contain actual text content.']);
            }
            
            $status = SystemStatus::create([
                'title' => $validated['title'],
                'message' => $message,
                'status_type' => $validated['status_type'],
                'is_active' => $isActive,
                'show_on_login' => $showOnLogin,
                'created_by' => auth()->id(),
                'created_on' => now(),
                'updated_on' => now(),
            ]);

            return redirect()->route('admin.system-status.index')
                ->with('success', 'System status created successfully.');
        } catch (\Exception $e) {
            // Check if it's a table not found error
            if (str_contains($e->getMessage(), "Table 'jvsys.system_status' doesn't exist")) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'The system_status table does not exist yet. Please run the migration: /run-system-status-migration']);
            }
            
            // Re-throw other errors
            throw $e;
        }
    }

    public function edit($id)
    {
        $status = SystemStatus::findOrFail($id);
        return view('admin.system-status.edit', compact('status'));
    }

    public function update(Request $request, $id)
    {
        $status = SystemStatus::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'status_type' => 'required|in:info,success,warning,error,maintenance',
            'is_active' => 'nullable|boolean',
            'show_on_login' => 'nullable|boolean',
        ]);

        // Deactivate all other statuses if this one is being activated and should show on login
        if ($validated['is_active'] && $validated['show_on_login'] && !$status->is_active) {
            SystemStatus::where('id', '!=', $id)
                ->where('show_on_login', true)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $status->title = $validated['title'];
        $status->message = $validated['message'];
        $status->status_type = $validated['status_type'];
        $status->is_active = $validated['is_active'] ?? $status->is_active;
        $status->show_on_login = $validated['show_on_login'] ?? $status->show_on_login;
        $status->updated_on = now();
        $status->save();

        return redirect()->route('admin.system-status.index')
            ->with('success', 'System status updated successfully.');
    }

    public function destroy($id)
    {
        $status = SystemStatus::findOrFail($id);
        $status->deleted = true;
        $status->updated_on = now();
        $status->save();

        return redirect()->route('admin.system-status.index')
            ->with('success', 'System status deleted successfully.');
    }

    public function toggle($id)
    {
        $status = SystemStatus::findOrFail($id);
        $status->is_active = !$status->is_active;
        $status->updated_on = now();
        $status->save();

        return redirect()->back()->with('success', 'Status toggled successfully.');
    }

    public function addUpdate(Request $request, $id)
    {
        $status = SystemStatus::findOrFail($id);

        $validated = $request->validate([
            'message' => 'required|string|min:1',
        ]);

        $update = SystemStatusUpdate::create([
            'status_id' => $status->id,
            'account_id' => auth()->id(),
            'message' => $validated['message'],
            'created_on' => now(),
            'updated_on' => now(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'update' => $update->load('account.person', 'account.company'),
            ]);
        }

        return redirect()->back()->with('success', 'Update added successfully.');
    }

    public function markFixed(Request $request, $updateId)
    {
        $update = SystemStatusUpdate::findOrFail($updateId);
        $update->is_fixed = true;
        $update->fixed_by = auth()->id();
        $update->fixed_on = now();
        $update->updated_on = now();
        $update->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'update' => $update->load('account.person', 'account.company', 'fixedBy.person', 'fixedBy.company'),
            ]);
        }

        return redirect()->back()->with('success', 'Status update marked as fixed.');
    }
}

