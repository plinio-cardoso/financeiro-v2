# Refactor 01: Extract Transaction Filters Component

**Status:** ⏳ Pendente
**Priority:** Alta
**Estimated Impact:** ~30% payload reduction on filter changes

## Problem

Currently, the `TransactionList` component contains both filters and list rendering in a monolithic structure. When any filter changes, the entire component re-renders (filters + aggregates + table + rows), sending unnecessary HTML over the wire.

**Current Issues:**
- Filter UI embedded in main component (51 lines)
- Full page re-renders when filters change
- Large HTML payloads even for simple filter updates
- Poor performance when editing items

## Current Code Structure

### TransactionList.php
```php
class TransactionList extends Component
{
    // Filter properties
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public ?string $startDate = null;
    #[Url(history: true)] public ?string $endDate = null;
    #[Url(history: true)] public array $selectedTags = [];
    #[Url(history: true)] public ?string $filterStatus = null;
    #[Url(history: true)] public ?string $filterType = null;
    #[Url(history: true)] public ?string $filterRecurrence = null;

    // List logic, aggregates, modals, etc.
    // ...
}
```

### transaction-list.blade.php (lines 36-86)
Filter UI embedded directly in the component view.

## Solution: Separate Filters Component

Create dedicated `TransactionFilters` component that:
- Manages all filter state independently
- Persists filters in URL (bookmarkable)
- Dispatches events when filters change
- Only sends filter HTML when filters update

**Architecture:**
```
transactions/index.blade.php
├── <livewire:transaction-filters /> (isolated, URL state)
└── <livewire:transaction-list /> (listens to filter events)
```

When filters change:
1. User changes filter → TransactionFilters updates
2. TransactionFilters dispatches `filters-updated` event
3. TransactionList receives event, applies filters, re-renders
4. **Only list HTML returns** - filters stay untouched

## Implementation Steps

### Step 1: Create TransactionFilters Component

**File:** `app/Livewire/TransactionFilters.php`

```php
<?php

namespace App\Livewire;

use App\Services\TagService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class TransactionFilters extends Component
{
    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public ?string $startDate = null;

    #[Url(history: true)]
    public ?string $endDate = null;

    #[Url(history: true)]
    public array $selectedTags = [];

    #[Url(history: true)]
    public ?string $filterStatus = null;

    #[Url(history: true)]
    public ?string $filterType = null;

    #[Url(history: true)]
    public ?string $filterRecurrence = null;

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
        $this->startDate = null;
        $this->endDate = null;
        $this->selectedTags = [];
        $this->filterStatus = null;
        $this->filterType = null;
        $this->filterRecurrence = null;

        $this->dispatchFilters();
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return !empty($this->search)
            || !empty($this->startDate)
            || !empty($this->endDate)
            || !empty($this->selectedTags)
            || !empty($this->filterStatus)
            || !empty($this->filterType)
            || !empty($this->filterRecurrence);
    }

    #[Computed]
    public function tags()
    {
        return app(TagService::class)->getAllTags();
    }

    private function dispatchFilters(): void
    {
        $this->dispatch('filters-updated', filters: [
            'search' => $this->search,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'tags' => $this->selectedTags,
            'status' => $this->filterStatus,
            'type' => $this->filterType,
            'recurring' => $this->filterRecurrence,
        ]);
    }

    public function render()
    {
        return view('livewire.transaction-filters');
    }
}
```

### Step 2: Create TransactionFilters View

**File:** `resources/views/livewire/transaction-filters.blade.php`

```blade
<div x-data @tags-loaded.window="$store.tags.setTags($event.detail.tags)">
    {{-- Compact Filters Row --}}
    <div class="flex flex-wrap items-center gap-4 mb-8">
        {{-- Date Range Group --}}
        <div class="flex items-center bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 px-1">
            <input type="date" wire:model.live="startDate"
                class="bg-transparent border-none focus:ring-0 text-xs font-bold text-gray-600 dark:text-gray-400 py-2 px-3">
            <div class="w-px h-4 bg-gray-100 dark:bg-gray-700"></div>
            <input type="date" wire:model.live="endDate"
                class="bg-transparent border-none focus:ring-0 text-xs font-bold text-gray-600 dark:text-gray-400 py-2 px-3">
        </div>

        {{-- Status Filter --}}
        <div class="w-40">
            <x-custom-select wire:model.live="filterStatus" :options="[]" placeholder="Todos os Status"
                x-init="options = $store.options.statuses; $watch('$store.options.statuses', val => options = val)"
                class="!py-2 !text-xs !font-bold" />
        </div>

        {{-- Type Filter --}}
        <div class="w-40">
            <x-custom-select wire:model.live="filterType" :options="[]" placeholder="Todos os Tipos"
                x-init="options = $store.options.types; $watch('$store.options.types', val => options = val)"
                class="!py-2 !text-xs !font-bold" />
        </div>

        {{-- Recurrence Filter --}}
        <div class="w-44">
            <x-custom-select wire:model.live="filterRecurrence" :options="[
                ['value' => '', 'label' => 'Recorrência (Todos)'],
                ['value' => 'recurring', 'label' => 'Recorrentes'],
                ['value' => 'not_recurring', 'label' => 'Não recorrentes']
            ]" placeholder="Recorrência (Todos)" class="!py-2 !text-xs !font-bold" />
        </div>

        {{-- Tags Filter --}}
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

### Step 3: Refactor TransactionList Component

**File:** `app/Livewire/TransactionList.php`

**Changes:**
1. Remove all filter properties and `#[Url]` attributes
2. Add `public array $activeFilters = [];`
3. Add listener for `filters-updated` event
4. Update `getFilteredQuery()` to use `$activeFilters`

```php
class TransactionList extends Component
{
    use WithPagination;

    public ?int $editingTransactionId = null;
    public ?int $editingRecurringId = null;
    public int $modalCounter = 0;

    // Filter state (from event)
    public array $activeFilters = [];

    // Sorting
    #[Url(history: true)]
    public string $sortField = 'due_date';

    #[Url(history: true)]
    public string $sortDirection = 'asc';

    public int $perPage = 15;
    private $aggregatesCache = null;

    protected $listeners = [
        'filters-updated' => 'applyFilters',
        'transaction-updated' => 'refreshAggregates',
        'open-edit-modal' => 'openEditModal',
    ];

    public function applyFilters(array $filters): void
    {
        $this->activeFilters = $filters;
        $this->aggregatesCache = null; // Clear cache
        $this->resetPage();
    }

    #[Computed]
    public function transactions()
    {
        return $this->getFilteredQuery()->paginate($this->perPage);
    }

    // Update getFilteredQuery() to use $this->activeFilters
    private function getFilteredQuery()
    {
        return app(TransactionService::class)->getFilteredTransactions(
            auth()->id(),
            [
                'search' => $this->activeFilters['search'] ?? '',
                'start_date' => $this->activeFilters['start_date'] ?? null,
                'end_date' => $this->activeFilters['end_date'] ?? null,
                'tags' => $this->activeFilters['tags'] ?? [],
                'status' => $this->activeFilters['status'] ?? null,
                'type' => $this->activeFilters['type'] ?? null,
                'recurring' => $this->activeFilters['recurring'] ?? null,
                'sort_by' => $this->sortField,
                'sort_direction' => $this->sortDirection,
            ]
        );
    }

    // Remove: search, startDate, endDate, selectedTags, filterStatus, filterType, filterRecurrence
    // Remove: updatedSearch(), updatedStartDate(), etc. lifecycle hooks
    // Remove: tags() computed property
    // Keep: aggregates, modals, sorting logic
}
```

### Step 4: Update TransactionList View

**File:** `resources/views/livewire/transaction-list.blade.php`

**Remove lines 36-86** (entire filter section)

Keep:
- Alpine.js modal setup
- Search bar in list header
- Aggregates bar
- Table
- Modal

### Step 5: Update Transaction Index View

**File:** `resources/views/transactions/index.blade.php`

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Transações') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Transações</h2>
            </div>

            {{-- Filters Component (isolated) --}}
            <livewire:transaction-filters />

            {{-- List Component (isolated) --}}
            <livewire:transaction-list />
        </div>
    </div>
</x-app-layout>
```

## Files to Modify

### New Files:
- `app/Livewire/TransactionFilters.php`
- `resources/views/livewire/transaction-filters.blade.php`

### Modified Files:
- `app/Livewire/TransactionList.php`
- `resources/views/livewire/transaction-list.blade.php`
- `resources/views/transactions/index.blade.php`

## Testing Checklist

- [ ] Filters render correctly on page load
- [ ] URL state persists (refresh page, filters remain)
- [ ] Browser back/forward works with filters
- [ ] Changing any filter updates the list
- [ ] Only list component HTML returns (verify in network tab)
- [ ] Aggregates update when filters change
- [ ] Clear filters button works
- [ ] Tags filter works with multi-select
- [ ] Date range filter works
- [ ] Status/Type/Recurrence filters work
- [ ] Search works (3+ chars)
- [ ] Pagination resets when filters change
- [ ] Sorting still works
- [ ] Modal open/close still works
- [ ] Inline editing still works in rows

## Performance Verification

**Before:**
- Filter change → Full component re-render
- Payload size: ~50-80KB (filters + list + aggregates)
- Network: 200-400ms

**After:**
- Filter change → Only list component updates
- Payload size: ~35-50KB (list + aggregates only)
- Network: 150-250ms
- **Reduction: ~30% payload size**

**Verify in browser DevTools:**
1. Open Network tab
2. Filter by "Fetch/XHR"
3. Change a filter
4. Check response payload size
5. Confirm only list HTML is returned

## Related Tasks

- **Refactor 02:** Extract Recurring Transaction Filters (same pattern)
- **Refactor 03:** Performance optimizations (debouncing, caching)
- **Refactor 99:** Comprehensive tests (deferred)

## Success Criteria

✅ Filters isolated in separate component
✅ Only list re-renders when filters change
✅ URL state persists for all filters
✅ No functionality regressions
✅ Performance improved (measurable)
✅ Code cleaner and more maintainable
