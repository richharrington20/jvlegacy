<?php

namespace App\Mail;

use App\Models\Account;
use App\Models\SupportTicket;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportTicketConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Account $account;
    public SupportTicket $ticket;

    public function __construct(Account $account, SupportTicket $ticket)
    {
        $this->account = $account;
        $this->ticket = $ticket;
    }

    public function build()
    {
        $name = $this->account->person ? 
            ($this->account->person->first_name . ' ' . $this->account->person->last_name) : 
            ($this->account->company->name ?? 'Investor');

        $variables = [
            'name' => $name,
            'ticket_id' => $this->ticket->ticket_id ?? 'N/A',
            'subject' => $this->ticket->subject ?? 'Support Request',
            'message' => $this->ticket->message ?? '',
            'dashboard_url' => route('investor.dashboard'),
        ];

        $template = EmailTemplateService::getTemplateWithFallback('support_ticket_confirmation', $variables);

        return $this->subject($template['subject'])
            ->html($template['html'])
            ->text($template['text']);
    }
}

