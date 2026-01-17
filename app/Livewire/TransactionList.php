<?php

namespace App\Livewire;

use App\Models\Tag;
use App\Services\TransactionService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionList extends Component
{
    use WithPagination;

    public bool $showCreateModal = false;

    public ?int $editingTransactionId = null;

    // Filtros
    public string $search = '';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public array $selectedTags = [];

    public ?string $filterStatus = null;

    public ?string $filterType = null;

    // Ordenação
    public string $sortField = 'due_date';

    public string $sortDirection = 'asc';

    // Paginação
    public int $perPage = 15;

    #[Computed]
    public function transactions()
    {
        return $this->getFilteredQuery()->paginate($this->perPage);
    }

    #[Computed]
    public function totalCount(): int
    {
        return $this->getFilteredQuery()->count();
    }

    #[Computed]
    public function totalAmount(): float
    {
        $query = $this->getFilteredQuery();

        // Sum credits and debits separately to calculate net amount
        $credits = (clone $query)->where('type', 'credit')->sum('amount');
        $debits = (clone $query)->where('type', 'debit')->sum('amount');

        return $credits - $debits;
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
                'sort_by' => $this->sortField,
                'sort_direction' => $this->sortDirection,
            ]
        );
    }

    #[Computed]
    public function tags()
    {
        return Tag::orderBy('name')->get();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedTags(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
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

    public function createTransaction(): void
    {
        $this->editingTransactionId = null;
        $this->showCreateModal = true;
    }

    public function editTransaction(int $id): void
    {
        $this->editingTransactionId = $id;
        $this->showCreateModal = true;
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
        ]);
    }

    #[On('transaction-saved')]
    public function refreshList(): void
    {
        $this->showCreateModal = false;
        $this->editingTransactionId = null;
        $this->resetPage();

        $this->dispatch('notify', message: 'Transação salva com sucesso!', type: 'success');
    }

    public function markAsPaid(int $transactionId): void
    {
        try {
            $transaction = app(TransactionService::class)
                ->findTransactionById($transactionId, auth()->id());

            if (!$transaction) {
                $this->dispatch('notify', message: 'Transação não encontrada.', type: 'error');

                return;
            }

            if ($transaction->type->value !== 'debit') {
                $this->dispatch('notify', message: 'Apenas transações de débito podem ser marcadas como pagas.', type: 'error');

                return;
            }

            if ($transaction->status->value === 'paid') {
                $this->dispatch('notify', message: 'Esta transação já está marcada como paga.', type: 'info');

                return;
            }

            $transaction->markAsPaid();

            $this->resetPage();
            $this->dispatch('notify', message: 'Transação marcada como paga com sucesso!', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Erro ao marcar transação como paga: ' . $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.transaction-list');
    }
}
