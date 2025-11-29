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
        return view('admin.projects.index');
    }

    public function show($projectId)
    {
        $project = Project::with(['property', 'investorDocuments'])
            ->where('project_id', $projectId)
            ->firstOrFail();

        // Get all investors for this project
        $investments = Investments::with(['account.person', 'account.company'])
            ->where('project_id', $project->project_id)
            ->where('paid', 1)
            ->get()
            ->unique('account_id');

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

        return view('admin.projects.show', compact(
            'project',
            'investors',
            'investments',
            'updates',
            'documentLogs'
        ));
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
