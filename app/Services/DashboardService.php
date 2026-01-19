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
            ->whereDate('due_date', '<', today())
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

    /**
     * Get recent transaction activity (by updated_at)
     */
    public function getRecentActivity(int $userId, int $limit = 5): Collection
    {
        return Transaction::where('user_id', $userId)
            ->with('tags')
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get upcoming and overdue pending expenses
     */
    public function getUpcomingExpenses(int $userId): Collection
    {
        return Transaction::where('user_id', $userId)
            ->where('type', TransactionTypeEnum::Debit)
            ->where('status', 'pending')
            ->whereDate('due_date', '<=', today()->addDays(7))
            ->orderBy('due_date')
            ->with('tags')
            ->get();
    }

    /**
     * Get expenses grouped by tag for the current month
     */
    public function getExpensesByTag(int $userId): Collection
    {
        $now = now();

        return Transaction::where('user_id', $userId)
            ->where('type', TransactionTypeEnum::Debit)
            ->whereYear('due_date', $now->year)
            ->whereMonth('due_date', $now->month)
            ->join('transaction_tag', 'transactions.id', '=', 'transaction_tag.transaction_id')
            ->join('tags', 'transaction_tag.tag_id', '=', 'tags.id')
            ->selectRaw('tags.name as tag_name, tags.color as tag_color, SUM(transactions.amount) as total')
            ->groupBy('tags.name', 'tags.color')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Get monthly expenses for the last 6 months for comparison
     */
    public function getMonthlyComparison(int $userId): Collection
    {
        return Transaction::where('user_id', $userId)
            ->where('type', TransactionTypeEnum::Debit)
            ->where('due_date', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw('YEAR(due_date) as year, MONTH(due_date) as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }
}
