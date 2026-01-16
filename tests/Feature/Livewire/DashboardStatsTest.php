<?php

namespace Tests\Feature\Livewire;

use App\Livewire\DashboardStats;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_render(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(DashboardStats::class)
            ->assertStatus(200)
            ->assertSee('Total a Pagar');
    }

    public function test_component_loads_user_stats(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(DashboardStats::class);

        $this->assertEquals($user->id, $component->userId);
        $this->assertIsArray($component->stats);
    }

    public function test_component_displays_stats_in_view(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(DashboardStats::class)
            ->assertSee('Total a Pagar (Mês Atual)')
            ->assertSee('Total Pago (Mês Atual)')
            ->assertSee('Previsto (Próximo Mês)')
            ->assertSee('Transações Vencidas');
    }
}
