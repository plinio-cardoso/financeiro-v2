# Code Quality Rules

## DRY (Don't Repeat Yourself)

### Eliminate Duplication
- If you write the same code twice, extract it into a method
- If you write the same method twice, extract it into a service or trait
- If you see similar patterns across services, create a base class or helper

### Common Duplication Sources
**Validation Logic**
```php
// ❌ Bad - Duplicated validation
public function storeEvent(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'date' => 'required|date|after:today',
    ]);
}

public function updateEvent(Request $request, Event $event)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'date' => 'required|date|after:today',
    ]);
}

// ✅ Good - Shared Form Request
class EventRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'date' => 'required|date|after:today',
        ];
    }
}
```

**Query Logic**
```php
// ❌ Bad - Duplicated queries
class EventController
{
    public function active()
    {
        return Event::where('status', 'active')
            ->where('date', '>', now())
            ->with('participants')
            ->get();
    }
}

class EventService
{
    public function getUpcoming()
    {
        return Event::where('status', 'active')
            ->where('date', '>', now())
            ->with('participants')
            ->get();
    }
}

// ✅ Good - Query scope in model
class Event extends Model
{
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>', now());
    }
}

// Usage
Event::active()->upcoming()->with('participants')->get();
```

**Response Formatting**
```php
// ❌ Bad - Duplicated response structure
return response()->json([
    'data' => $event,
    'message' => 'Event created successfully'
], 201);

// ✅ Good - API Resource
return response()->json([
    'data' => new EventResource($event)
], 201);
```

## Clean Code Principles

### Meaningful Names
```php
// ❌ Bad - Unclear names
public function get($id) { }
public function doStuff() { }
public function data() { }
$e = Event::find(1);
$arr = [...];

// ✅ Good - Descriptive names
public function findEventById(int $id): ?Event { }
public function sendParticipantConfirmation(): void { }
public function getUpcomingEvents(): Collection { }
$event = Event::find(1);
$participants = [...];
```

### Small Functions
- Functions should do ONE thing
- Aim for 5-15 lines per function
- If a function is longer than 30 lines, consider breaking it up
- Extract complex conditions into named methods

```php
// ❌ Bad - Too much in one function
public function addParticipant(Event $event, User $user)
{
    if ($event->participants->count() >= $event->max_participants) {
        if ($event->waitlist->count() < 50) {
            $event->waitlist()->create(['user_id' => $user->id]);
            $message = "You've been added to the waitlist";
        } else {
            throw new Exception('Waitlist is full');
        }
    } else {
        $event->participants()->attach($user->id);
        $message = "You've been added to the event";
    }

    Http::post('waha/send', ['to' => $user->phone, 'message' => $message]);
    Log::info("User {$user->id} added to event {$event->id}");
}

// ✅ Good - Broken into small, focused functions
public function addParticipant(Event $event, User $user): void
{
    if ($this->isEventFull($event)) {
        $this->addToWaitlist($event, $user);
        return;
    }

    $this->addAsParticipant($event, $user);
}

private function isEventFull(Event $event): bool
{
    return $event->participants->count() >= $event->max_participants;
}

private function addAsParticipant(Event $event, User $user): void
{
    $event->participants()->attach($user->id);
    $this->sendConfirmation($user, "You've been added to the event");
}

private function addToWaitlist(Event $event, User $user): void
{
    if ($event->waitlist->count() >= 50) {
        throw new WaitlistFullException();
    }

    $event->waitlist()->create(['user_id' => $user->id]);
    $this->sendConfirmation($user, "You've been added to the waitlist");
}

private function sendConfirmation(User $user, string $message): void
{
    SendWhatsAppMessageJob::dispatch($user->phone, $message);
}
```

### Comments & Documentation

**When to Comment**
- Complex business logic that isn't immediately obvious
- Non-obvious workarounds for bugs or limitations
- Important security or performance considerations
- PHPDoc blocks for public methods (especially services)

**When NOT to Comment**
- Don't comment what the code does - make the code self-explanatory
- Don't leave commented-out code - delete it (Git remembers)
- Don't add TODO comments - create issues or tasks instead

```php
// ❌ Bad comments
// Get the user
$user = User::find($id);

// Check if active
if ($user->isActive()) { }

// TODO: Fix this later
$amount = $price * 1.1; // Add tax

// ✅ Good comments
/**
 * Calculate final price including Brazilian ICMS tax.
 * Rate varies by state - using São Paulo rate (18%) as default.
 *
 * @see https://www.fazenda.sp.gov.br/icms
 */
private function calculateTotalWithTax(float $price, ?string $state = 'SP'): float
{
    $taxRate = $this->getStateTaxRate($state);
    return $price * (1 + $taxRate);
}
```

### Error Handling

**Use Specific Exceptions**
```php
// ❌ Bad - Generic exception
if ($event->isFull()) {
    throw new Exception('Event is full');
}

// ✅ Good - Domain exception
if ($event->isFull()) {
    throw new EventFullException($event);
}
```

**Fail Fast**
```php
// ❌ Bad - Nested conditions
public function process(Event $event)
{
    if ($event) {
        if ($event->isActive()) {
            if ($event->hasParticipants()) {
                // Process event
            }
        }
    }
}

// ✅ Good - Early returns
public function process(Event $event): void
{
    if (!$event) {
        throw new InvalidArgumentException('Event is required');
    }

    if (!$event->isActive()) {
        return;
    }

    if (!$event->hasParticipants()) {
        return;
    }

    // Process event
}
```

## Testing Standards

### Test Coverage Requirements
- All services must have feature tests
- All API endpoints must have feature tests
- Test happy path, error cases, and edge cases
- Aim for 80%+ code coverage on critical paths

### Test Structure
Use AAA pattern: Arrange, Act, Assert

```php
public function test_user_can_join_event_when_space_available(): void
{
    $event = Event::factory()->create(['max_participants' => 10]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson("/api/v1/events/{$event->id}/join");

    $response->assertStatus(200);
    $this->assertDatabaseHas('event_participants', [
        'event_id' => $event->id,
        'user_id' => $user->id,
    ]);
}
```

### Test Naming
- Use descriptive test names: `test_<action>_<condition>_<expected_result>`
- Be specific about what you're testing
- Don't abbreviate

```php
// ❌ Bad
public function test_event() { }
public function test_join() { }

// ✅ Good
public function test_user_can_join_event_when_space_available() { }
public function test_user_added_to_waitlist_when_event_full() { }
public function test_cannot_join_cancelled_event() { }
```

### Mock External Services
```php
public function test_sends_whatsapp_confirmation_when_user_joins(): void
{
    Http::fake([
        'waha.devlike.pro/*' => Http::response(['success' => true], 200)
    ]);

    $event = Event::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson("/api/v1/events/{$event->id}/join");

    Http::assertSent(function ($request) use ($user) {
        return $request->url() === 'https://waha.devlike.pro/api/sendText' &&
               $request['chatId'] === $user->phone;
    });
}
```

### Test Data
- Use factories for model creation
- Use faker for random data
- Create factory states for common scenarios

```php
// EventFactory.php
public function full(): Factory
{
    return $this->state(fn (array $attributes) => [
        'max_participants' => 10,
    ])->has(User::factory()->count(10), 'participants');
}

// Usage in tests
$event = Event::factory()->full()->create();
```

## Performance Best Practices

### N+1 Query Prevention
```php
// ❌ Bad - N+1 queries
$events = Event::all();
foreach ($events as $event) {
    echo $event->user->name; // Query per event
}

// ✅ Good - Eager loading
$events = Event::with('user')->get();
foreach ($events as $event) {
    echo $event->user->name; // No additional queries
}
```

### Chunking Large Datasets
```php
// ❌ Bad - Loads all into memory
Event::all()->each(function ($event) {
    $this->process($event);
});

// ✅ Good - Process in chunks
Event::chunk(100, function ($events) {
    foreach ($events as $event) {
        $this->process($event);
    }
});
```

### Caching
```php
// Cache expensive operations
public function getPopularEvents(): Collection
{
    return Cache::remember('popular_events', 3600, function () {
        return Event::withCount('participants')
            ->orderBy('participants_count', 'desc')
            ->limit(10)
            ->get();
    });
}

// Invalidate cache when data changes
public function createEvent(array $data): Event
{
    $event = Event::create($data);

    Cache::forget('popular_events');

    return $event;
}
```

## Laravel-Specific Best Practices

### Eloquent Relationships
Always define return types for relationships:

```php
class Event extends Model
{
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_participants')
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function waitlist(): HasMany
    {
        return $this->hasMany(Waitlist::class);
    }
}
```

### Use Query Scopes for Reusable Queries
```php
class Event extends Model
{
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>', now());
    }

    public function scopeFullyBooked($query)
    {
        return $query->whereColumn('participants_count', '>=', 'max_participants');
    }
}

// Usage
$events = Event::active()->upcoming()->get();
```

### Use Accessors & Mutators
```php
class Event extends Model
{
    // Accessor
    protected function formattedDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->date->format('M d, Y')
        );
    }

    // Mutator
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => ucwords(strtolower($value))
        );
    }
}
```

### Use Enums for Status Fields
```php
enum EventStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
}

class Event extends Model
{
    protected function casts(): array
    {
        return [
            'status' => EventStatus::class,
            'date' => 'datetime',
        ];
    }
}

// Usage
$event->status = EventStatus::Active;
if ($event->status === EventStatus::Active) { }
```

## Security Best Practices

### Always Validate Input
```php
// Use Form Requests for validation
class CreateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Event::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'date' => 'required|date|after:today',
            'max_participants' => 'required|integer|min:1|max:100',
        ];
    }
}
```

### Use Mass Assignment Protection
```php
class Event extends Model
{
    protected $fillable = [
        'name',
        'date',
        'max_participants',
        'description',
    ];

    protected $guarded = [
        'id',
        'user_id',
        'created_at',
        'updated_at',
    ];
}
```

### Authorization
```php
// Use policies for authorization
class EventPolicy
{
    public function update(User $user, Event $event): bool
    {
        return $user->id === $event->user_id;
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->id === $event->user_id || $user->isAdmin();
    }
}

// Use in controller
public function update(UpdateEventRequest $request, Event $event)
{
    $this->authorize('update', $event);

    // ...
}
```

## Code Review Checklist

Before submitting code for review:
- [ ] All tests pass
- [ ] Code follows PSR-12 standards (run Pint)
- [ ] No duplication - extracted common logic
- [ ] Meaningful variable and method names
- [ ] Functions are small and focused
- [ ] No N+1 queries
- [ ] External services are mocked in tests
- [ ] Validation rules are in Form Requests
- [ ] Authorization checks are in place
- [ ] Error handling is appropriate
- [ ] Model methods organized correctly:
  - [ ] State-modifying methods are in Actions traits
  - [ ] Derived/formatted data methods are in Accessors traits
  - [ ] Model class only contains: fillable/guarded, casts, relationships, scopes
- [ ] Documentation is updated (business docs if needed)
- [ ] No sensitive data in logs or responses
