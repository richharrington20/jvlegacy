<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ProjectDocumentsMail;
use App\Models\DocumentEmailLog;
use App\Models\Investments;
use App\Models\Project;
use App\Models\Update;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with('property');
        
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('project_id', 'like', '%' . $request->search . '%');
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $projects = $query->orderByDesc('created_on')->paginate(25)->withQueryString();
        
        return view('admin.projects.index', compact('projects'));
    }
    
    public function create()
    {
        $accounts = \App\Models\Account::on('legacy')
            ->where('deleted', 0)
            ->with('person', 'company')
            ->orderBy('id')
            ->limit(500)
            ->get();
        
        return view('admin.projects.create', compact('accounts'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'account_id' => 'required|exists:legacy.accounts,id',
            'status' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);
        
        // Get the next project_id
        $lastProject = Project::whereNotNull('project_id')
            ->orderByDesc('project_id')
            ->first();
        
        $nextProjectId = $lastProject ? ((int)$lastProject->project_id + 1) : 1000;
        
        $project = new Project();
        $project->name = $validated['name'];
        $project->description = $validated['description'] ?? '';
        $project->account_id = $validated['account_id'];
        $project->status = $validated['status'] ?? Project::STATUS_NOT_SUBMITTED;
        $project->progress = 0;
        $project->created_on = now();
        $project->updated_on = now();
        
        // Save first to get the internal ID
        $project->save();
        
        // Now set the project_id (external ID)
        $project->project_id = $nextProjectId;
        $project->save();
        
        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $project->project_id . '_' . time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('projects', $imageName, 'public');
            // Store image path - you may need to add an image_path column or use a files table
            // For now, we'll just store it (you can add this field later if needed)
        }
        
        return redirect()->route('admin.projects.show', $project->project_id)
            ->with('success', 'Project created successfully with Project ID: ' . $project->project_id);
    }

    public function show($projectId)
    {
        $project = Project::with(['property', 'investorDocuments'])
            ->where('project_id', $projectId)
            ->firstOrFail();

        // Get ALL investments (paid and unpaid) for financial summary
        $allInvestments = Investments::with(['account.person', 'account.company'])
            ->where('project_id', $project->id)
            ->get();

        // Calculate financial summary
        $totalInvested = $allInvestments->sum('amount');
        $totalPaid = $allInvestments->where('paid', 1)->sum('amount');
        $totalUnpaid = $allInvestments->where('paid', 0)->sum('amount');
        $investmentCount = $allInvestments->count();
        $paidCount = $allInvestments->where('paid', 1)->count();
        $unpaidCount = $allInvestments->where('paid', 0)->count();

        // Get all investors for this project (only paid investments for display)
        $investments = $allInvestments->where('paid', 1)->unique('account_id');

        // Get all unique investor accounts
        $investors = $investments->map(function ($investment) {
            return $investment->account;
        })->filter()->unique('id');

        // Get all updates for this project
        $updates = Update::where('project_id', $project->project_id)
            ->where('deleted', 0)
            ->orderByDesc('sent_on')
            ->paginate(20);

        // Get document email logs for this project (if table exists)
        try {
            $documentLogs = DocumentEmailLog::with('account')
                ->where('project_id', $project->project_id)
                ->orderByDesc('sent_at')
                ->limit(50)
                ->get();
        } catch (\Exception $e) {
            // Table doesn't exist yet, use empty collection
            $documentLogs = collect();
        }

        // Get all accounts for investment creation dropdown
        $accounts = \App\Models\Account::on('legacy')
            ->where('deleted', 0)
            ->with('person', 'company')
            ->orderBy('id')
            ->limit(500)
            ->get();

        return view('admin.projects.show', compact(
            'project',
            'investors',
            'investments',
            'allInvestments',
            'updates',
            'documentLogs',
            'accounts',
            'totalInvested',
            'totalPaid',
            'totalUnpaid',
            'investmentCount',
            'paidCount',
            'unpaidCount'
        ));
    }

    public function edit($projectId)
    {
        $project = Project::where('project_id', $projectId)->firstOrFail();
        $accounts = \App\Models\Account::on('legacy')
            ->where('deleted', 0)
            ->with('person', 'company')
            ->orderBy('id')
            ->limit(500)
            ->get();
        
        return view('admin.projects.edit', compact('project', 'accounts'));
    }

    public function update(Request $request, $projectId)
    {
        $project = Project::where('project_id', $projectId)->firstOrFail();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'account_id' => 'required|exists:legacy.accounts,id',
            'status' => 'nullable|integer',
            'progress' => 'nullable|integer|min:0|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $project->name = $validated['name'];
        $project->description = $validated['description'] ?? '';
        $project->account_id = $validated['account_id'];
        $project->status = $validated['status'] ?? $project->status;
        $project->progress = $validated['progress'] ?? $project->progress;
        $project->updated_on = now();
        $project->save();

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $project->project_id . '_' . time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('projects', $imageName, 'public');
            $project->image_path = $imagePath;
            $project->save();
        }

        return redirect()->route('admin.projects.show', $project->project_id)
            ->with('success', 'Project updated successfully.');
    }

    public function resendDocuments(Request $request, $projectId)
    {
        $project = Project::with('investorDocuments')
            ->where('project_id', $projectId)
            ->firstOrFail();

        $accountId = $request->input('account_id');

        if ($accountId) {
            // Resend to specific investor
            $account = \App\Models\Account::findOrFail($accountId);
            $documents = $project->investorDocuments;

            if ($documents->isEmpty()) {
                return back()->with('error', 'No documents available to email.');
            }

            Mail::to($account->email)->send(new ProjectDocumentsMail($account, $project, $documents));

            try {
                foreach ($documents as $document) {
                    DocumentEmailLog::create([
                        'project_id' => $project->project_id,
                        'account_id' => $account->id,
                        'document_id' => $document->id ?? null,
                        'document_name' => $document->name ?? null,
                        'recipient' => $account->email,
                        'sent_by' => auth()->id(),
                        'sent_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                // Table doesn't exist yet, skip logging
            }

            return back()->with('success', 'Documents sent to ' . $account->email);
        } else {
            // Resend to all investors
            $investments = Investments::with('account')
                ->where('project_id', $project->project_id)
                ->where('paid', 1)
                ->get()
                ->pluck('account')
                ->filter()
                ->unique('email');

            $documents = $project->investorDocuments;

            if ($documents->isEmpty()) {
                return back()->with('error', 'No documents available to email.');
            }

            $sentCount = 0;
            foreach ($investments as $account) {
                try {
                    Mail::to($account->email)->send(new ProjectDocumentsMail($account, $project, $documents));

                    try {
                        foreach ($documents as $document) {
                            DocumentEmailLog::create([
                                'project_id' => $project->project_id,
                                'account_id' => $account->id,
                                'document_id' => $document->id ?? null,
                                'document_name' => $document->name ?? null,
                                'recipient' => $account->email,
                                'sent_by' => auth()->id(),
                                'sent_at' => now(),
                            ]);
                        }
                    } catch (\Exception $e) {
                        // Table doesn't exist yet, skip logging
                    }
                    $sentCount++;
                } catch (\Exception $e) {
                    // Log error but continue
                }
            }

            return back()->with('success', "Documents sent to {$sentCount} investors.");
        }
    }
}
