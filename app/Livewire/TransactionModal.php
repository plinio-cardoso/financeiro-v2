<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class TransactionModal extends Component
{
    public ?int $transactionId = null;
    public ?int $recurringId = null;
    public string $mode = 'transaction'; // 'transaction' or 'recurring'

    protected $listeners = [
        'open-edit-modal' => 'openTransactionModal',
        'open-recurring-modal' => 'openRecurringModal',
    ];

    #[On('open-edit-modal')]
    public function openTransactionModal(?int $transactionId = null): void
    {
        $this->transactionId = $transactionId;
        $this->recurringId = null;
        $this->mode = 'transaction';
        $this->dispatch('open-modal');
    }

    #[On('open-recurring-modal')]
    public function openRecurringModal(?int $recurringId = null): void
    {
        $this->recurringId = $recurringId;
        $this->transactionId = null;
        $this->mode = 'recurring';
        $this->dispatch('open-modal');
    }

    public function closeModal(): void
    {
        $this->reset(['transactionId', 'recurringId']);
        $this->dispatch('close-modal');
    }

    #[On('transaction-saved')]
    #[On('recurring-saved')]
    public function handleSaved(): void
    {
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.transaction-modal');
    }
}
