<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Mail\ProjectDocumentsMail;
use App\Models\DocumentEmailLog;
use App\Models\Investments;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class InvestorDocumentController extends Controller
{
    public function email(Project $project, Request $request)
    {
        $account = $request->user('investor');

        $hasInvestment = Investments::where('project_id', $project->project_id)
            ->where('account_id', $account->id)
            ->where('paid', 1)
            ->exists();

        if (! $hasInvestment) {
            abort(403);
        }

        $documents = $project->investorDocuments;

        if ($documents->isEmpty()) {
            return back()->with('status', 'No documents available to email yet.');
        }

        Mail::to($account->email)->send(new ProjectDocumentsMail($account, $project, $documents));

        foreach ($documents as $document) {
            DocumentEmailLog::create([
                'project_id' => $project->project_id,
                'account_id' => $account->id,
                'document_id' => $document->id ?? null,
                'document_name' => $document->name ?? null,
                'recipient' => $account->email,
                'sent_by' => Auth::id(),
                'sent_at' => now(),
            ]);
        }

        return back()->with('status', 'Document links emailed to ' . $account->email);
    }
}

