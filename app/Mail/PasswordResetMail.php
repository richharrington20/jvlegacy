<?php

namespace App\Mail;

use App\Models\Account;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public Account $account;
    public string $resetUrl;

    public function __construct(Account $account, string $resetUrl)
    {
        $this->account = $account;
        $this->resetUrl = $resetUrl;
    }

    public function build()
    {
        $name = $this->account->person ? 
            ($this->account->person->first_name . ' ' . $this->account->person->last_name) : 
            ($this->account->company->name ?? 'Investor');

        $variables = [
            'name' => $name,
            'email' => $this->account->email,
            'reset_url' => $this->resetUrl,
            'expires_in' => '24 hours',
        ];

        $template = EmailTemplateService::getTemplateWithFallback('password_reset', $variables);

        return $this->subject($template['subject'])
            ->html($template['html'])
            ->text($template['text']);
    }
}

