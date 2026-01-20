# Phase 1: Cache Tags Query

**Status:** ✅ To Implement
**Priority:** High
**Estimated Impact:** ~100-150ms locally, ~300-500ms production

## Problem

Tags query runs on every Livewire update, even though tags rarely change. This causes unnecessary database queries.

## Current Code

### TransactionList.php (lines 87-91)
```php
#[Computed]
public function tags()
{
    return Tag::orderBy('name')->get();  // ❌ Query every update
}
```

### TransactionForm.php (lines 62-66)
```php
#[Computed]
public function tags()
{
    return Tag::orderBy('name')->get();  // ❌ Query every mount
}
```

## Solution

Add application-level caching with 1 week TTL (604800 seconds):

```php
#[Computed]
public function tags()
{
    return Cache::remember('user_tags', 604800, function () {
        return Tag::orderBy('name')->get();
    });
}
```

## Files to Modify

1. `/app/Livewire/TransactionList.php` (line 87-91)
2. `/app/Livewire/TransactionForm.php` (line 62-66)

## Cache Invalidation

The cache will be automatically cleared:
- After 1 week (604800 seconds)
- Manually via admin settings (see Phase 7)

### Future Enhancement
Add cache invalidation when tags are created/updated/deleted in TagService:

```php
// In TagService methods
Cache::forget('user_tags');
```

## Testing Checklist

- [ ] Tags load correctly in TransactionList
- [ ] Tags load correctly in TransactionForm
- [ ] Filter by tags still works
- [ ] Verify only 1 tags query on first load (using Debugbar/Telescope)
- [ ] Verify NO tags query on subsequent Livewire updates
- [ ] Create a tag and verify it appears after cache expires
- [ ] Manual cache clear works (after Phase 7 implementation)

## Performance Verification

**Before:**
- Tags query runs on every Livewire update
- ~50-100ms per query locally
- ~200-300ms per query in production

**After:**
- Tags query runs once per week (or until manual clear)
- Cached retrieval: <1ms
- Total savings: ~100-150ms locally, ~300-500ms production per request

## Related Phases

- **Phase 7:** Admin menu for manual cache clearing
