<?php

namespace App\Mail;

use App\Models\Account;
use App\Models\Project;
use App\Models\Update;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public Account $account;
    public ?Project $project;
    public Update $update;

    public function __construct(Account $account, ?Project $project, Update $update)
    {
        $this->account = $account;
        $this->project = $project;
        $this->update = $update;
    }

    public function build()
    {
        $name = $this->account->person ? 
            ($this->account->person->first_name . ' ' . $this->account->person->last_name) : 
            ($this->account->company->name ?? 'Investor');

        $projectName = $this->project?->name ?? 'Your Investment';
        $projectUrl = $this->project 
            ? route('investor.dashboard') . '#project-' . $this->project->id
            : route('investor.dashboard');

        $variables = [
            'name' => $name,
            'project_name' => $projectName,
            'update_content' => $this->update->comment ?? '',
            'update_date' => $this->update->sent_on ? $this->update->sent_on->format('d M Y') : date('d M Y'),
            'dashboard_url' => route('investor.dashboard'),
            'project_url' => $projectUrl,
        ];

        $template = EmailTemplateService::getTemplateWithFallback('project_update', $variables);

        return $this->subject($template['subject'])
            ->html($template['html'])
            ->text($template['text']);
    }
}

