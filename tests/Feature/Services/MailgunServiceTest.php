<?php

namespace Tests\Feature\Services;

use App\Services\MailgunService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class MailgunServiceTest extends TestCase
{
    use RefreshDatabase;

    private MailgunService $mailgunService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailgunService = app(MailgunService::class);

        // Create a simple test view for all tests
        View::addLocation(__DIR__);
        file_put_contents(__DIR__.'/test-email.blade.php', '<html><body>Test Email</body></html>');
    }

    protected function tearDown(): void
    {
        // Clean up test view
        if (file_exists(__DIR__.'/test-email.blade.php')) {
            unlink(__DIR__.'/test-email.blade.php');
        }

        parent::tearDown();
    }

    public function test_send_method_calls_mailgun_api(): void
    {
        Http::fake([
            'api.mailgun.net/*' => Http::response(['id' => 'test-id', 'message' => 'Queued. Thank you.'], 200),
        ]);

        $result = $this->mailgunService->send(
            ['test@example.com'],
            'Test Subject',
            'test-email'
        );

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.mailgun.net/v3') &&
                   str_contains($request->url(), '/messages');
        });
    }

    public function test_send_method_includes_correct_headers(): void
    {
        Http::fake([
            'api.mailgun.net/*' => Http::response(['id' => 'test-id'], 200),
        ]);

        $this->mailgunService->send(
            ['test@example.com'],
            'Test Subject',
            'test-email'
        );

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization');
        });
    }

    public function test_send_method_renders_view_correctly(): void
    {
        Http::fake([
            'api.mailgun.net/*' => Http::response(['id' => 'test-id'], 200),
        ]);

        $result = $this->mailgunService->send(
            ['test@example.com'],
            'Test Subject',
            'test-email'
        );

        $this->assertTrue($result);
    }

    public function test_send_method_returns_true_on_success(): void
    {
        Http::fake([
            'api.mailgun.net/*' => Http::response(['id' => 'test-id', 'message' => 'Queued'], 200),
        ]);

        $result = $this->mailgunService->send(
            ['test@example.com'],
            'Test Subject',
            'test-email'
        );

        $this->assertTrue($result);
    }

    public function test_send_method_returns_false_on_failure(): void
    {
        Http::fake([
            'api.mailgun.net/*' => Http::response(['message' => 'Invalid API Key'], 401),
        ]);

        $result = $this->mailgunService->send(
            ['test@example.com'],
            'Test Subject',
            'test-email'
        );

        $this->assertFalse($result);
    }

    public function test_send_method_logs_errors_on_failure(): void
    {
        Log::spy();

        Http::fake([
            'api.mailgun.net/*' => Http::response(['message' => 'Invalid API Key'], 401),
        ]);

        $this->mailgunService->send(
            ['test@example.com'],
            'Test Subject',
            'test-email'
        );

        Log::shouldHaveReceived('error')
            ->once()
            ->with('Failed to send email via Mailgun', \Mockery::type('array'));
    }

    public function test_send_method_handles_multiple_recipients(): void
    {
        Http::fake([
            'api.mailgun.net/*' => Http::response(['id' => 'test-id'], 200),
        ]);

        $result = $this->mailgunService->send(
            ['test1@example.com', 'test2@example.com'],
            'Test Subject',
            'test-email'
        );

        $this->assertTrue($result);
    }

    public function test_send_method_handles_exceptions(): void
    {
        Log::spy();

        Http::fake(function () {
            throw new \Exception('Network error');
        });

        $result = $this->mailgunService->send(
            ['test@example.com'],
            'Test Subject',
            'test-email'
        );

        $this->assertFalse($result);

        Log::shouldHaveReceived('error')
            ->once()
            ->with('Exception sending email via Mailgun', \Mockery::type('array'));
    }

    public function test_send_method_logs_successful_sends(): void
    {
        Log::spy();

        Http::fake([
            'api.mailgun.net/*' => Http::response(['id' => 'test-id'], 200),
        ]);

        $this->mailgunService->send(
            ['test@example.com'],
            'Test Subject',
            'test-email'
        );

        Log::shouldHaveReceived('info')
            ->once()
            ->with('Email sent via Mailgun', \Mockery::type('array'));
    }
}
