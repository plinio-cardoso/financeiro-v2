<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $transaction = $this->route('transaction');

        // Apenas o dono da transação pode atualizar
        return $transaction && $transaction->user_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'amount' => 'sometimes|required|numeric|min:0.01|max:999999999.99',
            'type' => 'sometimes|required|in:debit,credit',
            'status' => 'sometimes|required|in:pending,paid',
            'due_date' => 'sometimes|required|date',
            'paid_at' => 'nullable|date|required_if:status,paid',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            'amount.required' => 'O valor é obrigatório.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'amount.max' => 'O valor não pode exceder R$ 999.999.999,99.',
            'due_date.required' => 'A data de vencimento é obrigatória.',
            'paid_at.required_if' => 'A data de pagamento é obrigatória quando o status é "pago".',
            'tags.*.exists' => 'Uma ou mais tags selecionadas são inválidas.',
        ];
    }
}
