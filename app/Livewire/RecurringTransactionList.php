<?php

namespace App\Livewire;

use App\Models\RecurringTransaction;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class RecurringTransactionList extends Component
{
    use WithPagination;

    // Active filters received from RecurringTransactionFilters component
    public array $activeFilters = [];

    // Ordenação
    public string $sortField = 'next_due_date';

    public string $sortDirection = 'asc';

    // Paginação
    public int $perPage = 15;

    // Cache for aggregates to avoid multiple queries
    private $aggregatesCache = null;

    protected $listeners = [
        'recurring-saved' => 'refreshList',
        'recurring-filters-updated' => 'applyFilters',
    ];

    public function applyFilters(array $filters): void
    {
        $this->activeFilters = $filters;
        $this->aggregatesCache = null;
        $this->resetPage();
    }

    private function getFilteredQuery()
    {
        $query = RecurringTransaction::where('user_id', auth()->id());

        $search = $this->activeFilters['search'] ?? '';
        $type = $this->activeFilters['type'] ?? null;
        $status = $this->activeFilters['status'] ?? null;
        $frequency = $this->activeFilters['frequency'] ?? null;
        $tags = $this->activeFilters['tags'] ?? [];

        // Busca
        if (strlen($search) >= 3) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        // Filtro de tipo
        if ($type) {
            $query->where('type', $type);
        }

        // Filtro de status
        if ($status === 'active') {
            $query->where('active', true);
        } elseif ($status === 'inactive') {
            $query->where('active', false);
        }

        // Filtro de frequência
        if ($frequency) {
            $query->where('frequency', $frequency);
        }

        // Filtro de tags (NEW!)
        if (!empty($tags)) {
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('tags.id', $tags);
            });
        }

        return $query;
    }

    #[Computed]
    public function recurringTransactions()
    {
        return $this->getFilteredQuery()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    #[Computed]
    public function totalCount(): int
    {
        $this->loadAggregates();

        return $this->aggregatesCache->total_count ?? 0;
    }

    #[Computed]
    public function totalMonthlyAmount(): float
    {
        $this->loadAggregates();

        return $this->aggregatesCache->total_monthly_amount ?? 0;
    }

    private function loadAggregates(): void
    {
        if ($this->aggregatesCache !== null) {
            return;
        }

        // Get count from query
        $count = $this->getFilteredQuery()->count();

        // For monthly amount, we need to calculate based on active recurring transactions
        $activeRecurringTransactions = $this->getFilteredQuery()
            ->where('active', true)
            ->get();

        $monthlyAmount = $activeRecurringTransactions->sum(function ($recurring) {
            $monthlyAmount = match ($recurring->frequency->value) {
                'weekly' => $recurring->amount * 4,
                'monthly' => $recurring->amount,
                'custom' => $recurring->amount / ($recurring->interval ?? 1),
                default => 0,
            };

            return $recurring->type->value === 'credit' ? $monthlyAmount : -$monthlyAmount;
        });

        // Cache the results
        $this->aggregatesCache = (object) [
            'total_count' => $count,
            'total_monthly_amount' => $monthlyAmount,
        ];
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function closeModal(): void
    {
        // No longer needed but keeping for potential clean events
    }

    public function refreshList(): void
    {
        $this->refreshAggregates();
    }

    public function refreshAggregates(): void
    {
        $this->aggregatesCache = null;
    }

    public function render()
    {
        return view('livewire.recurring-transaction-list');
    }
}
