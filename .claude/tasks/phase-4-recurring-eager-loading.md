# Phase 4: Use Eager-Loaded Data in RecurringTransactionEdit

**Status:** ✅ To Implement (If Logic Remains Identical)
**Priority:** Medium
**Estimated Impact:** ~50-100ms reduction when editing recurring transactions

## Problem

RecurringTransactionEdit component eager-loads all transactions in `mount()`, but then the computed properties ignore this data and run separate database queries for counts.

## Current Code

### RecurringTransactionEdit.php (lines 41-95)

#### mount() Method
```php
public function mount(?int $recurringId = null): void
{
    if ($recurringId) {
        $this->recurring = RecurringTransaction::with('transactions')  // ✅ Loads all transactions
            ->where('user_id', auth()->id())
            ->findOrFail($recurringId);

        // ... rest of mount logic ...
    }
}
```

#### Computed Properties (Currently Run Separate Queries)
```php
#[Computed]
public function futureTransactionsCount(): int
{
    return Transaction::where('recurring_transaction_id', $this->recurring->id)
        ->where('due_date', '>=', now())
        ->where('status', 'pending')
        ->count();  // ❌ New query - ignores loaded data
}

#[Computed]
public function totalTransactionsCount(): int
{
    return Transaction::where('recurring_transaction_id', $this->recurring->id)
        ->count();  // ❌ Another new query
}
```

## Solution

Use Laravel Collection methods on the already-loaded transactions:

```php
#[Computed]
public function futureTransactionsCount(): int
{
    return $this->recurring->transactions
        ->where('due_date', '>=', now())
        ->where('status', 'pending')
        ->count();  // ✅ Uses loaded collection
}

#[Computed]
public function totalTransactionsCount(): int
{
    return $this->recurring->transactions->count();  // ✅ Uses loaded collection
}
```

## Files to Modify

1. `/app/Livewire/RecurringTransactionEdit.php` (lines 74-95)
   - Modify `futureTransactionsCount()` computed property
   - Modify `totalTransactionsCount()` computed property

## Important: Logic Verification

**CRITICAL:** This change will only be implemented if it produces IDENTICAL results to the current database queries.

### Verification Required
1. The `where()` method on collections filters in-memory (not database)
2. Date comparison: Verify `>=` works correctly on Carbon/DateTime objects in collections
3. Status comparison: Verify enum/string comparison works correctly
4. Count accuracy: Verify collection count matches database count

### Testing Before Commit
```php
// In tinker or test:
$recurring = RecurringTransaction::with('transactions')->find(1);

// Database query result:
$dbCount = Transaction::where('recurring_transaction_id', $recurring->id)
    ->where('due_date', '>=', now())
    ->where('status', 'pending')
    ->count();

// Collection result:
$collectionCount = $recurring->transactions
    ->where('due_date', '>=', now())
    ->where('status', 'pending')
    ->count();

// Must be identical:
dd($dbCount === $collectionCount);  // Should be true
```

## Implementation Notes

### Collection vs Query Builder Differences

**Collection `where()`:**
- Filters in-memory using loose comparison (`==` by default)
- Can use strict comparison with `whereStrict()`
- Date objects compared by value
- Enums compared by value

**Query Builder `where()`:**
- Filters in database using SQL
- Always strict comparison
- Dates converted to database format
- Enums converted to values

### Potential Issues

1. **Timezone differences:** If `now()` uses different timezone than database
2. **Soft deletes:** Collection includes soft-deleted if not excluded in eager load
3. **Lazy loading prevention:** If relationship isn't fully loaded

### Solution to Potential Issues

**Ensure eager loading is complete:**
```php
// In mount(), ensure ALL transactions are loaded:
$this->recurring = RecurringTransaction::with('transactions')
    ->where('user_id', auth()->id())
    ->findOrFail($recurringId);
```

**For date comparison safety, use Carbon:**
```php
$this->recurring->transactions
    ->filter(fn($t) => $t->due_date->gte(now()))  // Explicit comparison
    ->where('status', 'pending')
    ->count();
```

## Testing Checklist

- [ ] Edit recurring transaction form loads correctly
- [ ] `futureTransactionsCount` displays correct number
- [ ] `totalTransactionsCount` displays correct number
- [ ] Create new future transaction and verify count updates
- [ ] Mark pending transaction as paid and verify future count decreases
- [ ] Verify NO additional queries run for counts (Debugbar/Telescope)
- [ ] Compare results with database queries (manual verification)
- [ ] Test edge cases:
  - [ ] Recurring with 0 transactions
  - [ ] Recurring with all past transactions
  - [ ] Recurring with all future transactions
  - [ ] Recurring with mixed past/future transactions

## Performance Verification

**Before:**
- 1 query to load recurring with transactions (eager loading)
- 2 additional queries for counts
- Total: 3 queries, ~150-200ms

**After:**
- 1 query to load recurring with transactions (eager loading)
- 0 additional queries (uses loaded collection)
- Total: 1 query, ~100-150ms
- **Savings:** ~50-100ms

## Rollback Plan

If collection filtering produces different results than database queries:
1. Revert to original database query approach
2. Document why collection approach failed
3. Consider alternative optimizations (caching, fewer count queries, etc.)

## Approval Condition

**This phase will only be implemented if:**
- Testing confirms identical results between collection and database approaches
- No edge cases produce incorrect counts
- Logic remains consistent with current implementation
