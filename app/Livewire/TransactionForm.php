<?php

namespace App\Livewire;

use App\Models\Transaction;
use App\Services\TransactionService;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Component;

class TransactionForm extends Component
{
    use InteractsWithBanner;

    public ?Transaction $transaction = null;

    public function boot()
    {
        $this->withValidator(function ($validator) {
            $validator->after(function ($validator) {
                if ($validator->errors()->any()) {
                    $this->dispatch('validation-failed');
                }
            });
        });
    }

    public ?int $transactionId = null;

    // Form fields
    public string $title = '';

    public string $amount = '';

    public string $dueDate = '';

    public string $type = 'debit';

    public string $status = 'pending';

    public array $selectedTags = [];

    // Recurrence fields
    public bool $isRecurring = false;

    public string $frequency = 'monthly';

    public int $interval = 1;

    public ?string $startDate = null;

    public ?string $endDate = null;

    public ?int $occurrences = null;

    // State
    public bool $editing = false;

    public function mount(?int $transactionId = null): void
    {
        $this->dueDate = now()->format('Y-m-d');
        $this->startDate = now()->format('Y-m-d');

        if ($transactionId) {
            $transaction = Transaction::with(['tags', 'recurringTransaction'])->find($transactionId);

            if ($transaction) {
                $this->editing = true;
                $this->transaction = $transaction;
                $this->transactionId = $transactionId;
                $this->title = $transaction->title;
                $this->amount = (float) $transaction->amount;
                $this->dueDate = $transaction->due_date instanceof \DateTimeInterface ? $transaction->due_date->format('Y-m-d') : now()->format('Y-m-d');
                $this->type = $transaction->type->value;
                $this->status = $transaction->status->value;
                $this->selectedTags = $transaction->tags->pluck('id')->toArray();

                if ($transaction->recurring_transaction_id) {
                    $this->isRecurring = true;
                    $recurring = $transaction->recurringTransaction;
                    $this->frequency = $recurring->frequency->value;
                    $this->interval = $recurring->interval;
                    $this->startDate = $recurring->start_date instanceof \DateTimeInterface ? $recurring->start_date->format('Y-m-d') : null;
                    $this->endDate = $recurring->end_date instanceof \DateTimeInterface ? $recurring->end_date->format('Y-m-d') : null;
                    $this->occurrences = $recurring->occurrences;
                }
            }
        }
    }

    public function save(TransactionService $transactionService): void
    {
        // Remove everything except digits and comma, then convert to float
        if (!empty($this->amount)) {
            $amount = preg_replace('/[^\d,.]/', '', $this->amount);
            // If it has a comma, it's Brazilian format, so we clean accordingly
            if (str_contains($amount, ',')) {
                $amount = str_replace('.', '', $amount); // remove thousand separator
                $amount = str_replace(',', '.', $amount); // change decimal separator
            }
            $this->amount = (float) $amount;
        }

        $this->validate();

        $data = [
            'user_id' => auth()->id(),
            'title' => $this->title,
            'amount' => $this->amount,
            'type' => $this->type,
            'status' => $this->status,
            'due_date' => $this->dueDate,
            'tags' => $this->selectedTags,
        ];

        if ($this->editing) {
            $transactionService->updateTransaction($this->transaction->id, $data);
            $this->dispatch('transaction-saved', id: $this->transaction->id);
            $this->dispatch('notify', message: 'Transação atualizada com sucesso!', type: 'success');
        } else {
            if ($this->isRecurring) {
                $status = $this->status;
                $recurringData = array_merge($data, [
                    'frequency' => $this->frequency,
                    'interval' => $this->interval,
                    'start_date' => $this->startDate ?: $this->dueDate,
                    'end_date' => $this->endDate,
                    'occurrences' => $this->occurrences,
                ]);

                $recurring = $transactionService->createRecurringTransaction($recurringData);
                $this->dispatch('transaction-saved'); // Still general as many might be created
                $this->dispatch('notify', message: 'Recorrência criada com sucesso!', type: 'success');
            } else {
                $transaction = $transactionService->createTransaction($data);
                $this->dispatch('transaction-saved', id: $transaction->id);
                $this->dispatch('notify', message: 'Transação criada com sucesso!', type: 'success');
            }
        }
    }

    protected function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'dueDate' => 'required|date',
            'type' => 'required|in:debit,credit',
            'status' => 'required|in:pending,paid',
            'selectedTags' => 'array',
            'selectedTags.*' => 'exists:tags,id',
        ];

        if ($this->isRecurring && !$this->editing) {
            $rules = array_merge($rules, [
                'frequency' => 'required|in:weekly,monthly,custom',
                'interval' => 'required|integer|min:1',
                'startDate' => 'required|date',
                'endDate' => 'nullable|date|after_or_equal:startDate',
                'occurrences' => 'nullable|integer|min:1',
            ]);
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'amount.required' => 'O valor é obrigatório.',
            'amount.numeric' => 'O valor deve ser um número.',
            'dueDate.required' => 'A data é obrigatória.',
            'type.required' => 'O tipo é obrigatório.',
            'status.required' => 'O status é obrigatório.',
            'frequency.required' => 'A frequência é obrigatória.',
            'interval.required' => 'O intervalo é obrigatório.',
            'startDate.required' => 'A data de início é obrigatória.',
        ];
    }

    public function render()
    {
        return view('livewire.transaction-form');
    }
}
