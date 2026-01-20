# Phase 2: Consolidate Filtered Query Execution

**Status:** ✅ To Implement
**Priority:** High
**Estimated Impact:** ~50-100ms locally, ~200-300ms production

## Problem

The filtered query `getFilteredTransactions()` executes 4 separate times per Livewire update:

1. `transactions()` - paginate results
2. `totalCount()` - count ALL filtered transactions
3. `totalAmount()` - sum credits from ALL filtered transactions (clone 1)
4. `totalAmount()` - sum debits from ALL filtered transactions (clone 2)

**CRITICAL REQUIREMENT:** `totalCount` and `totalAmount` must reflect ALL filtered transactions, not just the current page.

## Current Code

### TransactionList.php (lines 45-67)

```php
#[Computed]
public function transactions()
{
    return $this->getFilteredQuery()->paginate($this->perPage);  // Query 1
}

#[Computed]
public function totalCount(): int
{
    return $this->getFilteredQuery()->count();  // Query 2
}

#[Computed]
public function totalAmount(): float
{
    $query = $this->getFilteredQuery();
    $credits = (clone $query)->where('type', 'credit')->sum('amount');  // Query 3
    $debits = (clone $query)->where('type', 'debit')->sum('amount');   // Query 4
    return $credits - $debits;
}
```

## Solution

Use a single aggregate query to calculate totals across ALL filtered transactions:

```php
private $aggregatesCache = null;

#[Computed]
public function transactions()
{
    return $this->getFilteredQuery()->paginate($this->perPage);
}

#[Computed]
public function totalCount(): int
{
    $this->loadAggregates();
    return $this->aggregatesCache->total_count ?? 0;
}

#[Computed]
public function totalAmount(): float
{
    $this->loadAggregates();
    return ($this->aggregatesCache->total_credits ?? 0) - ($this->aggregatesCache->total_debits ?? 0);
}

private function loadAggregates(): void
{
    if ($this->aggregatesCache === null) {
        // Single query to get ALL totals across ALL filtered transactions
        $this->aggregatesCache = $this->getFilteredQuery()
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(CASE WHEN type = "credit" THEN amount ELSE 0 END) as total_credits,
                SUM(CASE WHEN type = "debit" THEN amount ELSE 0 END) as total_debits
            ')
            ->first();
    }
}
```

## Files to Modify

1. `/app/Livewire/TransactionList.php` (lines 45-67)
   - Keep `transactions()` method unchanged
   - Modify `totalCount()` to use aggregates
   - Modify `totalAmount()` to use aggregates
   - Add private `$aggregatesCache` property
   - Add private `loadAggregates()` method

## Implementation Notes

### Why This Works
- Aggregates query uses same filters as `getFilteredQuery()`
- COUNT(*) gives total across ALL filtered transactions
- SUM with CASE statement calculates credits and debits in single query
- Results cached in component property during request lifecycle
- Livewire's `#[Computed]` caching ensures aggregates load only once per update

### Query Reduction
**Before:** 4 queries
1. Paginated results
2. Count query
3. Sum credits
4. Sum debits

**After:** 2 queries
1. Paginated results
2. Single aggregate query (count + sum credits + sum debits)

## Testing Checklist

- [ ] Total count displays correctly (all filtered transactions, not just page)
- [ ] Total amount displays correctly (all filtered transactions, not just page)
- [ ] Apply filters and verify totals update correctly
- [ ] Change pages and verify totals remain the same
- [ ] Clear filters and verify totals show all transactions
- [ ] Verify only 2 queries execute per Livewire update (Debugbar/Telescope)
- [ ] Test with large dataset (1000+ transactions) to verify performance
- [ ] Test with no transactions (verify 0 values display correctly)

## Performance Verification

**Before:**
- 4 separate queries per Livewire update
- ~200ms locally, ~800ms production

**After:**
- 2 queries per Livewire update (paginated + aggregates)
- ~150ms locally, ~600ms production
- **Savings:** ~50-100ms locally, ~200-300ms production

## Edge Cases to Test

1. **Empty results:** Verify totals show 0/0.00 when no transactions match filters
2. **All credits:** Verify positive total amount
3. **All debits:** Verify negative total amount
4. **Mixed credits/debits:** Verify correct calculation
5. **Large amounts:** Verify formatting with thousands/millions
6. **Pagination edge:** Verify totals on last page with partial results

## Rollback Plan

If issues occur:
1. Revert `totalCount()` and `totalAmount()` to use `getFilteredQuery()->count()` and clone queries
2. Remove `$aggregatesCache` property
3. Remove `loadAggregates()` method
