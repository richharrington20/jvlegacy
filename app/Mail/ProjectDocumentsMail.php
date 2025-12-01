<?php

namespace App\Mail;

use App\Models\Account;
use App\Models\Project;
use App\Services\EmailTemplateService;
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
        $name = $this->account->person ? 
            ($this->account->person->first_name . ' ' . $this->account->person->last_name) : 
            ($this->account->company->name ?? 'Investor');

        // Build document list HTML
        $documentsHtml = '<ul style="list-style: none; padding: 0;">';
        $documentsText = "\n";
        foreach ($this->documents as $doc) {
            $docUrl = route('document.investor', ['hash' => $doc->hash]);
            $documentsHtml .= '<li style="margin-bottom: 10px;"><a href="' . $docUrl . '" style="color: #3b82f6; text-decoration: none;">' . htmlspecialchars($doc->name) . '</a></li>';
            $documentsText .= "- " . $doc->name . "\n  " . $docUrl . "\n\n";
        }
        $documentsHtml .= '</ul>';

        $variables = [
            'name' => $name,
            'project_name' => $this->project->name ?? 'Your Investment',
            'documents_list' => $documentsHtml,
            'documents_list_text' => $documentsText,
            'dashboard_url' => route('investor.dashboard'),
        ];

        $template = EmailTemplateService::getTemplateWithFallback('project_documents', $variables);

        return $this->subject($template['subject'])
            ->html($template['html'])
            ->text($template['text']);
    }
}

