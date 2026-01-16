<?php

namespace App\Services;

use App\Enums\TransactionTypeEnum;
use App\Models\Transaction;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        private TransactionService $transactionService
    ) {}

    /**
     * Get current month statistics
     */
    public function getCurrentMonthStats(int $userId): array
    {
        $now = now();
        $currentYear = $now->year;
        $currentMonth = $now->month;

        // Calculate monthly totals (only debits)
        $monthlyTotals = $this->transactionService->calculateMonthlyTotals(
            $userId,
            $currentYear,
            $currentMonth
        );

        // Get next month total
        $nextMonthTotal = $this->transactionService->getNextMonthTotal(
            $userId,
            $currentYear,
            $currentMonth
        );

        // Count overdue transactions (only debits)
        $overdueCount = Transaction::where('user_id', $userId)
            ->where('type', TransactionTypeEnum::Debit)
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->count();

        // Count total transactions for the month (only debits)
        $transactionsCount = Transaction::where('user_id', $userId)
            ->where('type', TransactionTypeEnum::Debit)
            ->whereYear('due_date', $currentYear)
            ->whereMonth('due_date', $currentMonth)
            ->count();

        return [
            'total_due' => $monthlyTotals['total_due'],
            'total_paid' => $monthlyTotals['total_paid'],
            'total_pending' => $monthlyTotals['total_pending'],
            'next_month_total' => $nextMonthTotal,
            'transactions_count' => $transactionsCount,
            'overdue_count' => $overdueCount,
        ];
    }

    /**
     * Get monthly transactions (defaults to current month)
     */
    public function getMonthlyTransactions(int $userId, ?int $year = null, ?int $month = null): Collection
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return Transaction::where('user_id', $userId)
            ->whereBetween('due_date', [$startDate, $endDate])
            ->orderBy('due_date')
            ->with('tags')
            ->get();
    }
}
