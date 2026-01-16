# WhatsApp Integration Rules

## WAHA (WhatsApp HTTP API) Integration

### Core Principles
- **All WhatsApp communication is asynchronous** - Use queued jobs for all message sending
- **Never block the request/response cycle** - WhatsApp operations should never delay user responses
- **Webhook processing is event-driven** - Process incoming webhooks via Laravel Events
- **Retry failed messages** - Implement exponential backoff for failed deliveries
- **Log all WhatsApp interactions** - Essential for debugging and analytics

## Service Architecture

### WhatsApp Service Interface
All WhatsApp operations must go through the `WhatsAppServiceInterface`:

```php
namespace App\Contracts;

interface WhatsAppServiceInterface
{
    /**
     * Send a text message to a chat
     */
    public function sendMessage(string $sessionId, string $chatId, string $message): bool;

    /**
     * Send an image with optional caption
     */
    public function sendImage(string $sessionId, string $chatId, string $imageUrl, ?string $caption = null): bool;

    /**
     * Get session status
     */
    public function getSessionStatus(string $sessionId): array;

    /**
     * Get chat information
     */
    public function getChatInfo(string $sessionId, string $chatId): array;
}
```

### WAHA Service Implementation
```php
namespace App\Services\WhatsApp;

use App\Contracts\WhatsAppServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WahaWhatsAppService implements WhatsAppServiceInterface
{
    public function __construct(
        private string $baseUrl,
        private string $apiKey
    ) {}

    public function sendMessage(string $sessionId, string $chatId, string $message): bool
    {
        try {
            $response = $this->makeRequest('POST', '/api/sendText', [
                'session' => $sessionId,
                'chatId' => $chatId,
                'text' => $message,
            ]);

            Log::info('WhatsApp message sent', [
                'session' => $sessionId,
                'chat' => $chatId,
                'success' => $response->successful(),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message', [
                'session' => $sessionId,
                'chat' => $chatId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function makeRequest(string $method, string $endpoint, array $data = []): Response
    {
        return Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->$method("{$this->baseUrl}{$endpoint}", $data);
    }
}
```

### Service Provider Binding
```php
namespace App\Providers;

use App\Contracts\WhatsAppServiceInterface;
use App\Services\WhatsApp\WahaWhatsAppService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WhatsAppServiceInterface::class, function ($app) {
            return new WahaWhatsAppService(
                config('services.waha.base_url'),
                config('services.waha.api_key')
            );
        });
    }
}
```

## Queue Jobs for Message Sending

### CRITICAL: Never Send Messages Synchronously
All message sending MUST be queued to avoid blocking requests and enable retries.

```php
namespace App\Jobs;

use App\Contracts\WhatsAppServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // Seconds between retries

    public function __construct(
        private string $sessionId,
        private string $chatId,
        private string $message,
        private ?int $userId = null
    ) {}

    public function handle(WhatsAppServiceInterface $whatsAppService): void
    {
        $whatsAppService->sendMessage(
            $this->sessionId,
            $this->chatId,
            $this->message
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('WhatsApp message job failed permanently', [
            'session' => $this->sessionId,
            'chat' => $this->chatId,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

### Usage in Services
```php
// ✅ Correct - Queued
SendWhatsAppMessageJob::dispatch($sessionId, $chatId, $message);

// ✅ Correct - With delay
SendWhatsAppMessageJob::dispatch($sessionId, $chatId, $message)
    ->delay(now()->addMinutes(5));

// ❌ Wrong - Direct call (blocks request)
$whatsAppService->sendMessage($sessionId, $chatId, $message);
```

## Webhook Handling

### Webhook Controller
```php
namespace App\Http\Controllers\Api;

use App\Events\WhatsApp\MessageReceived;
use App\Events\WhatsApp\StatusUpdate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WahaWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $event = $request->input('event');
        $payload = $request->all();

        match ($event) {
            'message' => event(new MessageReceived($payload)),
            'message.ack' => event(new StatusUpdate($payload)),
            default => Log::info('Unknown WAHA webhook event', ['event' => $event])
        };

        return response()->json(['status' => 'ok']);
    }
}
```

### Webhook Events
```php
namespace App\Events\WhatsApp;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(public array $payload) {}

    public function getChatId(): string
    {
        return $this->payload['payload']['from'] ?? '';
    }

    public function getMessage(): string
    {
        return $this->payload['payload']['body'] ?? '';
    }

    public function getSessionId(): string
    {
        return $this->payload['session'] ?? '';
    }
}
```

### Webhook Event Listeners
```php
namespace App\Listeners\WhatsApp;

use App\Events\WhatsApp\MessageReceived;
use App\Services\BotCommandService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessIncomingMessage implements ShouldQueue
{
    public function __construct(
        private BotCommandService $botCommandService
    ) {}

    public function handle(MessageReceived $event): void
    {
        $message = $event->getMessage();
        $chatId = $event->getChatId();
        $sessionId = $event->getSessionId();

        $this->botCommandService->processMessage($sessionId, $chatId, $message);
    }
}
```

## Message Format & Templates

### Message Formatter Service
Create a dedicated service for message formatting to maintain consistency:

```php
namespace App\Services\WhatsApp;

class MessageFormatter
{
    public function eventCreated(Event $event): string
    {
        return "🎉 *New Event Created!*\n\n" .
               "📅 {$event->name}\n" .
               "📍 Date: {$event->formatted_date}\n" .
               "👥 Max Participants: {$event->max_participants}\n\n" .
               "Reply with '+1' or 'I'm in' to join!";
    }

    public function participantConfirmation(Event $event, int $position): string
    {
        return "✅ You're confirmed!\n\n" .
               "Event: {$event->name}\n" .
               "Position: #{$position}\n" .
               "Spots left: " . ($event->max_participants - $position);
    }

    public function waitlistAdded(Event $event, int $position): string
    {
        return "📋 Added to waitlist\n\n" .
               "Event: {$event->name}\n" .
               "Waitlist position: #{$position}\n\n" .
               "You'll be notified if a spot opens up!";
    }

    public function eventReminder(Event $event): string
    {
        return "⏰ *Event Reminder*\n\n" .
               "📅 {$event->name}\n" .
               "⏰ Tomorrow at {$event->time}\n" .
               "👥 {$event->participants_count} participants confirmed\n\n" .
               "See you there!";
    }
}
```

## Bot Command Processing

### Command Detection
Use AI or pattern matching to detect user intent:

```php
namespace App\Services;

use App\Services\AI\MessageAnalyzer;

class BotCommandService
{
    public function __construct(
        private MessageAnalyzer $messageAnalyzer,
        private EventService $eventService
    ) {}

    public function processMessage(string $sessionId, string $chatId, string $message): void
    {
        $intent = $this->messageAnalyzer->detectIntent($message);

        match ($intent) {
            'join_event' => $this->handleJoinEvent($sessionId, $chatId),
            'leave_event' => $this->handleLeaveEvent($sessionId, $chatId),
            'list_events' => $this->handleListEvents($sessionId, $chatId),
            default => null // Ignore unknown messages
        };
    }

    private function handleJoinEvent(string $sessionId, string $chatId): void
    {
        // Business logic here
        // Dispatch job to send response
        SendWhatsAppMessageJob::dispatch($sessionId, $chatId, $response);
    }
}
```

## Configuration

### WAHA Configuration
```php
// config/services.php
return [
    'waha' => [
        'base_url' => env('WAHA_BASE_URL', 'https://waha.devlike.pro'),
        'api_key' => env('WAHA_API_KEY'),
        'webhook_secret' => env('WAHA_WEBHOOK_SECRET'),
        'default_session' => env('WAHA_DEFAULT_SESSION', 'default'),
    ],
];
```

### Environment Variables
```env
WAHA_BASE_URL=https://waha.devlike.pro
WAHA_API_KEY=your_api_key_here
WAHA_WEBHOOK_SECRET=your_webhook_secret
WAHA_DEFAULT_SESSION=default
```

## Testing WhatsApp Integration

### Mock WAHA API
```php
namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendWhatsAppMessageJob;
use Tests\TestCase;

class WhatsAppIntegrationTest extends TestCase
{
    public function test_queues_whatsapp_message_on_event_creation(): void
    {
        Queue::fake();
        Http::fake();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/v1/events', [
                'name' => 'Soccer Match',
                'date' => '2024-12-25',
                'max_participants' => 10,
            ]);

        Queue::assertPushed(SendWhatsAppMessageJob::class);
    }

    public function test_processes_incoming_webhook_message(): void
    {
        Http::fake();

        $webhook = [
            'event' => 'message',
            'session' => 'default',
            'payload' => [
                'from' => '5511999999999@c.us',
                'body' => 'I want to join',
            ],
        ];

        $response = $this->postJson('/api/webhooks/waha', $webhook);

        $response->assertStatus(200);
    }
}
```

### Test Jobs Directly
```php
public function test_send_whatsapp_message_job_sends_message(): void
{
    Http::fake([
        'waha.devlike.pro/*' => Http::response(['success' => true], 200)
    ]);

    $job = new SendWhatsAppMessageJob(
        sessionId: 'default',
        chatId: '5511999999999@c.us',
        message: 'Test message'
    );

    $job->handle(app(WhatsAppServiceInterface::class));

    Http::assertSent(function ($request) {
        return $request->url() === 'https://waha.devlike.pro/api/sendText' &&
               $request['text'] === 'Test message';
    });
}
```

## Error Handling & Logging

### Log All Interactions
```php
// Log outgoing messages
Log::info('WhatsApp message queued', [
    'session' => $sessionId,
    'chat' => $chatId,
    'message_preview' => Str::limit($message, 50),
]);

// Log incoming webhooks
Log::info('WhatsApp webhook received', [
    'event' => $event,
    'session' => $sessionId,
    'from' => $chatId,
]);

// Log failures
Log::error('WhatsApp operation failed', [
    'operation' => 'sendMessage',
    'error' => $exception->getMessage(),
    'context' => compact('sessionId', 'chatId'),
]);
```

### Retry Strategy
- First retry: After 1 minute
- Second retry: After 5 minutes
- Third retry: After 15 minutes
- After 3 failures: Log to monitoring system and alert

### Dead Letter Queue
Monitor failed jobs and create alerts for:
- Repeated failures to same chat
- API authentication failures
- Network connectivity issues

## Security Considerations

### Webhook Authentication
```php
public function handle(Request $request): JsonResponse
{
    $signature = $request->header('X-Webhook-Signature');

    if (!$this->verifyWebhookSignature($signature, $request->getContent())) {
        Log::warning('Invalid webhook signature', ['ip' => $request->ip()]);
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Process webhook...
}

private function verifyWebhookSignature(?string $signature, string $payload): bool
{
    if (!$signature) {
        return false;
    }

    $expected = hash_hmac('sha256', $payload, config('services.waha.webhook_secret'));

    return hash_equals($expected, $signature);
}
```

### Rate Limiting
- Implement rate limiting on webhook endpoints
- Prevent spam/abuse from malicious actors
- Respect WAHA API rate limits

### Data Privacy
- Never log full phone numbers in production
- Mask sensitive data in logs: `5511****9999`
- Don't store message content longer than necessary
- Comply with LGPD (Brazilian data protection law)

## Best Practices Summary

✅ **DO:**
- Queue all message sending operations
- Use events/listeners for webhook processing
- Mock external API calls in tests
- Log all WhatsApp interactions
- Use dedicated message formatter service
- Implement retry logic with exponential backoff
- Validate webhook signatures
- Use dependency injection for WhatsApp service

❌ **DON'T:**
- Send messages synchronously (blocks requests)
- Process heavy logic in webhook controllers
- Make real API calls in tests
- Expose sensitive data in logs
- Skip error handling and retries
- Hardcode message templates in business logic
- Trust webhook data without validation