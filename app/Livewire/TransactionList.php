<?php

namespace App\Livewire;

use App\Services\TransactionService;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionList extends Component
{
    use WithPagination;

    public ?int $editingTransactionId = null;

    public ?int $editingRecurringId = null;

    // Active filters received from TransactionFilters component
    public array $activeFilters = [];

    // Ordenação
    #[Url(history: true)]
    public string $sortField = 'due_date';

    #[Url(history: true)]
    public string $sortDirection = 'asc';

    // Paginação
    public int $perPage = 15;

    // Cache for aggregates to avoid multiple queries
    private $aggregatesCache = null;

    protected $listeners = [
        'transaction-updated' => 'refreshAggregates',
        'open-edit-modal' => 'openEditModal',
        'filters-updated' => 'applyFilters',
    ];

    #[Computed]
    public function transactions()
    {
        return $this->getFilteredQuery()->paginate($this->perPage);
    }

    #[Computed]
    public function totalCount(): int
    {
        $this->loadAggregates();

        return $this->aggregatesCache->total_count ?? 0;
    }

    #[Computed]
    public function totalAmount(): float
    {
        $this->loadAggregates();

        return ($this->aggregatesCache->total_credits ?? 0) - ($this->aggregatesCache->total_debits ?? 0);
    }

    public function applyFilters(array $filters): void
    {
        $this->activeFilters = $filters;
        $this->aggregatesCache = null;
        $this->resetPage();
    }

    private function loadAggregates(): void
    {
        if ($this->aggregatesCache === null) {
            // Single query to get ALL totals across ALL filtered transactions
            $this->aggregatesCache = app(TransactionService::class)->getFilteredTransactions(
                auth()->id(),
                $this->activeFilters,
                false // Don't include select for aggregates
            )
                ->selectRaw('
                    COUNT(*) as total_count,
                    SUM(CASE WHEN type = "credit" THEN amount ELSE 0 END) as total_credits,
                    SUM(CASE WHEN type = "debit" THEN amount ELSE 0 END) as total_debits
                ')
                ->first();
        }
    }

    private function getFilteredQuery()
    {
        return app(TransactionService::class)->getFilteredTransactions(
            auth()->id(),
            array_merge($this->activeFilters, [
                'sort_by' => $this->sortField,
                'sort_direction' => $this->sortDirection,
            ])
        );
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
        // Reset both IDs when closing to ensure clean state
        $this->reset(['editingTransactionId', 'editingRecurringId']);
    }

    #[On('transaction-saved')]
    public function refreshList(): void
    {
        $this->editingTransactionId = null;
        $this->editingRecurringId = null;
        $this->refreshAggregates();
        $this->dispatch('close-modal');
    }

    #[On('recurring-saved')]
    public function refreshListRecurring(): void
    {
        $this->editingTransactionId = null;
        $this->editingRecurringId = null;
        $this->refreshAggregates();
        $this->dispatch('close-modal');
    }

    public function refreshAggregates(): void
    {
        // Reset aggregates cache to force recalculation
        $this->aggregatesCache = null;
    }

    public function openEditModal(int $transactionId): void
    {
        $this->editingTransactionId = $transactionId;
        $this->editingRecurringId = null; // Reset - TransactionForm handles this internally now
    }

    /**
     * Fallback method for marking transaction as paid
     * Kept for backward compatibility with existing tests
     * In production, this is handled by TransactionRow component
     */
    public function markAsPaid(int $transactionId): void
    {
        try {
            $transaction = app(TransactionService::class)
                ->findTransactionById($transactionId, auth()->id());

            if (! $transaction) {
                $this->dispatch('notify', message: 'Transação não encontrada.', type: 'error');

                return;
            }

            if ($transaction->status->value === 'paid') {
                $this->dispatch('notify', message: 'Esta transação já está marcada como paga.', type: 'info');

                return;
            }

            $transaction->markAsPaid();

            $this->refreshAggregates();
            $this->dispatch('notify', message: 'Transação marcada como paga com sucesso!', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Erro ao marcar transação como paga: '.$e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.transaction-list');
    }
}
