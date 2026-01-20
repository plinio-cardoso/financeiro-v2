<?php

namespace App\Livewire;

use App\Services\TagService;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionList extends Component
{
    use WithPagination;

    public ?int $editingTransactionId = null;

    public ?int $editingRecurringId = null;

    public int $modalCounter = 0;

    // Filtros
    public string $search = '';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public array $selectedTags = [];

    public ?string $filterStatus = null;

    public ?string $filterType = null;

    public ?string $filterRecurrence = null;

    // Ordenação
    public string $sortField = 'due_date';

    public string $sortDirection = 'asc';

    // Paginação
    public int $perPage = 15;

    // Cache for aggregates to avoid multiple queries
    private $aggregatesCache = null;

    protected $listeners = [
        'transaction-updated' => 'refreshAggregates',
        'open-edit-modal' => 'openEditModal',
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

    private function loadAggregates(): void
    {
        if ($this->aggregatesCache === null) {
            // Single query to get ALL totals across ALL filtered transactions
            $this->aggregatesCache = app(TransactionService::class)->getFilteredTransactions(
                auth()->id(),
                [
                    'search' => $this->search,
                    'start_date' => $this->startDate,
                    'end_date' => $this->endDate,
                    'tags' => $this->selectedTags,
                    'status' => $this->filterStatus,
                    'type' => $this->filterType,
                    'recurring' => $this->filterRecurrence,
                ],
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
            [
                'search' => $this->search,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'tags' => $this->selectedTags,
                'status' => $this->filterStatus,
                'type' => $this->filterType,
                'recurring' => $this->filterRecurrence,
                'sort_by' => $this->sortField,
                'sort_direction' => $this->sortDirection,
            ]
        );
    }

    #[Computed]
    public function tags()
    {
        return app(TagService::class)->getUserTags(auth()->id());
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return ! empty($this->search)
            || ! empty($this->startDate)
            || ! empty($this->endDate)
            || ! empty($this->selectedTags)
            || ! empty($this->filterStatus)
            || ! empty($this->filterType)
            || ! empty($this->filterRecurrence);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->aggregatesCache = null;
    }

    public function updatedSelectedTags(): void
    {
        $this->resetPage();
        $this->aggregatesCache = null;
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
        $this->aggregatesCache = null;
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
        $this->aggregatesCache = null;
    }

    public function updatedFilterRecurrence(): void
    {
        $this->resetPage();
        $this->aggregatesCache = null;
    }

    public function updatedStartDate(): void
    {
        $this->resetPage();
        $this->aggregatesCache = null;
    }

    public function updatedEndDate(): void
    {
        $this->resetPage();
        $this->aggregatesCache = null;
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

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'startDate',
            'endDate',
            'selectedTags',
            'filterStatus',
            'filterType',
            'filterRecurrence',
        ]);
    }

    #[On('transaction-saved')]
    public function refreshList(): void
    {
        $this->editingTransactionId = null;
        $this->editingRecurringId = null;
        $this->resetPage();
        $this->dispatch('close-modal');
        $this->dispatch('notify', message: 'Transação salva com sucesso!', type: 'success');
    }

    #[On('recurring-saved')]
    public function refreshListRecurring(): void
    {
        $this->editingTransactionId = null;
        $this->editingRecurringId = null;
        $this->resetPage();
        $this->dispatch('close-modal');
        $this->dispatch('notify', message: 'Recorrência atualizada com sucesso!', type: 'success');
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
        $this->modalCounter++;
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
