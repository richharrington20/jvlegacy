<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Services\EmailTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailTemplateController extends Controller
{
    // Available variables for each template type
    protected $templateVariables = [
        'project_update' => [
            'name' => 'Recipient name',
            'project_name' => 'Project name',
            'update_content' => 'Update content/comment',
            'update_date' => 'Date of update',
            'dashboard_url' => 'Link to investor dashboard',
            'project_url' => 'Link to specific project',
            'attachments_html' => 'HTML for attachments list',
            'attachments_text' => 'Plain text attachments list',
        ],
        'project_documents' => [
            'name' => 'Recipient name',
            'project_name' => 'Project name',
            'documents_list' => 'HTML list of documents',
            'documents_list_text' => 'Plain text list of documents',
            'dashboard_url' => 'Link to investor dashboard',
        ],
        'welcome_investor' => [
            'name' => 'Recipient name',
            'email' => 'Recipient email',
            'login_url' => 'Link to login page',
            'dashboard_url' => 'Link to investor dashboard',
        ],
        'password_reset' => [
            'name' => 'Recipient name',
            'reset_url' => 'Password reset link',
            'expires_in' => 'Reset link expiration time',
        ],
        'support_ticket_confirmation' => [
            'name' => 'Recipient name',
            'ticket_id' => 'Support ticket ID',
            'subject' => 'Ticket subject',
            'message' => 'Ticket message',
            'dashboard_url' => 'Link to investor dashboard',
        ],
        'account_share' => [
            'name' => 'Recipient name',
            'shared_by' => 'Name of person sharing',
            'dashboard_url' => 'Link to investor dashboard',
        ],
    ];

    public function index()
    {
        $templates = EmailTemplate::on('legacy')
            ->where('deleted', 0)
            ->orderBy('key')
            ->get();

        // Group templates by category
        $groupedTemplates = $templates->groupBy(function ($template) {
            if (str_contains($template->key, 'project')) {
                return 'Project Emails';
            } elseif (str_contains($template->key, 'welcome') || str_contains($template->key, 'password')) {
                return 'Account Emails';
            } elseif (str_contains($template->key, 'support')) {
                return 'Support Emails';
            }
            return 'Other';
        });

        return view('admin.email-templates.index', [
            'templates' => $templates,
            'groupedTemplates' => $groupedTemplates,
        ]);
    }

    public function show($id)
    {
        $template = EmailTemplate::on('legacy')->findOrFail($id);
        $variables = $this->templateVariables[$template->key] ?? [];

        return view('admin.email-templates.show', [
            'template' => $template,
            'variables' => $variables,
        ]);
    }

    public function edit($id)
    {
        $template = EmailTemplate::on('legacy')->findOrFail($id);
        $variables = $this->templateVariables[$template->key] ?? [];

        return view('admin.email-templates.edit', [
            'template' => $template,
            'variables' => $variables,
        ]);
    }

    public function update(Request $request, $id)
    {
        $template = EmailTemplate::on('legacy')->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:500',
            'body_html' => 'nullable|string',
            'body_text' => 'nullable|string',
        ]);

        $template->fill($data);
        $template->updated_on = now();
        $template->save();

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Email template updated successfully.');
    }

    public function preview(Request $request, $id)
    {
        $template = EmailTemplate::on('legacy')->findOrFail($id);
        $variables = $this->templateVariables[$template->key] ?? [];

        // Generate sample variables for preview
        $sampleVariables = [];
        foreach ($variables as $key => $description) {
            $sampleVariables[$key] = $this->getSampleValue($key);
        }

        $preview = EmailTemplateService::getTemplate($template->key, $sampleVariables);

        return response()->json([
            'subject' => $preview['subject'] ?? $template->subject,
            'html' => $preview['html'] ?? $template->body_html,
            'text' => $preview['text'] ?? $template->body_text,
        ]);
    }

    public function sendTest(Request $request, $id)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        $template = EmailTemplate::on('legacy')->findOrFail($id);
        $variables = $this->templateVariables[$template->key] ?? [];

        // Generate sample variables
        $sampleVariables = [];
        foreach ($variables as $key => $description) {
            $sampleVariables[$key] = $this->getSampleValue($key);
        }

        try {
            $preview = EmailTemplateService::getTemplate($template->key, $sampleVariables);

            Mail::mailer('postmark')->to($request->test_email)->send(
                new \Illuminate\Mail\Message(function ($message) use ($preview) {
                    $message->subject($preview['subject']);
                    $message->html($preview['html']);
                    $message->text($preview['text']);
                })
            );

            return redirect()->route('admin.email-templates.show', $id)
                ->with('success', "Test email sent to {$request->test_email}");
        } catch (\Exception $e) {
            return redirect()->route('admin.email-templates.show', $id)
                ->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    protected function getSampleValue(string $key): string
    {
        $samples = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'project_name' => 'Sample Project',
            'update_content' => 'This is a sample update message to demonstrate how the email template will look when rendered.',
            'update_date' => now()->format('d M Y'),
            'dashboard_url' => route('investor.dashboard'),
            'project_url' => route('investor.dashboard') . '#project-1',
            'login_url' => route('investor.login'),
            'reset_url' => route('investor.login') . '?token=sample-token',
            'expires_in' => '1 hour',
            'ticket_id' => 'TKT-12345',
            'subject' => 'Sample Support Request',
            'message' => 'This is a sample support ticket message.',
            'shared_by' => 'Jane Smith',
            'attachments_html' => '<ul><li><a href="#">Sample Document.pdf</a></li></ul>',
            'attachments_text' => '- Sample Document.pdf',
            'documents_list' => '<ul><li><a href="#">Document 1.pdf</a></li><li><a href="#">Document 2.pdf</a></li></ul>',
            'documents_list_text' => "- Document 1.pdf\n- Document 2.pdf",
        ];

        return $samples[$key] ?? 'Sample ' . str_replace('_', ' ', $key);
    }
}


