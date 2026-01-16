<?php

namespace App\Livewire;

use App\Models\Transaction;
use App\Services\TransactionService;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Component;

class TransactionActions extends Component
{
    use InteractsWithBanner;

    public Transaction $transaction;

    public bool $confirmingDelete = false;

    public function togglePaidStatus(TransactionService $transactionService): void
    {
        $newStatus = $this->transaction->status->value === 'paid' ? 'pending' : 'paid';

        $data = [
            'status' => $newStatus,
            'paid_at' => $newStatus === 'paid' ? now() : null,
        ];

        $transactionService->updateTransaction($this->transaction->id, $data);

        $this->transaction->refresh();

        $message = $newStatus === 'paid'
            ? 'Transação marcada como paga!'
            : 'Transação marcada como pendente!';

        $this->banner($message);
        $this->dispatch('transaction-updated');
    }

    public function confirmDelete(): void
    {
        $this->confirmingDelete = true;
    }

    public function delete(TransactionService $transactionService): void
    {
        $transactionService->deleteTransaction($this->transaction->id);

        $this->banner('Transação excluída com sucesso!');
        $this->dispatch('transaction-deleted');

        $this->redirect(route('transactions.index'));
    }

    public function render()
    {
        return view('livewire.transaction-actions');
    }
}
