<?php

namespace App\Mail;

use App\Models\Account;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountShareNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Account $primaryAccount;
    public Account $sharedAccount;
    public string $acceptUrl;

    public function __construct(Account $primaryAccount, Account $sharedAccount, string $acceptUrl)
    {
        $this->primaryAccount = $primaryAccount;
        $this->sharedAccount = $sharedAccount;
        $this->acceptUrl = $acceptUrl;
    }

    public function build()
    {
        $sharedName = $this->sharedAccount->person ? 
            ($this->sharedAccount->person->first_name . ' ' . $this->sharedAccount->person->last_name) : 
            ($this->sharedAccount->company->name ?? 'User');

        $primaryName = $this->primaryAccount->person ? 
            ($this->primaryAccount->person->first_name . ' ' . $this->primaryAccount->person->last_name) : 
            ($this->primaryAccount->company->name ?? 'Account Holder');

        $variables = [
            'name' => $sharedName,
            'primary_account_name' => $primaryName,
            'accept_url' => $this->acceptUrl,
            'dashboard_url' => route('investor.dashboard'),
        ];

        $template = EmailTemplateService::getTemplateWithFallback('account_share_notification', $variables);

        return $this->subject($template['subject'])
            ->html($template['html'])
            ->text($template['text']);
    }
}

