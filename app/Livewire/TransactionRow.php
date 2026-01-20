<?php

namespace App\Livewire;

use App\Models\Transaction;
use Livewire\Component;

class TransactionRow extends Component
{
    public Transaction $transaction;

    protected function rules(): array
    {
        return [
            'transaction.title' => 'required|string|max:255',
            'transaction.amount' => 'required|numeric|min:0.01',
            'transaction.due_date' => 'required|date',
        ];
    }

    /**
     * Update inline field (only title, amount, due_date)
     */
    public function updateField(string $field, $value): void
    {
        try {
            // Validate field name
            if (! in_array($field, ['title', 'amount', 'due_date'])) {
                $this->dispatch('notify', message: 'Campo inválido.', type: 'error');

                return;
            }

            // For amount, convert comma to dot
            if ($field === 'amount') {
                $value = str_replace(',', '.', $value);
            }

            // Update the field
            $this->transaction->{$field} = $value;

            // Validate
            $this->validateOnly("transaction.{$field}");

            // Save
            $this->transaction->save();

            // Refresh the transaction to get updated values
            $this->transaction->refresh();

            $fieldNames = [
                'title' => 'Título',
                'amount' => 'Valor',
                'due_date' => 'Data de vencimento',
            ];

            $this->dispatch('notify',
                message: "{$fieldNames[$field]} atualizado com sucesso!",
                type: 'success'
            );

            // Notify parent to recalculate aggregates
            $this->dispatch('transaction-updated');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('notify',
                message: $e->validator->errors()->first(),
                type: 'error'
            );
            // Re-throw to let Alpine.js know the update failed
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('notify',
                message: 'Erro ao atualizar: '.$e->getMessage(),
                type: 'error'
            );
            // Re-throw to let Alpine.js know the update failed
            throw $e;
        }
    }

    /**
     * Mark transaction as paid
     */
    public function markAsPaid(): void
    {
        try {
            if ($this->transaction->status->value === 'paid') {
                $this->dispatch('notify',
                    message: 'Esta transação já está marcada como paga.',
                    type: 'info'
                );

                return;
            }

            $this->transaction->markAsPaid();

            $this->dispatch('notify',
                message: 'Transação marcada como paga com sucesso!',
                type: 'success'
            );

            // Notify parent to recalculate aggregates
            $this->dispatch('transaction-updated');

        } catch (\Exception $e) {
            $this->dispatch('notify',
                message: 'Erro ao marcar como paga: '.$e->getMessage(),
                type: 'error'
            );
        }
    }

    /**
     * Open edit modal in parent component
     */
    public function edit(): void
    {
        $this->dispatch('open-edit-modal', transactionId: $this->transaction->id);
    }

    public function render()
    {
        return view('livewire.transaction-row');
    }
}
