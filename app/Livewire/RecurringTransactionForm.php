<?php

namespace App\Livewire;

use App\Models\RecurringTransaction;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Component;

class RecurringTransactionForm extends Component
{
    use InteractsWithBanner;

    public ?RecurringTransaction $recurring = null;

    public ?int $recurringId = null;

    public string $editScope = 'future_only'; // 'future_only' ou 'current_and_future'

    // Form fields
    public string $title = '';

    public string $description = '';

    public string $amount = '';

    public string $type = 'debit';

    public string $frequency = 'monthly';

    public int $interval = 1;

    public ?string $startDate = null;

    public ?string $endDate = null;

    public ?int $occurrences = null;

    public array $selectedTags = [];

    public bool $editing = false;

    public function mount(?int $recurringId = null): void
    {
        if ($recurringId) {
            $recurring = RecurringTransaction::where('user_id', auth()->id())
                ->with('transactions')
                ->find($recurringId);

            if ($recurring) {
                $this->editing = true;
                $this->recurring = $recurring;
                $this->recurringId = $recurringId;

                $this->title = $recurring->title;
                $this->description = $recurring->description ?? '';
                $this->amount = number_format((float) $recurring->amount, 2, '.', '');
                $this->type = $recurring->type->value;
                $this->frequency = $recurring->frequency->value;
                $this->interval = $recurring->interval;
                $this->startDate = $recurring->start_date instanceof \DateTimeInterface ? $recurring->start_date->format('Y-m-d') : null;
                $this->endDate = $recurring->end_date instanceof \DateTimeInterface ? $recurring->end_date->format('Y-m-d') : null;
                $this->occurrences = $recurring->occurrences;

                // Load tags from the recurring transaction
                $this->selectedTags = $recurring->tags->pluck('id')->toArray();
            }
        }
    }

    public function save(): void
    {
        // Remove 'R$', dots and replace comma with dot, but ONLY if not empty
        if (!empty($this->amount)) {
            $this->amount = (float) str_replace(['R$', '.', ','], ['', '', '.'], $this->amount);
        }

        $this->validate();

        if (!$this->editing || !$this->recurring) {
            $this->dispatch('notify', message: 'Recorrência não encontrada.', type: 'error');

            return;
        }

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'amount' => $this->amount,
            'type' => $this->type,
            'frequency' => $this->frequency,
            'interval' => $this->interval,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'occurrences' => $this->occurrences,
        ];

        // Update recurring transaction
        $this->recurring->update($data);
        $this->recurring->tags()->sync($this->selectedTags);

        // If editScope is 'current_and_future', update existing pending transactions too
        if ($this->editScope === 'current_and_future') {
            $this->recurring->transactions()
                ->where('status', 'pending')
                ->where('due_date', '>=', now())
                ->update([
                    'title' => $this->title,
                    'description' => $this->description,
                    'amount' => $this->amount,
                    'type' => $this->type,
                ]);

            // Update tags for pending transactions
            $this->recurring->transactions()
                ->where('status', 'pending')
                ->where('due_date', '>=', now())
                ->each(function ($transaction) {
                    $transaction->tags()->sync($this->selectedTags);
                });
        }

        $this->dispatch('recurring-saved');
        $this->dispatch('notify', message: 'Recorrência atualizada com sucesso!', type: 'success');
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:debit,credit',
            'frequency' => 'required|in:weekly,monthly,custom',
            'interval' => 'required|integer|min:1',
            'startDate' => 'required|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
            'occurrences' => 'nullable|integer|min:1',
            'selectedTags' => 'array',
            'selectedTags.*' => 'exists:tags,id',
            'editScope' => 'in:future_only,current_and_future',
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.string' => 'O título deve ser um texto.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            'description.string' => 'A descrição deve ser um texto.',
            'amount.required' => 'O valor é obrigatório.',
            'amount.numeric' => 'O valor deve ser um número.',
            'amount.min' => 'O valor deve ser maior ou igual a zero.',
            'type.required' => 'O tipo é obrigatório.',
            'type.in' => 'O tipo deve ser débito ou crédito.',
            'frequency.required' => 'A frequência é obrigatória.',
            'frequency.in' => 'A frequência deve ser semanal, mensal ou personalizada.',
            'interval.required' => 'O intervalo é obrigatório.',
            'interval.integer' => 'O intervalo deve ser um número inteiro.',
            'interval.min' => 'O intervalo deve ser pelo menos 1.',
            'startDate.date' => 'A data de início deve ser uma data válida.',
            'endDate.date' => 'A data de término deve ser uma data válida.',
            'endDate.after' => 'A data de término deve ser posterior à data de início.',
            'occurrences.integer' => 'O número de ocorrências deve ser um número inteiro.',
            'occurrences.min' => 'O número de ocorrências deve ser pelo menos 1.',
            'selectedTags.*.exists' => 'Uma ou mais tags selecionadas são inválidas.',
        ];
    }

    public function render()
    {
        return view('livewire.recurring-transaction-form');
    }
}
