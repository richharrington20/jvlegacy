<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Log;

class EmailTemplateService
{
    /**
     * Get an email template by key and render it with variables
     *
     * @param string $key Template key (e.g., 'welcome_investor', 'password_reset')
     * @param array $variables Variables to replace in template (e.g., ['name' => 'John', 'url' => '...'])
     * @return array|null Returns ['subject' => string, 'html' => string, 'text' => string] or null if not found
     */
    public static function getTemplate(string $key, array $variables = []): ?array
    {
        try {
            $template = EmailTemplate::on('legacy')
                ->where('key', $key)
                ->where('deleted', 0)
                ->first();

            if (!$template) {
                Log::warning("Email template not found: {$key}");
                return null;
            }

            $subject = self::replaceVariables($template->subject, $variables);
            $html = self::replaceVariables($template->body_html, $variables);
            $text = self::replaceVariables($template->body_text, $variables);

            return [
                'subject' => $subject,
                'html' => $html,
                'text' => $text,
            ];
        } catch (\Exception $e) {
            Log::error("Error fetching email template {$key}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Replace variables in template string
     * Supports {{variable}} and {variable} syntax
     *
     * @param string $template
     * @param array $variables
     * @return string
     */
    protected static function replaceVariables(string $template, array $variables): string
    {
        $result = $template;

        foreach ($variables as $key => $value) {
            // Replace {{variable}} and {variable}
            $result = str_replace(['{{' . $key . '}}', '{' . $key . '}'], $value ?? '', $result);
        }

        return $result;
    }

    /**
     * Get default template if custom template not found
     *
     * @param string $key
     * @param array $variables
     * @return array
     */
    public static function getTemplateWithFallback(string $key, array $variables = []): array
    {
        $template = self::getTemplate($key, $variables);

        if ($template) {
            return $template;
        }

        // Fallback to basic template
        return [
            'subject' => 'Message from JaeVee',
            'html' => '<p>' . ($variables['content'] ?? 'You have a new message from JaeVee.') . '</p>',
            'text' => $variables['content'] ?? 'You have a new message from JaeVee.',
        ];
    }
}

