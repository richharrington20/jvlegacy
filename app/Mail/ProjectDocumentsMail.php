<?php

namespace App\Mail;

use App\Models\Account;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ProjectDocumentsMail extends Mailable
{
    use Queueable, SerializesModels;

    public Project $project;
    public Account $account;

    /** @var \Illuminate\Support\Collection<int, \App\Models\ProjectInvestorDocument> */
    public Collection $documents;

    public function __construct(Account $account, Project $project, $documents)
    {
        $this->account = $account;
        $this->project = $project;
        $this->documents = Collection::wrap($documents);
    }

    public function build()
    {
        return $this->subject('Your documents for ' . ($this->project->name ?? 'your investment'))
            ->view('emails.project_documents')
            ->with([
                'project' => $this->project,
                'documents' => $this->documents,
                'account' => $this->account,
            ]);
    }
}

