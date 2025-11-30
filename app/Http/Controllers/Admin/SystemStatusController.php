<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemStatus;
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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'status_type' => 'required|in:info,success,warning,error,maintenance',
            'is_active' => 'nullable|boolean',
            'show_on_login' => 'nullable|boolean',
        ]);

        // Deactivate all other statuses if this one is active and should show on login
        if ($validated['is_active'] && $validated['show_on_login']) {
            SystemStatus::where('show_on_login', true)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $status = SystemStatus::create([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'status_type' => $validated['status_type'],
            'is_active' => $validated['is_active'] ?? true,
            'show_on_login' => $validated['show_on_login'] ?? true,
            'created_by' => auth()->id(),
            'created_on' => now(),
            'updated_on' => now(),
        ]);

        return redirect()->route('admin.system-status.index')
            ->with('success', 'System status created successfully.');
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
}

