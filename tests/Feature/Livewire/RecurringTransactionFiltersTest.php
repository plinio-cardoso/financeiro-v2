<?php

namespace Tests\Feature\Livewire;

use App\Livewire\RecurringTransactionFilters;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RecurringTransactionFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_render(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(RecurringTransactionFilters::class)
            ->assertStatus(200);
    }

    public function test_search_dispatches_filters_updated_event(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(RecurringTransactionFilters::class)
            ->set('search', 'Rent')
            ->assertDispatched('recurring-filters-updated', filters: [
                'search' => 'Rent',
                'tags' => [],
                'status' => null,
                'type' => null,
                'frequency' => null,
            ]);
    }

    public function test_status_filter_dispatches_event(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(RecurringTransactionFilters::class)
            ->set('filterStatus', 'active')
            ->assertDispatched('recurring-filters-updated', filters: [
                'search' => '',
                'tags' => [],
                'status' => 'active',
                'type' => null,
                'frequency' => null,
            ]);
    }

    public function test_type_filter_dispatches_event(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(RecurringTransactionFilters::class)
            ->set('filterType', 'credit')
            ->assertDispatched('recurring-filters-updated', filters: [
                'search' => '',
                'tags' => [],
                'status' => null,
                'type' => 'credit',
                'frequency' => null,
            ]);
    }

    public function test_frequency_filter_dispatches_event(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(RecurringTransactionFilters::class)
            ->set('filterFrequency', 'monthly')
            ->assertDispatched('recurring-filters-updated', filters: [
                'search' => '',
                'tags' => [],
                'status' => null,
                'type' => null,
                'frequency' => 'monthly',
            ]);
    }

    public function test_tag_filter_dispatches_event(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $this->actingAs($user);

        Livewire::test(RecurringTransactionFilters::class)
            ->set('selectedTags', [$tag->id])
            ->assertDispatched('recurring-filters-updated', filters: [
                'search' => '',
                'tags' => [$tag->id],
                'status' => null,
                'type' => null,
                'frequency' => null,
            ]);
    }

    public function test_clear_filters_resets_all_values(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $this->actingAs($user);

        Livewire::test(RecurringTransactionFilters::class)
            ->set('search', 'test')
            ->set('selectedTags', [$tag->id])
            ->set('filterStatus', 'active')
            ->set('filterType', 'credit')
            ->set('filterFrequency', 'monthly')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('selectedTags', [])
            ->assertSet('filterStatus', null)
            ->assertSet('filterType', null)
            ->assertSet('filterFrequency', null)
            ->assertDispatched('recurring-filters-updated');
    }

    public function test_has_active_filters_returns_true_when_filters_set(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(RecurringTransactionFilters::class)
            ->set('search', 'test');

        $this->assertTrue($component->get('hasActiveFilters'));
    }

    public function test_has_active_filters_returns_false_when_no_filters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(RecurringTransactionFilters::class);

        $this->assertFalse($component->get('hasActiveFilters'));
    }

    public function test_has_active_filters_with_tags(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(RecurringTransactionFilters::class)
            ->set('selectedTags', [$tag->id]);

        $this->assertTrue($component->get('hasActiveFilters'));
    }

    public function test_tags_are_loaded_on_mount(): void
    {
        $user = User::factory()->create();
        Tag::factory()->count(3)->create();

        $this->actingAs($user);

        $component = Livewire::test(RecurringTransactionFilters::class);

        $this->assertCount(3, $component->get('tags'));
    }

    public function test_mount_dispatches_tags_loaded_event(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(RecurringTransactionFilters::class)
            ->assertDispatched('tags-loaded');
    }

    public function test_mount_dispatches_initial_filters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(RecurringTransactionFilters::class)
            ->assertDispatched('recurring-filters-updated');
    }

    public function test_multiple_filters_dispatch_event(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $this->actingAs($user);

        Livewire::test(RecurringTransactionFilters::class)
            ->set('search', 'subscription')
            ->set('filterType', 'debit')
            ->set('filterFrequency', 'monthly')
            ->set('selectedTags', [$tag->id])
            ->assertDispatched('recurring-filters-updated', filters: [
                'search' => 'subscription',
                'tags' => [$tag->id],
                'status' => null,
                'type' => 'debit',
                'frequency' => 'monthly',
            ]);
    }
}
