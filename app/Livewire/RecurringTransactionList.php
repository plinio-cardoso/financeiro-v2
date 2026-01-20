<?php

namespace App\Livewire;

use App\Models\RecurringTransaction;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class RecurringTransactionList extends Component
{
    use WithPagination;

    public ?int $editingRecurringId = null;

    public int $modalCounter = 0;

    // Ordenação
    public string $sortField = 'next_due_date';

    public string $sortDirection = 'asc';

    // Paginação
    public int $perPage = 15;

    // Filtros
    public string $search = '';

    public string $filterType = '';

    public string $filterStatus = '';

    public string $filterFrequency = '';

    protected $listeners = [
        'recurring-saved' => 'refreshList',
    ];

    #[Computed]
    public function recurringTransactions()
    {
        $query = RecurringTransaction::where('user_id', auth()->id());

        // Busca
        if (strlen($this->search) >= 3) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        // Filtro de tipo
        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        // Filtro de status
        if ($this->filterStatus === 'active') {
            $query->where('active', true);
        } elseif ($this->filterStatus === 'inactive') {
            $query->where('active', false);
        }

        // Filtro de frequência
        if ($this->filterFrequency) {
            $query->where('frequency', $this->filterFrequency);
        }

        return $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    #[Computed]
    public function totalCount(): int
    {
        $query = RecurringTransaction::where('user_id', auth()->id());

        if (strlen($this->search) >= 3) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterStatus === 'active') {
            $query->where('active', true);
        } elseif ($this->filterStatus === 'inactive') {
            $query->where('active', false);
        }

        if ($this->filterFrequency) {
            $query->where('frequency', $this->filterFrequency);
        }

        return $query->count();
    }

    #[Computed]
    public function totalMonthlyAmount(): float
    {
        $query = RecurringTransaction::where('user_id', auth()->id())
            ->where('active', true);

        if (strlen($this->search) >= 3) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterFrequency) {
            $query->where('frequency', $this->filterFrequency);
        }

        return $query->get()->sum(function ($recurring) {
            $monthlyAmount = match ($recurring->frequency->value) {
                'weekly' => $recurring->amount * 4,
                'monthly' => $recurring->amount,
                'custom' => $recurring->amount / ($recurring->interval ?? 1),
                default => 0,
            };

            return $recurring->type->value === 'credit' ? $monthlyAmount : -$monthlyAmount;
        });
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return strlen($this->search) >= 3
            || $this->filterType !== ''
            || $this->filterStatus !== ''
            || $this->filterFrequency !== '';
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'filterType',
            'filterStatus',
            'filterFrequency',
        ]);

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

    public function openEditModal(int $recurringId): void
    {
        $this->editingRecurringId = $recurringId;
        $this->modalCounter++;
    }

    public function closeModal(): void
    {
        $this->reset(['editingRecurringId']);
    }

    public function refreshList(): void
    {
        $this->editingRecurringId = null;
        $this->resetPage();
        $this->dispatch('close-modal');
    }

    public function render()
    {
        return view('livewire.recurring-transaction-list');
    }
}
