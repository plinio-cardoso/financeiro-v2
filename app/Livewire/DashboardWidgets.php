<?php

namespace App\Livewire;

use App\Services\DashboardService;
use App\Services\TransactionService;
use Livewire\Component;

class DashboardWidgets extends Component
{
    public $recentActivity = [];
    public $upcomingExpenses = [];
    public $expensesByTag = [];
    public $monthlyComparison = [];

    public function mount(DashboardService $dashboardService): void
    {
        $userId = auth()->id();
        $this->recentActivity = $dashboardService->getRecentActivity($userId);
        $this->upcomingExpenses = $dashboardService->getUpcomingExpenses($userId);
        $this->expensesByTag = $dashboardService->getExpensesByTag($userId);
        $this->monthlyComparison = $dashboardService->getMonthlyComparison($userId);
    }

    public function markAsPaid(int $transactionId): void
    {
        try {
            $transaction = app(TransactionService::class)->findTransactionById($transactionId, auth()->id());

            if ($transaction) {
                app(TransactionService::class)->updateTransaction($transaction, [
                    'status' => 'paid',
                ]);

                $this->mount(app(DashboardService::class));
                $this->dispatch('notify', message: 'Transação marcada como paga!', type: 'success');
                $this->dispatch('transactionUpdated');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Erro: ' . $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.dashboard-widgets');
    }
}
