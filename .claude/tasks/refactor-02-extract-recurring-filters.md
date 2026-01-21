# Refactor 02: Extract Recurring Transaction Filters Component

**Status:** ⏳ Pendente
**Priority:** Alta
**Estimated Impact:** ~30% payload reduction + URL persistence + Tag filtering (new feature!)

## Problem

The `RecurringTransactionList` component has the same monolithic structure as transactions, but with additional missing features:
- **No URL state persistence** - filters reset on page refresh
- **No tag filtering** - tags exist on model but not exposed in UI
- **No aggregate caching** - recalculates on every update
- Full page re-renders when filters change

## Current Code Structure

### RecurringTransactionList.php
```php
class RecurringTransactionList extends Component
{
    // Filter properties (NO #[Url] attributes)
    public string $search = '';
    public ?string $filterStatus = null;
    public ?string $filterType = null;
    public ?string $filterFrequency = null;
    // Missing: selectedTags

    // List logic, aggregates, modals, etc.
    // ...
}
```

### recurring-transaction-list.blade.php (lines 19-48)
Filter UI embedded directly in view.

## Solution: Extract Filters + Add Missing Features

Create `RecurringTransactionFilters` component following the same pattern as `TransactionFilters`, but with enhancements:
- **Add URL state persistence** (bookmarkable filters)
- **Add tag filtering support** (currently missing!)
- Event-driven communication with list

**Architecture:**
```
recurring-transactions/index.blade.php
├── <livewire:recurring-transaction-filters /> (isolated, URL state, tags!)
└── <livewire:recurring-transaction-list /> (listens to filter events)
```

## Implementation Steps

### Step 1: Create RecurringTransactionFilters Component

**File:** `app/Livewire/RecurringTransactionFilters.php`

```php
<?php

namespace App\Livewire;

use App\Services\TagService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class RecurringTransactionFilters extends Component
{
    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public array $selectedTags = []; // NEW!

    #[Url(history: true)]
    public ?string $filterStatus = null;

    #[Url(history: true)]
    public ?string $filterType = null;

    #[Url(history: true)]
    public ?string $filterFrequency = null;

    public function mount(): void
    {
        $this->dispatch('tags-loaded', tags: $this->tags);
        $this->dispatchFilters();
    }

    public function updated($property): void
    {
        $this->dispatchFilters();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->selectedTags = [];
        $this->filterStatus = null;
        $this->filterType = null;
        $this->filterFrequency = null;

        $this->dispatchFilters();
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return !empty($this->search)
            || !empty($this->selectedTags)
            || !empty($this->filterStatus)
            || !empty($this->filterType)
            || !empty($this->filterFrequency);
    }

    #[Computed]
    public function tags()
    {
        return app(TagService::class)->getAllTags();
    }

    private function dispatchFilters(): void
    {
        $this->dispatch('recurring-filters-updated', filters: [
            'search' => $this->search,
            'tags' => $this->selectedTags,
            'status' => $this->filterStatus,
            'type' => $this->filterType,
            'frequency' => $this->filterFrequency,
        ]);
    }

    public function render()
    {
        return view('livewire.recurring-transaction-filters');
    }
}
```

### Step 2: Create RecurringTransactionFilters View

**File:** `resources/views/livewire/recurring-transaction-filters.blade.php`

```blade
<div x-data @tags-loaded.window="$store.tags.setTags($event.detail.tags)">
    {{-- Compact Filters Row --}}
    <div class="flex flex-wrap items-center gap-4 mb-8">
        {{-- Type Filter --}}
        <div class="w-40">
            <x-custom-select wire:model.live="filterType" :options="[]"
                x-init="options = $store.options.types"
                placeholder="Todos os Tipos" class="!py-2 !text-xs !font-bold" />
        </div>

        {{-- Status Filter --}}
        <div class="w-40">
            <x-custom-select wire:model.live="filterStatus" :options="[]"
                x-init="options = $store.options.recurringStatuses"
                placeholder="Todos os Status" class="!py-2 !text-xs !font-bold" />
        </div>

        {{-- Frequency Filter --}}
        <div class="w-44">
            <x-custom-select wire:model.live="filterFrequency" :options="[]"
                x-init="options = $store.options.frequencies"
                placeholder="Todas Frequências" class="!py-2 !text-xs !font-bold" />
        </div>

        {{-- Tags Filter (NEW!) --}}
        <div class="w-48">
            <x-multi-select wire:model.live="selectedTags" :options="[]" placeholder="Tags"
                x-init="options = $store.tags.list; $watch('$store.tags.list', val => options = val)"
                class="!py-2 !text-xs !font-bold" />
        </div>

        <button wire:click="clearFilters" @disabled(!$this->hasActiveFilters) @class([
            'text-xs font-bold uppercase tracking-widest ml-2 transition-colors',
            'text-gray-400 hover:text-[#4ECDC4] cursor-pointer' => $this->hasActiveFilters,
            'text-gray-300 dark:text-gray-600 cursor-not-allowed opacity-50' => !$this->hasActiveFilters,
        ])>
            Limpar filtros
        </button>
    </div>
</div>
```

### Step 3: Refactor RecurringTransactionList Component

**File:** `app/Livewire/RecurringTransactionList.php`

**Changes:**
1. Remove all filter properties
2. Add `public array $activeFilters = [];`
3. Add listener for `recurring-filters-updated` event
4. Add aggregate caching (currently missing!)
5. Update queries to use `$activeFilters`

```php
<?php

namespace App\Livewire;

use App\Models\RecurringTransaction;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class RecurringTransactionList extends Component
{
    use WithPagination;

    public ?int $editingRecurringId = null;
    public int $modalCounter = 0;

    // Filter state (from event)
    public array $activeFilters = [];

    // Sorting
    #[Url(history: true)]
    public string $sortField = 'next_due_date';

    #[Url(history: true)]
    public string $sortDirection = 'asc';

    public int $perPage = 15;

    // NEW: Aggregate caching
    private $aggregatesCache = null;

    protected $listeners = [
        'recurring-filters-updated' => 'applyFilters',
    ];

    public function applyFilters(array $filters): void
    {
        $this->activeFilters = $filters;
        $this->aggregatesCache = null; // Clear cache
        $this->resetPage();
    }

    #[Computed]
    public function recurringTransactions()
    {
        $query = RecurringTransaction::query()
            ->where('user_id', auth()->id());

        // Apply filters from activeFilters
        if (!empty($this->activeFilters['search']) && strlen($this->activeFilters['search']) >= 3) {
            $query->where('title', 'like', '%' . $this->activeFilters['search'] . '%');
        }

        if (!empty($this->activeFilters['type'])) {
            $query->where('type', $this->activeFilters['type']);
        }

        if (!empty($this->activeFilters['status'])) {
            $query->where('status', $this->activeFilters['status']);
        }

        if (!empty($this->activeFilters['frequency'])) {
            $query->where('frequency', $this->activeFilters['frequency']);
        }

        // NEW: Tag filtering support
        if (!empty($this->activeFilters['tags'])) {
            $query->whereHas('tags', function ($q) {
                $q->whereIn('tags.id', $this->activeFilters['tags']);
            });
        }

        // Sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    #[Computed]
    public function totalCount(): int
    {
        $this->loadAggregates();
        return $this->aggregatesCache->total_count ?? 0;
    }

    #[Computed]
    public function totalMonthlyAmount(): float
    {
        $this->loadAggregates();
        return $this->aggregatesCache->total_monthly ?? 0;
    }

    // NEW: Aggregate caching
    private function loadAggregates(): void
    {
        if ($this->aggregatesCache !== null) {
            return;
        }

        // Build base query with same filters
        $query = RecurringTransaction::query()
            ->where('user_id', auth()->id());

        // Apply same filters as main query
        if (!empty($this->activeFilters['search']) && strlen($this->activeFilters['search']) >= 3) {
            $query->where('title', 'like', '%' . $this->activeFilters['search'] . '%');
        }

        if (!empty($this->activeFilters['type'])) {
            $query->where('type', $this->activeFilters['type']);
        }

        if (!empty($this->activeFilters['status'])) {
            $query->where('status', $this->activeFilters['status']);
        }

        if (!empty($this->activeFilters['frequency'])) {
            $query->where('frequency', $this->activeFilters['frequency']);
        }

        if (!empty($this->activeFilters['tags'])) {
            $query->whereHas('tags', function ($q) {
                $q->whereIn('tags.id', $this->activeFilters['tags']);
            });
        }

        // Calculate aggregates with monthly conversion
        $this->aggregatesCache = $query->selectRaw('
            COUNT(*) as total_count,
            SUM(CASE
                WHEN frequency = "weekly" THEN amount * 4
                WHEN frequency = "monthly" THEN amount
                WHEN frequency = "yearly" THEN amount / 12
                WHEN frequency = "custom" THEN amount / interval_value
                ELSE 0
            END) as total_monthly
        ')->first();
    }

    // Remove: search, filterType, filterStatus, filterFrequency properties
    // Remove: updatedXxx() lifecycle hooks
}
```

### Step 4: Update RecurringTransactionList View

**File:** `resources/views/livewire/recurring-transaction-list.blade.php`

**Remove lines 19-48** (entire filter section)

Keep:
- Alpine.js modal setup
- Search bar in list header
- Aggregates bar
- Table
- Modal

### Step 5: Update Recurring Transactions Index View

**File:** `resources/views/recurring-transactions/index.blade.php`

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Recorrências') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Recorrências</h2>
            </div>

            {{-- Filters Component (isolated) --}}
            <livewire:recurring-transaction-filters />

            {{-- List Component (isolated) --}}
            <livewire:recurring-transaction-list />
        </div>
    </div>
</x-app-layout>
```

## Files to Modify

### New Files:
- `app/Livewire/RecurringTransactionFilters.php`
- `resources/views/livewire/recurring-transaction-filters.blade.php`

### Modified Files:
- `app/Livewire/RecurringTransactionList.php`
- `resources/views/livewire/recurring-transaction-list.blade.php`
- `resources/views/recurring-transactions/index.blade.php`

## Testing Checklist

### Filter Functionality:
- [ ] All existing filters work (type, status, frequency)
- [ ] **NEW: Tag filtering works** (multi-select)
- [ ] URL state persists (refresh page, filters remain)
- [ ] Browser back/forward works with filters
- [ ] Clear filters button works

### List Behavior:
- [ ] Changing any filter updates the list
- [ ] Only list component HTML returns (verify in network tab)
- [ ] Aggregates update correctly (total count + monthly amount)
- [ ] Pagination resets when filters change
- [ ] Sorting still works

### Performance:
- [ ] Aggregates calculated once per filter change (not multiple queries)
- [ ] Monthly amount conversion correct for all frequencies
- [ ] Search works (3+ chars minimum)

### Modal & Editing:
- [ ] Modal open/close works
- [ ] Edit recurring transaction works
- [ ] Edit scope (future_only / current_and_future) works

## Performance Verification

**Before:**
- No URL persistence - filters lost on refresh
- No tag filtering - feature missing
- Aggregates recalculated on every property access
- Filter change → Full component re-render
- Payload: ~40-60KB

**After:**
- URL persistence - filters bookmarkable
- Tag filtering - new feature!
- Aggregates cached - single query
- Filter change → Only list updates
- Payload: ~30-40KB
- **Reduction: ~30% + new features**

**Verify in browser DevTools:**
1. Change filter → check only list HTML returns
2. Refresh page → filters persist from URL
3. Filter by tags → verify it works
4. Check Network tab for aggregate queries (should be 1, not multiple)

## New Features Added

### 1. Tag Filtering
Users can now filter recurring transactions by tags - previously tags existed on the model but weren't exposed in the UI.

**Usage:**
1. Open recurring transactions page
2. Use Tags multi-select filter
3. Select one or more tags
4. List updates to show only recurring transactions with selected tags

### 2. URL State Persistence
All filters now persist in the URL, making views:
- **Bookmarkable** - Save filtered view
- **Shareable** - Send link with specific filters
- **Browser-friendly** - Back/forward buttons work

**Example URL:**
```
/recurring-transactions?filterType=debit&filterStatus=active&selectedTags[]=1&selectedTags[]=3
```

### 3. Aggregate Caching
Monthly amount and count calculations now run once per filter change instead of on every computed property access.

## Related Tasks

- **Refactor 01:** Extract Transaction Filters (same pattern, done first)
- **Refactor 03:** Performance optimizations (debouncing, loading states)
- **Refactor 99:** Comprehensive tests (deferred)

## Success Criteria

✅ Filters isolated in separate component
✅ Only list re-renders when filters change
✅ URL state persists for all filters (NEW!)
✅ Tag filtering works (NEW!)
✅ Aggregate caching prevents redundant queries (NEW!)
✅ No functionality regressions
✅ Performance improved
✅ Parity with transactions page achieved
