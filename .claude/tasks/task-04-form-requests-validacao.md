# Task 04: Form Requests - Validação

## Objetivo
Criar Form Request classes para validar todas as entradas de dados do sistema, mantendo a lógica de validação fora dos Controllers.

## Contexto
Form Requests centralizam toda a validação de dados. Eles também podem conter lógica de autorização. Seguindo as boas práticas do Laravel, validação nunca deve estar inline nos controllers.

## Escopo

### Form Requests a Criar
- [ ] `StoreTransactionRequest` - Validação para criar transação
- [ ] `UpdateTransactionRequest` - Validação para atualizar transação
- [ ] `UpdateNotificationSettingRequest` - Validação para configurações de notificação

## Detalhamento

### StoreTransactionRequest

**Localização**: `app/Http/Requests/StoreTransactionRequest.php`

**Autorização**:
```php
public function authorize(): bool
{
    // Usuário autenticado pode criar transações
    return true;
}
```

**Regras de Validação**:
```php
public function rules(): array
{
    return [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'amount' => 'required|numeric|min:0.01|max:999999999.99',
        'type' => 'required|in:debit,credit',
        'status' => 'required|in:pending,paid',
        'due_date' => 'required|date|after_or_equal:today',
        'paid_at' => 'nullable|date|required_if:status,paid',
        'tags' => 'nullable|array',
        'tags.*' => 'exists:tags,id',
    ];
}
```

**Mensagens Customizadas**:
```php
public function messages(): array
{
    return [
        'title.required' => 'O título é obrigatório.',
        'title.max' => 'O título não pode ter mais de 255 caracteres.',
        'amount.required' => 'O valor é obrigatório.',
        'amount.min' => 'O valor deve ser maior que zero.',
        'amount.max' => 'O valor não pode exceder R$ 999.999.999,99.',
        'due_date.required' => 'A data de vencimento é obrigatória.',
        'due_date.after_or_equal' => 'A data de vencimento não pode ser no passado.',
        'paid_at.required_if' => 'A data de pagamento é obrigatória quando o status é "pago".',
        'tags.*.exists' => 'Uma ou mais tags selecionadas são inválidas.',
    ];
}
```

**Método Adicional**:
```php
protected function prepareForValidation(): void
{
    // Adiciona user_id automaticamente do usuário autenticado
    $this->merge([
        'user_id' => auth()->id(),
    ]);
}
```

### UpdateTransactionRequest

**Localização**: `app/Http/Requests/UpdateTransactionRequest.php`

**Autorização**:
```php
public function authorize(): bool
{
    $transaction = $this->route('transaction');

    // Apenas o dono da transação pode atualizar
    return $transaction && $transaction->user_id === auth()->id();
}
```

**Regras de Validação**:
```php
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
```

**Mensagens Customizadas**:
```php
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
```

**Observação**: Usa `sometimes` para permitir atualização parcial (PATCH)

### UpdateNotificationSettingRequest

**Localização**: `app/Http/Requests/UpdateNotificationSettingRequest.php`

**Autorização**:
```php
public function authorize(): bool
{
    // Apenas usuários autenticados podem atualizar configurações
    // Futuramente pode incluir verificação de admin
    return true;
}
```

**Regras de Validação**:
```php
public function rules(): array
{
    return [
        'emails' => 'required|array|min:1|max:10',
        'emails.*' => 'required|email:rfc,dns',
        'notify_due_today' => 'required|boolean',
        'notify_overdue' => 'required|boolean',
    ];
}
```

**Mensagens Customizadas**:
```php
public function messages(): array
{
    return [
        'emails.required' => 'Pelo menos um email deve ser fornecido.',
        'emails.min' => 'Pelo menos um email deve ser fornecido.',
        'emails.max' => 'Você pode cadastrar no máximo 10 emails.',
        'emails.*.required' => 'Todos os emails devem ser preenchidos.',
        'emails.*.email' => 'Um ou mais emails são inválidos.',
        'notify_due_today.required' => 'A configuração de notificação para "vence hoje" é obrigatória.',
        'notify_due_today.boolean' => 'A configuração de notificação deve ser verdadeiro ou falso.',
        'notify_overdue.required' => 'A configuração de notificação para "vencidas" é obrigatória.',
        'notify_overdue.boolean' => 'A configuração de notificação deve ser verdadeiro ou falso.',
    ];
}
```

## Comandos Artisan a Usar

```bash
php artisan make:request StoreTransactionRequest
php artisan make:request UpdateTransactionRequest
php artisan make:request UpdateNotificationSettingRequest
```

## Estrutura de Exemplo

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            // ... outras regras
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            // ... outras mensagens
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }
}
```

## Uso nos Controllers

```php
public function store(StoreTransactionRequest $request, TransactionService $transactionService)
{
    $transaction = $transactionService->createTransaction($request->validated());

    return redirect()->route('transactions.index')
        ->with('success', 'Transação criada com sucesso!');
}
```

## Regras de Validação Importantes

### Amount (Valor)
- Mínimo: 0.01 (1 centavo)
- Máximo: 999999999.99 (999 milhões)
- Formato decimal com 2 casas

### Due Date (Data de Vencimento)
- Na criação: `after_or_equal:today` (não pode ser passado)
- Na atualização: sem restrição de data (permite editar transações antigas)

### Paid At
- Requerido apenas se `status = paid`
- Nullable em outros casos

### Tags
- Opcional (nullable)
- Deve ser array
- Cada tag deve existir na tabela `tags`

### Emails (NotificationSetting)
- Mínimo 1, máximo 10 emails
- Validação com `email:rfc,dns` (valida sintaxe e domínio)

## Convenções

- Form Requests ficam em `app/Http/Requests/`
- Sempre implementar `authorize()` e `rules()`
- Mensagens customizadas em português
- Usar `messages()` para mensagens de erro
- Usar `prepareForValidation()` para manipular dados antes da validação
- Type hint Form Request nos controllers
- Usar `$request->validated()` para pegar dados validados
- Nomes: `StoreModelRequest`, `UpdateModelRequest`

## Acceptance Criteria

- [ ] 3 Form Requests criados em `app/Http/Requests/`
- [ ] Todos têm método `authorize()` implementado
- [ ] Todos têm método `rules()` implementado
- [ ] Todos têm método `messages()` com mensagens em português
- [ ] StoreTransactionRequest adiciona user_id automaticamente
- [ ] UpdateTransactionRequest verifica ownership na autorização
- [ ] Validações cobrem todos os campos necessários
- [ ] Mensagens de erro são claras e em português
- [ ] Rules seguem convenções do Laravel

## Dependências
- Task 01 completa (Enums para validação de type e status)
- Task 02 completa (Models para validação de exists)

## Próxima Task
Task 05: Controllers (HTTP)

## Observações
- Validação SEMPRE em Form Requests, nunca inline
- Form Requests também podem ter lógica de autorização
- Use `$request->validated()` nos controllers para garantir apenas dados validados
- Mensagens em português melhoram UX para usuários brasileiros
