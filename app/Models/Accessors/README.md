# Accessors

This namespace groups **model-specific traits** with methods that serve as **custom accessors**, auxiliary calculations, and derived formatting.

Each trait implements logic for accessing additional data, organized by model:

- `EventAccessorTrait`
- `UserAccessorTrait`
- `GroupAccessorTrait`
- etc.

---

## ✅ Purpose

Centralize methods that return **derived, formatted, or composite values** from the main model data, avoiding logic duplication in controllers and services.

---

## 🧠 Conventions

- Trait name must follow the pattern `{ModelName}AccessorTrait`.
- All methods must:
    - Be `public`.
    - NOT generate side effects.
    - Return concrete data (string, array, float, Collection, etc).
- Recommended prefixes: `get`, `calculate`, `list`, `format`, `is`, `has`, etc.

---

## 🧪 Usage Example

```php
use App\Models\Accessors\EventAccessorTrait;

class Event extends Model
{
    use EventAccessorTrait;
}

// Anywhere in your code:
$formattedDate = $event->getFormattedDate();
$availableSpots = $event->getAvailableSpots();
