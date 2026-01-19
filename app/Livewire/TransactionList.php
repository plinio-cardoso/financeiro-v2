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
    }

    public function editTransaction(int $id): void
    {
        $this->editingTransactionId = $id;
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
        $this->editingTransactionId = null;
        $this->resetPage();
        $this->dispatch('close-modal');
        $this->dispatch('notify', message: 'Transação salva com sucesso!', type: 'success');
    }

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

            $this->resetPage();
            $this->dispatch('notify', message: 'Transação marcada como paga com sucesso!', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Erro ao marcar transação como paga: '.$e->getMessage(), type: 'error');
        }
    }

    public function updateField(int $id, string $field, $value): void
    {
        try {
            $transaction = app(TransactionService::class)->findTransactionById($id, auth()->id());

            if (! $transaction) {
                $this->dispatch('notify', message: 'Transação não encontrada.', type: 'error');

                return;
            }

            // Map field names to human-readable names for error messages
            $fieldNames = [
                'title' => 'título',
                'description' => 'descrição',
                'amount' => 'valor',
                'type' => 'tipo',
                'due_date' => 'data de vencimento',
                'status' => 'status',
            ];

            // Validation logic
            $rules = [
                'field' => 'required|in:title,description,amount,type,due_date,status',
            ];

            $validationValue = $value;

            switch ($field) {
                case 'title':
                    $rules['value'] = 'required|string|max:255';
                    break;
                case 'description':
                    $rules['value'] = 'nullable|string';
                    break;
                case 'amount':
                    // Convert potential comma decimal separator
                    $validationValue = str_replace(',', '.', $value);
                    $rules['value'] = 'required|numeric|min:0.01';
                    break;
                case 'type':
                    $rules['value'] = 'required|in:debit,credit';
                    break;
                case 'due_date':
                    $rules['value'] = 'required|date';
                    break;
                case 'status':
                    $rules['value'] = 'required|in:pending,paid';
                    break;
            }

            $validator = validator(['field' => $field, 'value' => $validationValue], $rules);

            if ($validator->fails()) {
                $this->dispatch('notify', message: $validator->errors()->first(), type: 'error');

                return;
            }

            // Update the model
            app(TransactionService::class)->updateTransaction($transaction, [
                $field => $validationValue,
            ]);

            $this->dispatch('notify', message: ucfirst($fieldNames[$field]).' atualizado com sucesso!', type: 'success');

            // Refresh the component to show updated data
            // Since we are using Computed properties, we don't strictly need to redirect,
            // but we might need to reset items or just let Livewire do its thing.
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Erro ao atualizar: '.$e->getMessage(), type: 'error');
        }
    }

    public function updateTags(int $id, array $tagIds): void
    {
        try {
            $transaction = app(TransactionService::class)->findTransactionById($id, auth()->id());

            if (! $transaction) {
                $this->dispatch('notify', message: 'Transação não encontrada.', type: 'error');

                return;
            }

            app(TransactionService::class)->updateTransaction($transaction, [
                'tags' => $tagIds,
            ]);

            $this->dispatch('notify', message: 'Tags atualizadas com sucesso!', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Erro ao analisar tags: '.$e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.transaction-list');
    }
}
