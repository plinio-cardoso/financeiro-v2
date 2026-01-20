<?php

namespace App\Livewire;

use App\Enums\RecurringFrequencyEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RecurringTransactionEdit extends Component
{
    public ?int $recurringId = null;

    public ?RecurringTransaction $recurring = null;

    // Form fields
    public string $title = '';

    public string $description = '';

    public string $amount = '';

    public string $type = '';

    public string $frequency = '';

    public int $interval = 1;

    public string $startDate = '';

    public ?string $endDate = null;

    public ?int $occurrences = null;

    public bool $active = true;

    public string $editScope = 'future'; // 'future' | 'all'

    public function mount(?int $recurringId = null): void
    {
        if ($recurringId) {
            $this->recurring = RecurringTransaction::with('transactions')
                ->where('user_id', auth()->id())
                ->findOrFail($recurringId);

            $this->recurringId = $recurringId;
            $this->title = $this->recurring->title;
            $this->description = $this->recurring->description ?? '';
            $this->amount = number_format($this->recurring->amount, 2, '.', '');
            $this->type = $this->recurring->type->value;
            $this->frequency = $this->recurring->frequency->value;
            $this->interval = $this->recurring->interval;
            $this->startDate = $this->recurring->start_date->format('Y-m-d');
            $this->endDate = $this->recurring->end_date?->format('Y-m-d');
            $this->occurrences = $this->recurring->occurrences;
            $this->active = $this->recurring->active;
        } else {
            // Reset to defaults when creating new recurring transaction
            $this->title = '';
            $this->description = '';
            $this->amount = '';
            $this->type = 'debit';
            $this->frequency = 'monthly';
            $this->interval = 1;
            $this->startDate = '';
            $this->endDate = null;
            $this->occurrences = null;
            $this->active = true;
        }
    }

    #[Computed]
    public function futureTransactionsCount(): int
    {
        if (! $this->recurring) {
            return 0;
        }

        return Transaction::where('recurring_transaction_id', $this->recurring->id)
            ->where('due_date', '>=', now())
            ->where('status', 'pending')
            ->count();
    }

    #[Computed]
    public function totalTransactionsCount(): int
    {
        if (! $this->recurring) {
            return 0;
        }

        return Transaction::where('recurring_transaction_id', $this->recurring->id)->count();
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:debit,credit',
            'frequency' => 'required|in:weekly,monthly,custom',
            'interval' => 'required|integer|min:1',
            'startDate' => 'required|date',
            'endDate' => 'nullable|date|after:startDate',
            'occurrences' => 'nullable|integer|min:1',
            'editScope' => 'required|in:future,all',
        ], [
            'title.required' => 'O título é obrigatório.',
            'amount.required' => 'O valor é obrigatório.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'frequency.required' => 'A frequência é obrigatória.',
            'interval.min' => 'O intervalo deve ser pelo menos 1.',
            'startDate.required' => 'A data de início é obrigatória.',
            'endDate.after' => 'A data final deve ser posterior à data de início.',
        ]);

        try {
            // Update recurring transaction
            $this->recurring->update([
                'title' => $this->title,
                'description' => $this->description,
                'amount' => $this->amount,
                'type' => TransactionTypeEnum::from($this->type),
                'frequency' => RecurringFrequencyEnum::from($this->frequency),
                'interval' => $this->interval,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'occurrences' => $this->occurrences,
                'active' => $this->active,
            ]);

            // Update existing transactions based on edit scope
            if ($this->editScope === 'all') {
                // Update all pending transactions
                Transaction::where('recurring_transaction_id', $this->recurring->id)
                    ->where('status', 'pending')
                    ->update([
                        'title' => $this->title,
                        'description' => $this->description,
                        'amount' => $this->amount,
                        'type' => $this->type,
                    ]);
            } elseif ($this->editScope === 'future') {
                // Update only future pending transactions
                Transaction::where('recurring_transaction_id', $this->recurring->id)
                    ->where('status', 'pending')
                    ->where('due_date', '>=', now())
                    ->update([
                        'title' => $this->title,
                        'description' => $this->description,
                        'amount' => $this->amount,
                        'type' => $this->type,
                    ]);
            }

            $this->dispatch('recurring-saved');
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Erro ao salvar: '.$e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.recurring-transaction-edit');
    }
}
