<?php

namespace App\Mail;

use App\Models\Account;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeInvestorMail extends Mailable
{
    use Queueable, SerializesModels;

    public Account $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function build()
    {
        $name = $this->account->person ? 
            ($this->account->person->first_name . ' ' . $this->account->person->last_name) : 
            ($this->account->company->name ?? 'Investor');

        $variables = [
            'name' => $name,
            'email' => $this->account->email,
            'login_url' => route('investor.login'),
            'dashboard_url' => route('investor.dashboard'),
        ];

        $template = EmailTemplateService::getTemplateWithFallback('welcome_investor', $variables);

        return $this->subject($template['subject'])
            ->html($template['html'])
            ->text($template['text']);
    }
}

