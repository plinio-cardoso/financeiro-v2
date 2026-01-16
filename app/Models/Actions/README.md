# Actions

This namespace groups traits with **imperative actions** that operate directly on the model. Each method represents a command that modifies state or executes internal side effects.

Each trait is specific to a model, such as:

- `EventActionTrait`
- `UserActionTrait`
- `GroupActionTrait`
- etc.

---

## ✅ Purpose

Centralize **executable behaviors** that modify model attributes or states in an explicit and safe manner.

Typical examples:

- Initialize attributes (`initializeDefaults`, `initializeSession`)
- Apply restrictions (`blockUser`, `unblockUser`)
- Configure relationships
- Change status (`activate`, `cancel`, `complete`)

---

## 🧠 Conventions

- Trait name: `{ModelName}ActionTrait`
- All methods must be:
    - `public`
    - Return `void`
- Methods **must NOT return values** (never `bool`, `array`, `null`, etc)
- Methods **must throw exceptions** if they cannot complete the operation
- Methods must **leave the model in a valid and persisted state**, when applicable
- Names must start with verbs (e.g., `initialize`, `block`, `activate`, `reset`)

---

## 🔥 Expected Behavior

If an action cannot be executed successfully, **it must throw an exception**, for example:

```php
throw new \Exception("Event is already active.");

// ❌ WRONG — actions should not return boolean values
if ($this->status === EventStatus::Active) return false;
```

## 🧪 Usage Example

```php
use App\Models\Actions\EventActionTrait;

class Event extends Model
{
    use EventActionTrait;
}

// Anywhere in your code:
$event->initializeDefaults();
$event->activate();
