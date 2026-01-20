# Phase 7: Admin Menu for Manual Cache Clearing

**Status:** 🆕 New Requirement
**Priority:** Medium
**Estimated Effort:** 1-2 hours

## Requirement

Add an admin settings page with a button to manually clear the tags cache when needed.

## User Story

As an administrator, I want to manually clear the tags cache so that new/updated tags appear immediately without waiting for the 1-week cache expiration.

## Use Cases

1. **Tag created:** Admin creates a new tag → wants it to appear immediately in filters
2. **Tag updated:** Admin updates tag name/color → wants changes to reflect immediately
3. **Tag deleted:** Admin deletes a tag → wants it removed from filters immediately
4. **Troubleshooting:** Cache becomes stale or corrupted → admin can manually refresh

## Implementation Design

### 1. Create Admin Settings Page

**Location:** `/settings` or `/admin/settings`

**Layout:**
- New card in existing settings area (if exists)
- Or new dedicated admin section

**Structure:**
```
Settings
├── Profile
├── Security
└── 🆕 Cache Management  ← New section
```

### 2. Cache Management UI

**Design:**
```
┌─────────────────────────────────────────┐
│ Cache Management                        │
├─────────────────────────────────────────┤
│                                         │
│ Tags Cache                              │
│ ┌─────────────────────────────────────┐ │
│ │ Status: Cached                      │ │
│ │ Last cleared: Never                 │ │
│ │ Expires: 6 days, 23h remaining      │ │
│ │                                     │ │
│ │ [Clear Tags Cache]                  │ │
│ └─────────────────────────────────────┘ │
│                                         │
└─────────────────────────────────────────┘
```

### 3. Backend Implementation

**Create Livewire Component:**
```php
// app/Livewire/Admin/CacheManagement.php
namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class CacheManagement extends Component
{
    public function clearTagsCache(): void
    {
        Cache::forget('user_tags');

        $this->dispatch('notify',
            message: 'Tags cache cleared successfully!',
            type: 'success'
        );
    }

    public function render()
    {
        return view('livewire.admin.cache-management');
    }
}
```

**Create View:**
```blade
{{-- resources/views/livewire/admin/cache-management.blade.php --}}
<div>
    <x-section-title>
        <x-slot name="title">{{ __('Cache Management') }}</x-slot>
        <x-slot name="description">{{ __('Manage application caches.') }}</x-slot>
    </x-section-title>

    <div class="mt-5">
        <x-action-section>
            <x-slot name="title">{{ __('Tags Cache') }}</x-slot>

            <x-slot name="description">
                {{ __('Clear the tags cache to see newly created or updated tags immediately.') }}
            </x-slot>

            <x-slot name="content">
                <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Tags are cached for performance. If you create or update tags and don\'t see changes, clear this cache.') }}
                </div>

                <div class="mt-5">
                    <x-button wire:click="clearTagsCache" wire:loading.attr="disabled">
                        {{ __('Clear Tags Cache') }}
                    </x-button>
                </div>

                <div wire:loading wire:target="clearTagsCache" class="mt-2">
                    <span class="text-sm text-gray-500">Clearing cache...</span>
                </div>
            </x-slot>
        </x-action-section>
    </div>
</div>
```

### 4. Add Route

**In `routes/web.php`:**
```php
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // ... existing routes ...

    Route::get('/settings/cache', \App\Livewire\Admin\CacheManagement::class)
        ->name('settings.cache');
});
```

### 5. Add Navigation Link

**In settings navigation (wherever settings menu exists):**
```blade
<a href="{{ route('settings.cache') }}"
   class="...">
    <svg><!-- cache icon --></svg>
    Cache Management
</a>
```

## Files to Create

1. `/app/Livewire/Admin/CacheManagement.php` - Livewire component
2. `/resources/views/livewire/admin/cache-management.blade.php` - View
3. Add route to `/routes/web.php`
4. Add navigation link to settings menu

## Testing Checklist

- [ ] Navigate to `/settings/cache` page
- [ ] See "Clear Tags Cache" button
- [ ] Click button
- [ ] See success notification
- [ ] Verify tags cache is actually cleared:
  - [ ] In tinker: `Cache::has('user_tags')` returns `false` after clearing
  - [ ] Create a new tag
  - [ ] Go to transactions page
  - [ ] New tag appears in filter dropdown immediately
- [ ] Test authorization:
  - [ ] Only authenticated users can access
  - [ ] (Optional) Only admin users can access

## Authorization Considerations

### Option 1: All Authenticated Users
```php
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/settings/cache', CacheManagement::class);
});
```

### Option 2: Admin Only (If role system exists)
```php
Route::middleware(['auth:sanctum', 'verified', 'admin'])->group(function () {
    Route::get('/settings/cache', CacheManagement::class);
});
```

**Decision:** Start with Option 1 (all authenticated users). Can restrict later if needed.

## Future Enhancements

1. **Show cache status:**
   - "Cached" vs "Not Cached"
   - Last cleared timestamp
   - Expiration countdown

2. **More cache types:**
   - Clear all application cache
   - Clear specific user cache
   - Clear view cache
   - Clear config cache

3. **Auto-invalidation:**
   - Clear tags cache automatically when tag created/updated/deleted
   - Add cache clearing to TagService methods

4. **Cache statistics:**
   - Cache hit rate
   - Cache size
   - Most cached queries

## Related Phases

- **Phase 1:** Tags caching implementation (this provides the manual clearing)
- **Phase 3:** TransactionForm tags caching (benefits from manual clearing)

## Documentation Updates

Add to project documentation:
- Where to find cache management
- When to clear tags cache
- How long caches last
- What caches exist in the application

## User Communication

When deploying:
- Announce new cache management feature
- Explain when to use it
- Document in user guide

## Rollback Plan

If issues occur:
- Remove route
- Remove navigation link
- Component remains but inaccessible
- Cache clearing still works via tinker: `Cache::forget('user_tags')`
