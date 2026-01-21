<?php

namespace Tests\Feature\Livewire;

use App\Livewire\TransactionFilters;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_render(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionFilters::class)
            ->assertStatus(200);
    }

    public function test_search_dispatches_filters_updated_event(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionFilters::class)
            ->set('search', 'Grocery')
            ->assertDispatched('filters-updated', filters: [
                'search' => 'Grocery',
                'start_date' => null,
                'end_date' => null,
                'tags' => [],
                'status' => null,
                'type' => null,
                'recurring' => null,
            ]);
    }

    public function test_date_range_dispatches_filters_updated_event(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionFilters::class)
            ->set('startDate', '2024-01-01')
            ->set('endDate', '2024-12-31')
            ->assertDispatched('filters-updated');
    }

    public function test_status_filter_dispatches_event(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionFilters::class)
            ->set('filterStatus', 'paid')
            ->assertDispatched('filters-updated', filters: [
                'search' => '',
                'start_date' => null,
                'end_date' => null,
                'tags' => [],
                'status' => 'paid',
                'type' => null,
                'recurring' => null,
            ]);
    }

    public function test_type_filter_dispatches_event(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionFilters::class)
            ->set('filterType', 'debit')
            ->assertDispatched('filters-updated', filters: [
                'search' => '',
                'start_date' => null,
                'end_date' => null,
                'tags' => [],
                'status' => null,
                'type' => 'debit',
                'recurring' => null,
            ]);
    }

    public function test_tag_filter_dispatches_event(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionFilters::class)
            ->set('selectedTags', [$tag->id])
            ->assertDispatched('filters-updated', filters: [
                'search' => '',
                'start_date' => null,
                'end_date' => null,
                'tags' => [$tag->id],
                'status' => null,
                'type' => null,
                'recurring' => null,
            ]);
    }

    public function test_recurrence_filter_dispatches_event(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionFilters::class)
            ->set('filterRecurrence', 'recurring')
            ->assertDispatched('filters-updated', filters: [
                'search' => '',
                'start_date' => null,
                'end_date' => null,
                'tags' => [],
                'status' => null,
                'type' => null,
                'recurring' => 'recurring',
            ]);
    }

    public function test_clear_filters_resets_all_values(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionFilters::class)
            ->set('search', 'test')
            ->set('startDate', '2024-01-01')
            ->set('endDate', '2024-12-31')
            ->set('selectedTags', [$tag->id])
            ->set('filterStatus', 'paid')
            ->set('filterType', 'debit')
            ->set('filterRecurrence', 'recurring')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('startDate', null)
            ->assertSet('endDate', null)
            ->assertSet('selectedTags', [])
            ->assertSet('filterStatus', null)
            ->assertSet('filterType', null)
            ->assertSet('filterRecurrence', null)
            ->assertDispatched('filters-updated');
    }

    public function test_has_active_filters_returns_true_when_filters_set(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(TransactionFilters::class)
            ->set('search', 'test');

        $this->assertTrue($component->get('hasActiveFilters'));
    }

    public function test_has_active_filters_returns_false_when_no_filters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(TransactionFilters::class);

        $this->assertFalse($component->get('hasActiveFilters'));
    }

    public function test_tags_are_loaded_on_mount(): void
    {
        $user = User::factory()->create();
        Tag::factory()->count(3)->create();

        $this->actingAs($user);

        $component = Livewire::test(TransactionFilters::class);

        $this->assertCount(3, $component->get('tags'));
    }

    public function test_mount_dispatches_tags_loaded_event(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionFilters::class)
            ->assertDispatched('tags-loaded');
    }

    public function test_mount_dispatches_initial_filters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionFilters::class)
            ->assertDispatched('filters-updated');
    }

    public function test_multiple_filters_dispatch_event(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionFilters::class)
            ->set('search', 'grocery')
            ->set('filterType', 'debit')
            ->set('selectedTags', [$tag->id])
            ->assertDispatched('filters-updated', filters: [
                'search' => 'grocery',
                'start_date' => null,
                'end_date' => null,
                'tags' => [$tag->id],
                'status' => null,
                'type' => 'debit',
                'recurring' => null,
            ]);
    }
}
