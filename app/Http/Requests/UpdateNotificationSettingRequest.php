<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Apenas usuários autenticados podem atualizar configurações
        // Futuramente pode incluir verificação de admin
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'emails' => 'required|array|min:1|max:10',
            'emails.*' => 'required|email:rfc',
            'notify_due_today' => 'nullable|boolean',
            'notify_overdue' => 'nullable|boolean',
        ];
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'notify_due_today' => filter_var($this->input('notify_due_today'), FILTER_VALIDATE_BOOLEAN),
            'notify_overdue' => filter_var($this->input('notify_overdue'), FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'emails.required' => 'Pelo menos um email deve ser fornecido.',
            'emails.min' => 'Pelo menos um email deve ser fornecido.',
            'emails.max' => 'Você pode cadastrar no máximo 10 emails.',
            'emails.*.required' => 'Todos os emails devem ser preenchidos.',
            'emails.*.email' => 'Um ou mais emails são inválidos.',
            'notify_due_today.boolean' => 'A configuração de notificação deve ser verdadeiro ou falso.',
            'notify_overdue.boolean' => 'A configuração de notificação deve ser verdadeiro ou falso.',
        ];
    }
}
