<?php

namespace App\Livewire;

use App\Models\Transaction;
use App\Services\TagService;
use App\Services\TransactionService;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Attributes\Computed;
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

    // Form fields (removidos: title, amount, dueDate - são inline)
    public string $description = '';

    public string $type = 'debit';

    public string $status = 'pending';

    public array $selectedTags = [];

    // Computed
    public bool $editing = false;

    #[Computed]
    public function tags()
    {
        return app(TagService::class)->getUserTags(auth()->id());
    }

    public function mount(?int $transactionId = null): void
    {
        if ($transactionId) {
            $transaction = Transaction::with('tags')->find($transactionId);

            if ($transaction) {
                $this->editing = true;
                $this->transaction = $transaction;
                $this->description = $transaction->description ?? '';
                $this->type = $transaction->type->value;
                $this->status = $transaction->status->value;
                $this->selectedTags = $transaction->tags->pluck('id')->toArray();
            }
        }
    }

    public function save(TransactionService $transactionService): void
    {
        $this->validate();

        if ($this->editing) {
            // Editing existing transaction
            $data = [
                'description' => $this->description,
                'type' => $this->type,
                'status' => $this->status,
                'tags' => $this->selectedTags,
            ];

            // Update only this transaction
            $transactionService->updateTransaction($this->transaction->id, $data);
            $this->dispatch('transaction-saved');
        } else {
            // Creating new transaction
            $this->dispatch('notify', message: 'Por favor, crie transações usando o botão "Nova Transação"', type: 'info');
        }
    }

    protected function rules(): array
    {
        return [
            'description' => 'nullable|string',
            'type' => 'required|in:debit,credit',
            'status' => 'required|in:pending,paid',
            'selectedTags' => 'array',
            'selectedTags.*' => 'exists:tags,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'description.string' => 'A descrição deve ser um texto.',
            'type.required' => 'O tipo é obrigatório.',
            'type.in' => 'O tipo deve ser débito ou crédito.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser pendente ou pago.',
            'selectedTags.*.exists' => 'Uma ou mais tags selecionadas são inválidas.',
        ];
    }

    public function render()
    {
        return view('livewire.transaction-form');
    }
}
