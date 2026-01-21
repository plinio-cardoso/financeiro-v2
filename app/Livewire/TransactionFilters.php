<?php

namespace App\Livewire;

use App\Services\TagService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class TransactionFilters extends Component
{
    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public ?string $startDate = null;

    #[Url(history: true)]
    public ?string $endDate = null;

    #[Url(history: true)]
    public array $selectedTags = [];

    #[Url(history: true)]
    public ?string $filterStatus = null;

    #[Url(history: true)]
    public ?string $filterType = null;

    #[Url(history: true)]
    public ?string $filterRecurrence = null;

    public function mount(): void
    {
        // Load tags and dispatch initial filter state
        $this->dispatch('tags-loaded', tags: $this->tags);
        $this->dispatchFilters();
    }

    #[Computed]
    public function tags()
    {
        return app(TagService::class)->getUserTags(auth()->id());
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return !empty($this->search)
            || !empty($this->startDate)
            || !empty($this->endDate)
            || !empty($this->selectedTags)
            || !empty($this->filterStatus)
            || !empty($this->filterType)
            || !empty($this->filterRecurrence);
    }

    public function updated($property): void
    {
        $this->dispatchFilters();
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

        $this->dispatchFilters();
    }

    public function getFilters(): array
    {
        return [
            'search' => $this->search,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'tags' => $this->selectedTags,
            'status' => $this->filterStatus,
            'type' => $this->filterType,
            'recurring' => $this->filterRecurrence,
        ];
    }

    private function dispatchFilters(): void
    {
        $this->dispatch('filters-updated', filters: $this->getFilters());
    }

    public function render()
    {
        return view('livewire.transaction-filters');
    }
}
