# Backend Architecture Rules

## Layered Architecture

### Controller Layer
- Controllers are **entry points only** - they coordinate the request/response cycle
- Controllers should NOT contain business logic
- Keep controllers thin - 10-20 lines per action maximum
- Responsibilities:
  - Validate input (via Form Requests)
  - Delegate to service layer
  - Return formatted responses (via API Resources)
  - Handle HTTP concerns (status codes, headers)

**Good Controller Example:**
```php
public function store(CreateEventRequest $request, EventService $eventService): JsonResponse
{
    $event = $eventService->createEvent($request->validated());

    return response()->json([
        'data' => new EventResource($event)
    ], 201);
}
```

**Bad Controller Example (too much logic):**
```php
public function store(Request $request): JsonResponse
{
    // ❌ Validation in controller
    $validated = $request->validate([...]);

    // ❌ Business logic in controller
    $event = Event::create($validated);
    $event->participants()->attach($request->user()->id);

    // ❌ External API call in controller
    Http::post('https://waha.api/send-message', [...]);

    // ❌ Direct model transformation
    return response()->json($event);
}
```

### Service Layer
- Services contain **all business logic**
- Services orchestrate operations across multiple models, repositories, and external services
- One service per domain/aggregate (EventService, ParticipantService, WhatsAppService)
- Services should be injected via dependency injection
- Services return domain objects (Models, Collections, DTOs) - NOT responses

**Service Structure:**
```php
namespace App\Services;

use App\Models\Event;
use App\Contracts\WhatsAppServiceInterface;
use Illuminate\Support\Collection;

class EventService
{
    public function __construct(
        private WhatsAppServiceInterface $whatsAppService,
        private ParticipantService $participantService
    ) {}

    public function createEvent(array $data): Event
    {
        $event = Event::create($data);

        $this->notifyGroupAboutNewEvent($event);

        return $event;
    }

    public function addParticipant(Event $event, int $userId): void
    {
        if ($event->isFull()) {
            throw new EventFullException();
        }

        $this->participantService->addToEvent($event, $userId);
        $this->whatsAppService->sendConfirmation($event, $userId);
    }

    private function notifyGroupAboutNewEvent(Event $event): void
    {
        $this->whatsAppService->sendToGroup(
            $event->group_id,
            "New event created: {$event->name}"
        );
    }
}
```

### Repository Pattern (Optional, Use When Needed)
- Use repositories for complex queries that are reused across services
- Repositories abstract database operations
- Keep simple CRUD in models/services - don't over-engineer
- Use repositories when:
  - Complex query logic needs reuse
  - Multiple data sources might be used
  - Testing requires easy mocking of data layer

**Repository Example:**
```php
namespace App\Repositories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;

class EventRepository
{
    public function findUpcomingByGroup(int $groupId): Collection
    {
        return Event::where('group_id', $groupId)
            ->where('date', '>', now())
            ->with(['participants', 'group'])
            ->orderBy('date')
            ->get();
    }

    public function findByDateRange(string $start, string $end): Collection
    {
        return Event::whereBetween('date', [$start, $end])
            ->with('participants')
            ->get();
    }
}
```

## Model Organization with Actions & Accessors

### Overview
To keep models clean and organized, use **trait-based patterns** to separate different types of model behavior:
- **Actions**: Imperative commands that modify model state
- **Accessors**: Read-only methods that return derived or formatted data

This pattern prevents bloated models and makes behavior explicit and testable.

**⚠️ IMPORTANT: This pattern is MANDATORY when applicable.**
- When adding methods that modify model state → Use Actions trait
- When adding methods that return derived/formatted data → Use Accessors trait
- Do NOT add these methods directly in the model class
- Keep model classes focused on: fillable/guarded, casts, relationships, and scopes only

### Model Actions Pattern

**Purpose**: Centralize executable behaviors that modify model attributes or state.

**Location**: `app/Models/Actions/`

**Naming Convention**: `{ModelName}ActionTrait`

#### Action Trait Conventions
- All methods must be `public`
- All methods must return `void`
- Methods **must throw exceptions** on failure (never return `false` or `null`)
- Methods should leave the model in a valid and persisted state when applicable
- Use imperative verb names: `initialize`, `activate`, `block`, `reset`, `configure`

#### When to Use Actions
- Initializing model attributes
- Applying state changes
- Configuring relationships
- Executing commands that alter the model

**Action Trait Example:**
```php
namespace App\Models\Actions;

use App\Exceptions\EventAlreadyStartedException;
use App\Jobs\SendWhatsAppMessageJob;

trait EventActionTrait
{
    /**
     * Initialize event with default settings
     */
    public function initializeDefaults(): void
    {
        if ($this->status !== null) {
            throw new \Exception('Event already initialized');
        }

        $this->status = EventStatus::Draft;
        $this->max_participants = $this->max_participants ?? 20;
        $this->save();
    }

    /**
     * Activate the event and notify participants
     */
    public function activate(): void
    {
        if ($this->status === EventStatus::Active) {
            throw new \Exception('Event is already active');
        }

        if ($this->date < now()) {
            throw new EventAlreadyStartedException();
        }

        $this->status = EventStatus::Active;
        $this->save();

        $this->notifyParticipants();
    }

    /**
     * Cancel the event and notify all participants
     */
    public function cancel(): void
    {
        if ($this->status === EventStatus::Completed) {
            throw new \Exception('Cannot cancel completed event');
        }

        $this->status = EventStatus::Cancelled;
        $this->save();

        SendWhatsAppMessageJob::dispatch(
            $this->group->whatsapp_id,
            "Event '{$this->name}' has been cancelled"
        );
    }

    /**
     * Block user from joining the event
     */
    public function blockUser(int $userId): void
    {
        if ($this->blocked_users()->where('user_id', $userId)->exists()) {
            throw new \Exception('User already blocked');
        }

        $this->blocked_users()->attach($userId);
        $this->participants()->detach($userId);
    }

    private function notifyParticipants(): void
    {
        foreach ($this->participants as $participant) {
            SendWhatsAppMessageJob::dispatch(
                $participant->phone,
                "Event '{$this->name}' is now active!"
            );
        }
    }
}
```

**Model Implementation:**
```php
namespace App\Models;

use App\Models\Actions\EventActionTrait;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use EventActionTrait;

    // Model definition...
}
```

**Usage in Services:**
```php
class EventService
{
    public function createEvent(array $data): Event
    {
        $event = Event::create($data);

        // Use action to initialize
        $event->initializeDefaults();

        return $event;
    }

    public function publishEvent(Event $event): void
    {
        // Use action to activate
        $event->activate();
    }
}
```

### Model Accessors Pattern

**Purpose**: Centralize methods that return derived, formatted, or composite values without side effects.

**Location**: `app/Models/Accessors/`

**Naming Convention**: `{ModelName}AccessorTrait`

#### Accessor Trait Conventions
- All methods must be `public`
- Methods must NOT have side effects
- Methods must return concrete data types (string, array, float, Collection, Model, etc)
- Use descriptive prefixes: `get`, `calculate`, `format`, `list`, `is`, `has`

#### When to Use Accessors
- Formatting data for display
- Calculating derived values
- Composing data from multiple attributes
- Building complex query results
- Checking state conditions

**Accessor Trait Example:**
```php
namespace App\Models\Accessors;

use Illuminate\Support\Collection;

trait EventAccessorTrait
{
    /**
     * Get formatted event date for display
     */
    public function getFormattedDate(): string
    {
        return $this->date->format('F j, Y \a\t g:i A');
    }

    /**
     * Get available spots remaining
     */
    public function getAvailableSpots(): int
    {
        return max(0, $this->max_participants - $this->participants()->count());
    }

    /**
     * Calculate event capacity percentage
     */
    public function calculateCapacityPercentage(): float
    {
        if ($this->max_participants === 0) {
            return 0.0;
        }

        return ($this->participants()->count() / $this->max_participants) * 100;
    }

    /**
     * Check if event is full
     */
    public function isFull(): bool
    {
        return $this->participants()->count() >= $this->max_participants;
    }

    /**
     * Check if event is accepting participants
     */
    public function isAcceptingParticipants(): bool
    {
        return $this->status === EventStatus::Active
            && !$this->isFull()
            && $this->date > now();
    }

    /**
     * Get list of confirmed participants
     */
    public function listConfirmedParticipants(): Collection
    {
        return $this->participants()
            ->wherePivot('confirmed', true)
            ->orderBy('event_participant.created_at')
            ->get();
    }

    /**
     * Format event summary for WhatsApp message
     */
    public function formatWhatsAppSummary(): string
    {
        $participants = $this->participants()->count();
        $available = $this->getAvailableSpots();

        return "📅 *{$this->name}*\n" .
               "📍 {$this->getFormattedDate()}\n" .
               "👥 {$participants}/{$this->max_participants} participants\n" .
               "🎯 {$available} spots available";
    }

    /**
     * Get event status badge color
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            EventStatus::Draft => 'gray',
            EventStatus::Active => 'green',
            EventStatus::Cancelled => 'red',
            EventStatus::Completed => 'blue',
        };
    }
}
```

**Model Implementation:**
```php
namespace App\Models;

use App\Models\Accessors\EventAccessorTrait;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use EventAccessorTrait;

    // Model definition...
}
```

**Usage in Services:**
```php
class EventService
{
    public function getEventDetails(Event $event): array
    {
        return [
            'name' => $event->name,
            'date' => $event->getFormattedDate(),
            'available_spots' => $event->getAvailableSpots(),
            'capacity_percentage' => $event->calculateCapacityPercentage(),
            'is_accepting' => $event->isAcceptingParticipants(),
            'participants' => $event->listConfirmedParticipants(),
        ];
    }

    public function sendEventSummary(Event $event): void
    {
        $summary = $event->formatWhatsAppSummary();

        SendWhatsAppMessageJob::dispatch(
            $event->group->whatsapp_id,
            $summary
        );
    }
}
```

### Actions vs Accessors: Quick Reference

| Aspect | Actions | Accessors |
|--------|---------|-----------|
| **Purpose** | Modify model state | Read/compute data |
| **Return Type** | `void` | Concrete types |
| **Side Effects** | Yes (saves, dispatches jobs) | No |
| **Exception Handling** | Throw on failure | Return safe defaults or throw |
| **Naming** | Verbs: `activate`, `cancel`, `block` | Prefixes: `get`, `calculate`, `is`, `format` |
| **Location** | `app/Models/Actions/` | `app/Models/Accessors/` |
| **Example** | `$event->activate()` | `$event->getAvailableSpots()` |

### Combining Both Patterns

Models can use both traits for complete behavior organization:

```php
namespace App\Models;

use App\Models\Actions\EventActionTrait;
use App\Models\Accessors\EventAccessorTrait;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use EventActionTrait;
    use EventAccessorTrait;

    // Only core Eloquent features remain in the model:
    // - Fillable/guarded
    // - Casts
    // - Relationships
    // - Scopes
}
```

### Testing Actions and Accessors

**Testing Actions:**
```php
public function test_activate_event_changes_status_and_notifies(): void
{
    Queue::fake();

    $event = Event::factory()->create([
        'status' => EventStatus::Draft,
        'date' => now()->addDay(),
    ]);

    $event->activate();

    $this->assertEquals(EventStatus::Active, $event->status);
    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'status' => EventStatus::Active,
    ]);

    Queue::assertPushed(SendWhatsAppMessageJob::class);
}

public function test_activate_throws_exception_when_already_active(): void
{
    $event = Event::factory()->create(['status' => EventStatus::Active]);

    $this->expectException(\Exception::class);

    $event->activate();
}
```

**Testing Accessors:**
```php
public function test_get_available_spots_returns_correct_count(): void
{
    $event = Event::factory()->create(['max_participants' => 10]);
    $event->participants()->attach(User::factory()->count(3)->create());

    $this->assertEquals(7, $event->getAvailableSpots());
}

public function test_is_full_returns_true_when_at_capacity(): void
{
    $event = Event::factory()->create(['max_participants' => 5]);
    $event->participants()->attach(User::factory()->count(5)->create());

    $this->assertTrue($event->isFull());
}

public function test_format_whatsapp_summary_includes_all_details(): void
{
    $event = Event::factory()->create([
        'name' => 'Soccer Match',
        'max_participants' => 10,
    ]);
    $event->participants()->attach(User::factory()->count(3)->create());

    $summary = $event->formatWhatsAppSummary();

    $this->assertStringContainsString('Soccer Match', $summary);
    $this->assertStringContainsString('3/10', $summary);
    $this->assertStringContainsString('7 spots available', $summary);
}
```

### Benefits of This Pattern

1. **Organized Models**: Models stay clean with only core Eloquent features
2. **Single Responsibility**: Each trait has one clear purpose
3. **Reusability**: Traits can be composed as needed
4. **Testability**: Easy to test actions and accessors in isolation
5. **Discoverability**: Clear naming makes methods easy to find
6. **Consistency**: Project-wide conventions for similar behaviors
7. **Type Safety**: Return types and void declarations prevent misuse

## SOLID Principles

### Single Responsibility Principle (SRP)
- Each class should have ONE reason to change
- Services should focus on one domain/aggregate
- Split large services into smaller, focused services

**Example:**
```php
// ✅ Good - Single responsibility
class WhatsAppMessageService
{
    public function sendMessage(string $to, string $message): void { }
    public function sendImage(string $to, string $imageUrl): void { }
}

class WhatsAppWebhookHandler
{
    public function handleIncomingMessage(array $payload): void { }
    public function handleStatusUpdate(array $payload): void { }
}

// ❌ Bad - Multiple responsibilities
class WhatsAppService
{
    public function sendMessage() { }
    public function handleWebhook() { }
    public function processQueue() { }
    public function analyzeMessage() { }
}
```

### Open/Closed Principle (OCP)
- Open for extension, closed for modification
- Use interfaces and abstract classes
- Extend behavior through inheritance or composition, not modification

### Liskov Substitution Principle (LSP)
- Subtypes must be substitutable for their base types
- Interfaces should be honored by all implementations
- Don't break contracts in child classes

### Interface Segregation Principle (ISP)
- Many specific interfaces are better than one general interface
- Clients shouldn't depend on interfaces they don't use

### Dependency Inversion Principle (DIP)
- Depend on abstractions (interfaces), not concretions
- High-level modules should not depend on low-level modules

## Interfaces & Contracts

### When to Use Interfaces
- When you need to swap implementations (e.g., different payment gateways)
- When mocking dependencies in tests
- When multiple implementations of the same behavior exist
- When defining contracts between layers

### Interface Location & Naming
- Store interfaces in `app/Contracts/` directory
- Name with `Interface` suffix: `WhatsAppServiceInterface`
- Or use Laravel convention without suffix in dedicated `Contracts` namespace

**Interface Example:**
```php
namespace App\Contracts;

interface WhatsAppServiceInterface
{
    public function sendMessage(string $sessionId, string $to, string $message): bool;

    public function sendImage(string $sessionId, string $to, string $imageUrl): bool;

    public function getSessionStatus(string $sessionId): array;
}
```

**Implementation:**
```php
namespace App\Services\WhatsApp;

use App\Contracts\WhatsAppServiceInterface;
use Illuminate\Support\Facades\Http;

class WahaWhatsAppService implements WhatsAppServiceInterface
{
    public function __construct(
        private string $baseUrl,
        private string $apiKey
    ) {}

    public function sendMessage(string $sessionId, string $to, string $message): bool
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}"
        ])->post("{$this->baseUrl}/api/sendText", [
            'session' => $sessionId,
            'chatId' => $to,
            'text' => $message,
        ]);

        return $response->successful();
    }

    // ... other methods
}
```

**Service Provider Binding:**
```php
namespace App\Providers;

use App\Contracts\WhatsAppServiceInterface;
use App\Services\WhatsApp\WahaWhatsAppService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(WhatsAppServiceInterface::class, function ($app) {
            return new WahaWhatsAppService(
                config('services.waha.base_url'),
                config('services.waha.api_key')
            );
        });
    }
}
```

## Design Patterns

### Strategy Pattern
- Use for interchangeable algorithms/behaviors
- Example: Different notification strategies (WhatsApp, Email, SMS)

### Factory Pattern
- Use for complex object creation
- Example: Creating different types of events based on sport type

### Observer Pattern
- Laravel Events & Listeners implement this
- Use for decoupled event handling
- Example: When event is created → notify participants, log, update stats

### Command Pattern
- Laravel Jobs implement this
- Use for queued operations
- Example: SendWhatsAppMessageJob, ProcessWebhookJob

## Laravel Events & Listeners

### When to Use Events
- When an action triggers multiple side effects
- When you want loose coupling between components
- When the same event might need different handlers in the future

**Event Example:**
```php
namespace App\Events;

use App\Models\Event;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Event $event) {}
}
```

**Listener Example:**
```php
namespace App\Listeners;

use App\Events\EventCreated;
use App\Services\WhatsAppService;

class NotifyGroupAboutEvent
{
    public function __construct(private WhatsAppService $whatsAppService) {}

    public function handle(EventCreated $event): void
    {
        $this->whatsAppService->sendToGroup(
            $event->event->group_id,
            "New event: {$event->event->name}"
        );
    }
}
```

**Usage in Service:**
```php
public function createEvent(array $data): Event
{
    $event = Event::create($data);

    event(new EventCreated($event));

    return $event;
}
```

## Data Transfer Objects (DTOs)

### When to Use DTOs
- When passing complex data between layers
- When you need type safety for structured data
- When API request/response shapes differ from models

**DTO Example:**
```php
namespace App\DataTransferObjects;

readonly class CreateEventData
{
    public function __construct(
        public string $name,
        public string $date,
        public int $maxParticipants,
        public int $groupId,
        public ?string $description = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            date: $data['date'],
            maxParticipants: $data['max_participants'],
            groupId: $data['group_id'],
            description: $data['description'] ?? null,
        );
    }
}
```

## Service Organization

### Directory Structure
```
app/
├── Contracts/              # Interfaces
│   ├── WhatsAppServiceInterface.php
│   └── PaymentGatewayInterface.php
├── Services/              # Service implementations
│   ├── EventService.php
│   ├── ParticipantService.php
│   ├── WhatsApp/
│   │   ├── WahaWhatsAppService.php
│   │   └── WhatsAppMessageFormatter.php
│   └── Payment/
│       ├── StripePaymentService.php
│       └── PaymentService.php
├── Models/
│   ├── Event.php
│   ├── User.php
│   ├── Actions/           # Model action traits
│   │   ├── EventActionTrait.php
│   │   └── UserActionTrait.php
│   └── Accessors/         # Model accessor traits
│       ├── EventAccessorTrait.php
│       └── UserAccessorTrait.php
├── Repositories/          # Optional - only when needed
│   └── EventRepository.php
├── DataTransferObjects/   # DTOs
│   └── CreateEventData.php
├── Events/                # Domain events
│   └── EventCreated.php
└── Listeners/             # Event listeners
    └── NotifyGroupAboutEvent.php
```

## Dependency Injection

### Constructor Injection (Preferred)
```php
class EventService
{
    public function __construct(
        private WhatsAppServiceInterface $whatsAppService,
        private EventRepository $eventRepository
    ) {}
}
```

### Method Injection (When Needed)
```php
public function store(CreateEventRequest $request, EventService $eventService)
{
    // $eventService is automatically injected
}
```

### Service Container Binding
- Bind interfaces to implementations in `AppServiceProvider`
- Use singleton when service should be shared
- Use bind for new instance each time

## Error Handling

### Custom Exceptions
- Create domain-specific exceptions in `app/Exceptions/`
- Extend Laravel's base exception or create custom ones
- Handle in `app/Exceptions/Handler.php`

**Custom Exception:**
```php
namespace App\Exceptions;

use Exception;

class EventFullException extends Exception
{
    public function __construct()
    {
        parent::__construct('The event is already full', 422);
    }
}
```

**Exception Handler:**
```php
public function register(): void
{
    $this->renderable(function (EventFullException $e, Request $request) {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    });
}
```

## Testing Services

### Service Test Structure
```php
namespace Tests\Feature\Services;

use App\Services\EventService;
use App\Models\Event;
use App\Models\User;
use Tests\TestCase;

class EventServiceTest extends TestCase
{
    private EventService $eventService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventService = app(EventService::class);
    }

    public function test_creates_event_successfully(): void
    {
        $user = User::factory()->create();

        $event = $this->eventService->createEvent([
            'name' => 'Soccer Match',
            'date' => '2024-12-25',
            'max_participants' => 10,
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertDatabaseHas('events', ['name' => 'Soccer Match']);
    }
}
```

## Code Quality Principles

### DRY (Don't Repeat Yourself)
- Extract repeated logic into methods, services, or helpers
- Create reusable components and utilities
- Use Laravel's built-in features (scopes, traits, macros)

### KISS (Keep It Simple, Stupid)
- Prefer simple solutions over complex ones
- Don't over-engineer - build what you need now
- Refactor when complexity is justified, not preemptively

### YAGNI (You Aren't Gonna Need It)
- Don't build features "just in case"
- Add abstraction when you have 2+ use cases, not before
- Start concrete, refactor to abstract when needed