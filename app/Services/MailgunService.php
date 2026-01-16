<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailgunService
{
    private string $domain;

    private string $secret;

    private string $endpoint;

    public function __construct()
    {
        $this->domain = config('services.mailgun.domain');
        $this->secret = config('services.mailgun.secret');
        $this->endpoint = config('services.mailgun.endpoint', 'api.mailgun.net');
    }

    /**
     * Send email via Mailgun
     *
     * @param  array  $to  Array of recipient email addresses
     * @param  string  $subject  Email subject
     * @param  string  $view  Blade view name for email body
     * @param  array  $data  Data to pass to the view
     * @return bool True if successful, false if failed
     */
    public function send(array $to, string $subject, string $view, array $data = []): bool
    {
        try {
            $html = $this->buildEmailHtml($view, $data);

            $response = Http::withBasicAuth('api', $this->secret)
                ->asMultipart()
                ->post("https://{$this->endpoint}/v3/{$this->domain}/messages", [
                    [
                        'name' => 'from',
                        'contents' => config('mail.from.address'),
                    ],
                    [
                        'name' => 'to',
                        'contents' => implode(',', $to),
                    ],
                    [
                        'name' => 'subject',
                        'contents' => $subject,
                    ],
                    [
                        'name' => 'html',
                        'contents' => $html,
                    ],
                ]);

            if ($response->successful()) {
                Log::info('Email sent via Mailgun', [
                    'to' => $to,
                    'subject' => $subject,
                    'view' => $view,
                ]);

                return true;
            }

            Log::error('Failed to send email via Mailgun', [
                'to' => $to,
                'subject' => $subject,
                'view' => $view,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception sending email via Mailgun', [
                'to' => $to,
                'subject' => $subject,
                'view' => $view,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Build email HTML from Blade view
     */
    private function buildEmailHtml(string $view, array $data): string
    {
        return view($view, $data)->render();
    }
}
