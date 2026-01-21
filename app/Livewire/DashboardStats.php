<?php

namespace App\Livewire;

use App\Services\DashboardService;
use Livewire\Component;

class DashboardStats extends Component
{
    public int $userId;

    public array $stats = [];

    public function mount(DashboardService $dashboardService): void
    {
        // Load tags and dispatch to Alpine store
        $this->dispatch('tags-loaded', tags: app(\App\Services\TagService::class)->getUserTags(auth()->id()));

        $this->userId = auth()->id();
        $stats = $dashboardService->getCurrentMonthStats($this->userId);

        $this->stats = [
            'total_to_pay' => $stats['total_pending'],
            'total_paid' => $stats['total_paid'],
            'next_month_forecast' => $stats['next_month_total'],
            'overdue_count' => $stats['overdue_count'],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard-stats');
    }
}
