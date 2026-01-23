<?php

namespace App\Livewire;

use App\Services\DashboardService;
use App\Services\TransactionService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DashboardWidgets extends Component
{
    #[Locked]
    public Collection $recentActivity;

    #[Locked]
    public Collection $upcomingExpenses;

    #[Locked]
    public Collection $expensesByTag;

    #[Locked]
    public Collection $monthlyComparison;

    public bool $loadedRecent = false;

    public bool $loadedUpcoming = false;

    public bool $loadedByTag = false;

    public bool $loadedComparison = false;

    public function mount(): void
    {
        $this->recentActivity = collect();
        $this->upcomingExpenses = collect();
        $this->expensesByTag = collect();
        $this->monthlyComparison = collect();
    }

    public function loadRecentActivity(): void
    {
        if (! $this->loadedRecent) {
            $this->recentActivity = app(DashboardService::class)->getRecentActivity(auth()->id());
            $this->loadedRecent = true;
        }
    }

    public function loadUpcomingExpenses(): void
    {
        if (! $this->loadedUpcoming) {
            $this->upcomingExpenses = app(DashboardService::class)->getUpcomingExpenses(auth()->id());
            $this->loadedUpcoming = true;
        }
    }

    #[Computed]
    public function upcomingExpensesGrouped(): array
    {
        if (! $this->loadedUpcoming) {
            return [];
        }

        return app(DashboardService::class)
            ->getUpcomingExpensesGroupedByDay(auth()->id());
    }

    #[Computed]
    public function monthlyExpenseTotal(): float
    {
        if (! $this->loadedByTag) {
            return 0.0;
        }

        return app(DashboardService::class)
            ->getCurrentMonthExpenseTotal(auth()->id());
    }

    public function loadExpensesByTag(): void
    {
        if (! $this->loadedByTag) {
            $this->expensesByTag = app(DashboardService::class)->getExpensesByTag(auth()->id());
            $this->loadedByTag = true;
            $this->dispatch('expensesByTagLoaded', data: $this->expensesByTag->toArray());
        }
    }

    public function loadMonthlyComparison(): void
    {
        if (! $this->loadedComparison) {
            $this->monthlyComparison = app(DashboardService::class)->getMonthlyComparison(auth()->id());
            $this->loadedComparison = true;
            $this->dispatch('monthlyComparisonLoaded', data: $this->monthlyComparison->toArray());
        }
    }

    public function markAsPaid(int $transactionId): void
    {
        try {
            $transaction = app(TransactionService::class)->findTransactionById($transactionId, auth()->id());

            if ($transaction) {
                app(TransactionService::class)->updateTransaction($transaction, [
                    'status' => 'paid',
                ]);

                $this->loadedRecent = false;
                $this->loadedUpcoming = false;
                $this->loadedByTag = false;
                $this->loadedComparison = false;

                $this->loadUpcomingExpenses();
                $this->loadRecentActivity();
                $this->loadExpensesByTag();
                $this->loadMonthlyComparison();

                $this->dispatch('notify', message: 'Transação marcada como paga!', type: 'success');
                $this->dispatch('transactionUpdated');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Erro: '.$e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.dashboard-widgets');
    }
}
