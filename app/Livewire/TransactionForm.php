<?php

namespace App\Livewire;

use App\Models\Tag;
use App\Models\Transaction;
use App\Services\TransactionService;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TransactionForm extends Component
{
    use InteractsWithBanner;

    public ?Transaction $transaction = null;

    // Form fields
    public string $title = '';

    public string $description = '';

    public $amount = 0;

    public string $type = 'debit';

    public string $status = 'pending';

    public string $dueDate = '';

    public ?string $paidAt = null;

    public array $selectedTags = [];

    // Computed
    public bool $editing = false;

    #[Computed]
    public function tags()
    {
        return Tag::orderBy('name')->get();
    }

    public function mount(?Transaction $transaction = null): void
    {
        if ($transaction && $transaction->exists) {
            $this->editing = true;
            $this->transaction = $transaction;
            $this->title = $transaction->title;
            $this->description = $transaction->description ?? '';
            $this->amount = $transaction->amount;
            $this->type = $transaction->type->value;
            $this->status = $transaction->status->value;
            $this->dueDate = $transaction->due_date->format('Y-m-d');
            $this->paidAt = $transaction->paid_at?->format('Y-m-d\TH:i');
            $this->selectedTags = $transaction->tags->pluck('id')->toArray();
        } else {
            $this->dueDate = now()->format('Y-m-d');
        }
    }

    public function save(TransactionService $transactionService): void
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'amount' => $this->amount,
            'type' => $this->type,
            'status' => $this->status,
            'due_date' => $this->dueDate,
            'paid_at' => $this->paidAt,
            'tags' => $this->selectedTags,
        ];

        if ($this->editing) {
            $transactionService->updateTransaction($this->transaction->id, $data);
        } else {
            $data['user_id'] = auth()->id();
            $transactionService->createTransaction($data);
        }

        $this->dispatch('transaction-saved');
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:debit,credit',
            'status' => 'required|in:pending,paid',
            'dueDate' => 'required|date',
            'paidAt' => 'nullable|date',
            'selectedTags' => 'array',
            'selectedTags.*' => 'exists:tags,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            'amount.required' => 'O valor é obrigatório.',
            'amount.numeric' => 'O valor deve ser um número.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'type.required' => 'O tipo é obrigatório.',
            'type.in' => 'O tipo deve ser débito ou crédito.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser pendente ou pago.',
            'dueDate.required' => 'A data de vencimento é obrigatória.',
            'dueDate.date' => 'A data de vencimento deve ser uma data válida.',
            'paidAt.date' => 'A data de pagamento deve ser uma data válida.',
            'selectedTags.*.exists' => 'Uma ou mais tags selecionadas são inválidas.',
        ];
    }

    public function render()
    {
        return view('livewire.transaction-form');
    }
}
