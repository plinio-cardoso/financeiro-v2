<?php

namespace App\Livewire;

use App\Models\Tag;
use App\Services\TransactionService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionList extends Component
{
    use WithPagination;

    // Filtros
    public string $search = '';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public array $selectedTags = [];

    public ?string $filterStatus = null;

    public ?string $filterType = null;

    // Ordenação
    public string $sortBy = 'due_date';

    public string $sortDirection = 'asc';

    // Paginação
    public int $perPage = 15;

    #[Computed]
    public function transactions()
    {
        $query = app(TransactionService::class)->getFilteredTransactions(
            auth()->id(),
            [
                'search' => $this->search,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'tags' => $this->selectedTags,
                'status' => $this->filterStatus,
                'type' => $this->filterType,
                'sort_by' => $this->sortBy,
                'sort_direction' => $this->sortDirection,
            ]
        );

        return $query->paginate($this->perPage);
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
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
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

    public function render()
    {
        return view('livewire.transaction-list');
    }
}
