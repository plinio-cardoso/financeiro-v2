# Refactor 99: Comprehensive Testing Suite

**Status:** ⏸️ DEFERRED (Final Phase)
**Priority:** Alta (quando executar)
**Estimated Impact:** Ensure all refactoring changes work correctly, prevent regressions

## Problem

After completing Refactor 01, 02, and 03, we need comprehensive tests to:
- Ensure all functionality works as expected
- Prevent regressions in future changes
- Document expected behavior
- Enable confident refactoring

Currently, tests likely need updates for:
- New filter components
- Event-driven communication
- URL state persistence
- Tag filtering in recurring transactions
- Aggregate caching logic

## Scope

This task covers testing for ALL refactoring phases:

### Refactor 01: Transaction Filters
- Filter component tests
- Event communication tests
- URL state persistence tests
- Integration with list component

### Refactor 02: Recurring Filters
- Filter component tests (including new tag filtering)
- Event communication tests
- URL state persistence tests
- Aggregate caching tests
- Integration with list component

### Refactor 03: Performance Optimizations
- Loading state tests
- Debouncing tests
- Modal remounting tests
- Query optimization verification

## Test Categories

### 1. Component Tests (Livewire)

#### TransactionFilters Component
```php
// tests/Feature/Livewire/TransactionFiltersTest.php

public function test_filters_dispatch_event_on_change(): void
public function test_filters_persist_in_url(): void
public function test_clear_filters_resets_all_values(): void
public function test_has_active_filters_computed_property(): void
public function test_tags_loaded_on_mount(): void
public function test_search_dispatches_event(): void
public function test_date_range_dispatches_event(): void
public function test_status_filter_dispatches_event(): void
public function test_type_filter_dispatches_event(): void
public function test_tag_filter_dispatches_event(): void
```

#### RecurringTransactionFilters Component
```php
// tests/Feature/Livewire/RecurringTransactionFiltersTest.php

public function test_filters_dispatch_event_on_change(): void
public function test_filters_persist_in_url(): void
public function test_clear_filters_resets_all_values(): void
public function test_has_active_filters_computed_property(): void
public function test_tag_filtering_works(): void // NEW FEATURE
public function test_search_dispatches_event(): void
public function test_frequency_filter_dispatches_event(): void
```

#### TransactionList Component
```php
// tests/Feature/Livewire/TransactionListTest.php

public function test_applies_filters_from_event(): void
public function test_resets_pagination_on_filter_change(): void
public function test_clears_aggregate_cache_on_filter_change(): void
public function test_aggregates_calculated_once_per_filter_change(): void
public function test_sorting_works_with_filters(): void
public function test_pagination_works_with_filters(): void
public function test_modal_opens_and_closes(): void
public function test_transactions_filtered_by_search(): void
public function test_transactions_filtered_by_date_range(): void
public function test_transactions_filtered_by_status(): void
public function test_transactions_filtered_by_type(): void
public function test_transactions_filtered_by_tags(): void
public function test_transactions_filtered_by_recurrence(): void
public function test_multiple_filters_work_together(): void
```

#### RecurringTransactionList Component
```php
// tests/Feature/Livewire/RecurringTransactionListTest.php

public function test_applies_filters_from_event(): void
public function test_resets_pagination_on_filter_change(): void
public function test_clears_aggregate_cache_on_filter_change(): void
public function test_aggregates_calculated_once_per_filter_change(): void
public function test_monthly_amount_calculation_correct(): void
public function test_sorting_works_with_filters(): void
public function test_pagination_works_with_filters(): void
public function test_modal_opens_and_closes(): void
public function test_recurring_transactions_filtered_by_search(): void
public function test_recurring_transactions_filtered_by_status(): void
public function test_recurring_transactions_filtered_by_type(): void
public function test_recurring_transactions_filtered_by_frequency(): void
public function test_recurring_transactions_filtered_by_tags(): void // NEW
public function test_multiple_filters_work_together(): void
```

### 2. Integration Tests

#### Filter + List Communication
```php
// tests/Feature/Integration/FilterListCommunicationTest.php

public function test_transaction_filters_update_list(): void
{
    Livewire::test(TransactionFilters::class)
        ->set('search', 'grocery')
        ->assertDispatched('filters-updated', filters: [
            'search' => 'grocery',
            // ...
        ]);

    // Verify list receives event
    Livewire::test(TransactionList::class)
        ->dispatch('filters-updated', filters: ['search' => 'grocery'])
        ->assertSet('activeFilters.search', 'grocery');
}

public function test_recurring_filters_update_list(): void
{
    // Similar for recurring transactions
}

public function test_filter_changes_reset_pagination(): void
public function test_filter_changes_clear_cache(): void
```

#### URL State Persistence
```php
// tests/Feature/Integration/UrlStatePersistenceTest.php

public function test_transaction_filters_persist_in_url(): void
{
    $this->get('/transactions?search=grocery&filterType=debit')
        ->assertSeeLivewire(TransactionFilters::class)
        ->assertSet('search', 'grocery')
        ->assertSet('filterType', 'debit');
}

public function test_recurring_filters_persist_in_url(): void
{
    $this->get('/recurring-transactions?filterFrequency=monthly')
        ->assertSeeLivewire(RecurringTransactionFilters::class)
        ->assertSet('filterFrequency', 'monthly');
}

public function test_url_changes_update_filters(): void
public function test_browser_back_restores_filters(): void
```

### 3. Feature Tests (End-to-End)

#### Transaction List Workflow
```php
// tests/Feature/TransactionListWorkflowTest.php

public function test_user_can_filter_and_view_transactions(): void
{
    $user = User::factory()->create();
    $transactions = Transaction::factory()->count(20)->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get('/transactions')
        ->assertSeeLivewire(TransactionFilters::class)
        ->assertSeeLivewire(TransactionList::class);

    Livewire::actingAs($user)
        ->test(TransactionFilters::class)
        ->set('filterType', 'debit')
        ->assertDispatched('filters-updated');

    // Verify list shows only debits
    Livewire::actingAs($user)
        ->test(TransactionList::class)
        ->dispatch('filters-updated', filters: ['type' => 'debit'])
        ->assertSee($transactions->where('type', 'debit')->first()->title)
        ->assertDontSee($transactions->where('type', 'credit')->first()->title);
}

public function test_user_can_clear_filters(): void
public function test_user_can_sort_transactions(): void
public function test_user_can_paginate_transactions(): void
public function test_user_can_search_transactions(): void
public function test_user_can_filter_by_multiple_criteria(): void
```

#### Recurring Transaction List Workflow
```php
// tests/Feature/RecurringTransactionListWorkflowTest.php

public function test_user_can_filter_recurring_by_tags(): void
{
    $user = User::factory()->create();
    $tag = Tag::factory()->create();

    $recurring = RecurringTransaction::factory()
        ->create(['user_id' => $user->id]);
    $recurring->tags()->attach($tag);

    $recurringWithoutTag = RecurringTransaction::factory()
        ->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(RecurringTransactionFilters::class)
        ->set('selectedTags', [$tag->id])
        ->assertDispatched('recurring-filters-updated');

    Livewire::actingAs($user)
        ->test(RecurringTransactionList::class)
        ->dispatch('recurring-filters-updated', filters: ['tags' => [$tag->id]])
        ->assertSee($recurring->title)
        ->assertDontSee($recurringWithoutTag->title);
}

public function test_monthly_amount_aggregation_correct(): void
public function test_user_can_filter_by_frequency(): void
```

### 4. Performance Tests (Optional)

#### Query Optimization
```php
// tests/Performance/QueryOptimizationTest.php

public function test_aggregates_query_runs_once(): void
{
    DB::enableQueryLog();

    $user = User::factory()->create();
    Transaction::factory()->count(50)->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(TransactionList::class)
        ->dispatch('filters-updated', filters: [])
        ->call('totalCount')
        ->call('totalAmount');

    $queries = DB::getQueryLog();

    // Aggregate query should run once, not twice
    $aggregateQueries = collect($queries)->filter(function ($query) {
        return str_contains($query['query'], 'COUNT') || str_contains($query['query'], 'SUM');
    });

    $this->assertCount(1, $aggregateQueries);
}

public function test_no_n_plus_one_queries(): void
public function test_indexes_are_used(): void
```

### 5. Regression Tests

#### Ensure Existing Functionality Intact
```php
// tests/Feature/RegressionTests.php

public function test_inline_editing_still_works(): void
public function test_mark_as_paid_still_works(): void
public function test_modal_editing_still_works(): void
public function test_transaction_creation_still_works(): void
public function test_recurring_transaction_creation_still_works(): void
public function test_edit_scope_future_only_still_works(): void
public function test_edit_scope_current_and_future_still_works(): void
```

## Test Data Setup

### Factories Needed

Ensure factories cover all scenarios:

```php
// database/factories/TransactionFactory.php
public function paid(): static
public function pending(): static
public function overdue(): static
public function debit(): static
public function credit(): static
public function withTags(array $tags): static
public function recurring(): static

// database/factories/RecurringTransactionFactory.php
public function weekly(): static
public function monthly(): static
public function yearly(): static
public function custom(int $interval): static
public function active(): static
public function inactive(): static
public function withTags(array $tags): static
```

### Seeders for Manual Testing

```php
// database/seeders/TransactionTestSeeder.php
- Create diverse transaction dataset
- Various statuses, types, dates, tags
- Test filtering scenarios

// database/seeders/RecurringTransactionTestSeeder.php
- Create recurring transactions with different frequencies
- Test monthly amount calculations
- Test tag filtering
```

## Implementation Steps

### Phase 1: Component Unit Tests
1. Test TransactionFilters component
2. Test RecurringTransactionFilters component
3. Test filter event dispatching
4. Test URL state persistence

### Phase 2: List Component Tests
1. Test TransactionList component
2. Test RecurringTransactionList component
3. Test filter application from events
4. Test aggregate caching

### Phase 3: Integration Tests
1. Test filter → list communication
2. Test URL state end-to-end
3. Test multiple filters together
4. Test pagination + filtering

### Phase 4: Feature Tests
1. Test complete user workflows
2. Test new tag filtering feature
3. Test all existing features
4. Test edge cases

### Phase 5: Regression Tests
1. Test all existing functionality
2. Ensure no breaking changes
3. Verify performance improvements
4. Check query optimization

## Files to Create

### Test Files:
- `tests/Feature/Livewire/TransactionFiltersTest.php`
- `tests/Feature/Livewire/RecurringTransactionFiltersTest.php`
- `tests/Feature/Livewire/TransactionListTest.php`
- `tests/Feature/Livewire/RecurringTransactionListTest.php`
- `tests/Feature/Integration/FilterListCommunicationTest.php`
- `tests/Feature/Integration/UrlStatePersistenceTest.php`
- `tests/Feature/TransactionListWorkflowTest.php`
- `tests/Feature/RecurringTransactionListWorkflowTest.php`
- `tests/Feature/RegressionTests.php`
- `tests/Performance/QueryOptimizationTest.php` (optional)

### Support Files:
- Update `database/factories/TransactionFactory.php`
- Update `database/factories/RecurringTransactionFactory.php`
- Create `database/seeders/TransactionTestSeeder.php`
- Create `database/seeders/RecurringTransactionTestSeeder.php`

## Running Tests

### Full Suite:
```bash
php artisan test
```

### Specific Test Files:
```bash
php artisan test tests/Feature/Livewire/TransactionFiltersTest.php
php artisan test tests/Feature/Livewire/RecurringTransactionFiltersTest.php
php artisan test --filter=TransactionList
```

### With Coverage:
```bash
php artisan test --coverage --min=80
```

## Success Criteria

✅ All component tests passing
✅ All integration tests passing
✅ All feature tests passing
✅ All regression tests passing
✅ No breaking changes detected
✅ Code coverage ≥ 80% for refactored code
✅ Performance tests verify optimizations
✅ New features (tag filtering, URL state) tested
✅ Edge cases covered
✅ Test suite runs in reasonable time (<2 min)

## Notes

- **DEFERRED:** Execute this task AFTER completing Refactor 01, 02, 03
- Run tests incrementally as you implement each refactor phase
- Fix broken tests immediately
- Add tests for any bugs found during development
- Keep tests maintainable and readable
- Use factories instead of manual model creation
- Mock external services (if any)

## Related Tasks

- **Refactor 01:** Transaction filters extraction
- **Refactor 02:** Recurring filters extraction
- **Refactor 03:** Performance optimizations

All three must be complete before running this comprehensive test suite.

## Estimate

- **Time:** 4-6 hours
- **Complexity:** Medium-High
- **Priority:** High (when ready to execute)
- **Blockers:** Refactor 01, 02, 03 must be complete
