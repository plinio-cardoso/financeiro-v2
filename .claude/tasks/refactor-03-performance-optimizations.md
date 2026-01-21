# Refactor 03: Performance Optimizations

**Status:** ⏳ Pendente
**Priority:** Média
**Estimated Impact:** Better UX, faster interactions, cleaner code

## Problem

After extracting filters (Refactor 01 & 02), there are still optimization opportunities:

1. **Search inputs** - Use Livewire's built-in debounce instead of Alpine.js timeout
2. **Loading states** - No visual feedback during async operations
3. **Modal counter pattern** - Hacky increment to force remount
4. **Query optimization** - Some queries could be more efficient

## Current Issues

### 1. Search Debouncing (Alpine.js)
Both components use custom Alpine.js debouncing:
```blade
<div x-data="{
    searchValue: @entangle('search').live,
    localSearch: '',
    timeout: null,
    handleInput() {
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => {
            if (this.localSearch.length >= 3 || this.localSearch === '') {
                this.searchValue = this.localSearch;
            }
        }, 500);
    }
}">
```

**Problem:** Complex, harder to maintain, mixes concerns

### 2. No Loading States
Users don't see feedback during:
- Filter changes
- List updates
- Aggregate calculations
- Inline edits

### 3. Modal Counter Pattern
```php
public int $modalCounter = 0;

public function openEditModal(int $id): void
{
    $this->editingTransactionId = $id;
    $this->modalCounter++; // Force remount
}
```

**Problem:** Hacky workaround, not idiomatic Livewire

### 4. Monthly Amount Calculation (Recurring)
Currently calculated at component level - could be in database query.

## Solutions

### 1. Replace Alpine Debouncing with Livewire

**Both Filter Components:**

Replace complex Alpine.js debouncing with Livewire's built-in:

```blade
{{-- OLD: Alpine.js custom debounce --}}
<div x-data="{ localSearch: '', timeout: null, ... }">
    <input type="text" x-model="localSearch" @input="handleInput()" ...>
</div>

{{-- NEW: Livewire built-in debounce --}}
<input type="text"
    wire:model.live.debounce.500ms="search"
    placeholder="Buscar (mín. 3 letras)..."
    class="..."
>
```

**Files to modify:**
- `resources/views/livewire/transaction-filters.blade.php`
- `resources/views/livewire/recurring-transaction-filters.blade.php`

**Note:** Remove search input from list views (it should only be in filters component)

### 2. Add Loading States

**a) Global Loading Indicator (Both List Components)**

Add loading overlay during filter changes and updates:

```blade
{{-- Add to transaction-list.blade.php and recurring-transaction-list.blade.php --}}
<div class="relative">
    {{-- Loading overlay --}}
    <div wire:loading
         wire:target="applyFilters,sort,gotoPage"
         class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 rounded-2xl">
        <div class="flex flex-col items-center gap-3">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#4ECDC4]"></div>
            <span class="text-xs font-bold text-gray-600 dark:text-gray-400">Atualizando...</span>
        </div>
    </div>

    {{-- Main Content Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-[2rem] ...">
        <!-- existing content -->
    </div>
</div>
```

**b) Filter-Specific Loading States**

Show which filter is being applied:

```blade
{{-- In filter components --}}
<div class="w-40">
    <x-custom-select wire:model.live="filterStatus" ... />
    <div wire:loading wire:target="filterStatus" class="absolute right-2 top-2">
        <div class="animate-spin rounded-full h-3 w-3 border border-[#4ECDC4] border-t-transparent"></div>
    </div>
</div>
```

**c) Inline Edit Loading (TransactionRow)**

Add loading state during field updates:

```blade
{{-- In transaction-row.blade.php --}}
<div class="relative">
    <input wire:model.blur="title" ... />
    <div wire:loading wire:target="updateField" class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 flex items-center justify-center">
        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-[#4ECDC4]"></div>
    </div>
</div>
```

### 3. Simplify Modal Counter Logic

**Remove hacky counter, use Livewire's reactive key:**

**TransactionList.php:**
```php
// REMOVE: public int $modalCounter = 0;

public function openEditModal(int $transactionId): void
{
    $this->editingTransactionId = $transactionId;
    // NO counter increment!
}

public function closeModal(): void
{
    $this->editingTransactionId = null;
    $this->editingRecurringId = null;
}
```

**transaction-list.blade.php:**
```blade
{{-- OLD: --}}
<livewire:transaction-form
    :transaction-id="$editingTransactionId"
    :key="'transaction-' . $modalCounter . '-' . ($editingTransactionId ?? 'new')"
/>

{{-- NEW: Component auto-remounts when transactionId changes --}}
<livewire:transaction-form
    :transaction-id="$editingTransactionId"
    :key="'transaction-form-' . ($editingTransactionId ?? 'new')"
/>
```

**Same pattern for RecurringTransactionList:**
```php
// REMOVE: public int $modalCounter = 0;

public function openEdit(int $recurringId): void
{
    $this->editingRecurringId = $recurringId;
}
```

```blade
<livewire:recurring-transaction-form
    :recurring-transaction-id="$editingRecurringId"
    :key="'recurring-form-' . ($editingRecurringId ?? 'new')"
/>
```

### 4. Optimize Aggregate Queries

**a) Use Database for Monthly Calculation (Recurring)**

Currently done in component - move to query:

```php
// In RecurringTransactionList::loadAggregates()

// BETTER: Single query with database calculation
$this->aggregatesCache = $query->selectRaw('
    COUNT(*) as total_count,
    SUM(CASE
        WHEN frequency = "weekly" THEN amount * 4
        WHEN frequency = "monthly" THEN amount
        WHEN frequency = "yearly" THEN amount / 12
        WHEN frequency = "custom" THEN amount / NULLIF(interval_value, 0)
        ELSE 0
    END) as total_monthly
')->first();
```

**b) Ensure Proper Indexing**

Add database indexes for commonly filtered/sorted columns:

**Migration (create if needed):**
```php
Schema::table('transactions', function (Blueprint $table) {
    $table->index('due_date');
    $table->index(['user_id', 'status']);
    $table->index(['user_id', 'type']);
    $table->index(['user_id', 'due_date']);
});

Schema::table('recurring_transactions', function (Blueprint $table) {
    $table->index('next_due_date');
    $table->index(['user_id', 'status']);
    $table->index(['user_id', 'type']);
    $table->index(['user_id', 'frequency']);
});
```

**c) Eager Load Tags (If Displaying in List)**

If showing tags in list, eager load to prevent N+1:

```php
// In TransactionList::getFilteredQuery()
return app(TransactionService::class)
    ->getFilteredTransactions(...)
    ->with('tags'); // Add if tags shown in list
```

### 5. Add Transition Effects

Make loading smoother with wire:transition:

```blade
{{-- Fade in/out list during updates --}}
<div wire:loading.class="opacity-50 transition-opacity duration-200"
     wire:target="applyFilters">
    <!-- table content -->
</div>
```

## Implementation Steps

### Step 1: Simplify Search Debouncing
1. Update `transaction-filters.blade.php`
2. Update `recurring-transaction-filters.blade.php`
3. Remove Alpine.js search logic
4. Use `wire:model.live.debounce.500ms`

### Step 2: Add Loading States
1. Add global loading overlay to list components
2. Add filter-specific loading indicators
3. Add inline edit loading states
4. Test visual feedback

### Step 3: Simplify Modal Counter
1. Remove `$modalCounter` from TransactionList
2. Remove `$modalCounter` from RecurringTransactionList
3. Update blade key bindings
4. Test modal open/close/remount

### Step 4: Optimize Queries
1. Move monthly calculation to database (recurring)
2. Create migration for indexes
3. Run migration
4. Add eager loading if needed
5. Test query performance

### Step 5: Add Transitions
1. Add wire:transition classes
2. Test smooth animations

## Files to Modify

### Filter Components:
- `resources/views/livewire/transaction-filters.blade.php`
- `resources/views/livewire/recurring-transaction-filters.blade.php`

### List Components:
- `app/Livewire/TransactionList.php`
- `app/Livewire/RecurringTransactionList.php`
- `resources/views/livewire/transaction-list.blade.php`
- `resources/views/livewire/recurring-transaction-list.blade.php`

### Row Component (optional):
- `resources/views/livewire/transaction-row.blade.php`

### Database (optional):
- Create migration: `add_indexes_to_transactions_tables.php`

## Testing Checklist

### Search Debouncing:
- [ ] Search still debounces (500ms)
- [ ] Min 3 chars requirement works
- [ ] No JavaScript errors
- [ ] Simpler code, same behavior

### Loading States:
- [ ] Global loading shows during filter changes
- [ ] Filter-specific loading shows on individual filters
- [ ] Inline edit loading shows during updates
- [ ] Loading indicators dismiss after completion
- [ ] No flickering or layout shift

### Modal Counter:
- [ ] Modal opens correctly
- [ ] Form remounts when editing different item
- [ ] No counter in code
- [ ] Simpler, cleaner implementation

### Query Optimization:
- [ ] Aggregates calculate correctly (recurring)
- [ ] Indexes created successfully
- [ ] Query performance improved (check with Debugbar/Telescope)
- [ ] No N+1 queries

### Transitions:
- [ ] Smooth fade in/out
- [ ] No jarring changes
- [ ] Professional feel

## Performance Verification

**Before:**
- Complex Alpine.js debouncing
- No loading feedback
- Hacky modal counter
- Aggregates in component
- Missing indexes

**After:**
- Native Livewire debouncing
- Professional loading states
- Clean Livewire patterns
- Aggregates in database
- Proper indexing

**Measurable Improvements:**
- Code simplicity: -20 lines Alpine.js
- User feedback: Loading states visible
- Query speed: +10-30% with indexes
- Developer experience: Cleaner, more maintainable

## Optional Enhancements

### 1. Skeleton Loading
Instead of spinner, show skeleton UI:

```blade
<div wire:loading wire:target="applyFilters">
    {{-- Skeleton table rows --}}
    <div class="animate-pulse space-y-2">
        <div class="h-12 bg-gray-200 dark:bg-gray-700 rounded"></div>
        <div class="h-12 bg-gray-200 dark:bg-gray-700 rounded"></div>
        <div class="h-12 bg-gray-200 dark:bg-gray-700 rounded"></div>
    </div>
</div>
```

### 2. Optimistic UI Updates
Update UI immediately, then sync with server:

```php
// In TransactionRow
public function markAsPaid(): void
{
    // Optimistic update
    $this->transaction->status = 'paid';

    // Then persist
    $this->transaction->markAsPaid();
}
```

### 3. Defer Loading Non-Critical Data
Use `wire:init` for non-critical data:

```blade
<div wire:init="loadAggregates">
    @if($aggregatesLoaded)
        <!-- show aggregates -->
    @else
        <!-- show skeleton -->
    @endif
</div>
```

## Related Tasks

- **Refactor 01:** Transaction filters extraction (prerequisite)
- **Refactor 02:** Recurring filters extraction (prerequisite)
- **Refactor 99:** Comprehensive tests (deferred)

## Success Criteria

✅ Search uses native Livewire debouncing
✅ Loading states provide visual feedback
✅ Modal counter removed (cleaner code)
✅ Queries optimized with database aggregates
✅ Indexes added for performance
✅ No functionality regressions
✅ Better UX and developer experience
✅ Code is simpler and more maintainable

## Notes

- All changes are non-breaking
- Can be implemented incrementally
- Focus on UX polish and code quality
- Builds on top of Refactor 01 & 02
