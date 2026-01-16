# Task 02: Models e Relacionamentos

## Objetivo
Criar os models Eloquent com relacionamentos, casts, fillable/guarded, e traits de Actions/Accessors seguindo o padrão já estabelecido no projeto.

## Contexto
Com as migrations criadas na Task 01, agora vamos criar os models que representam as entidades do sistema. O projeto já utiliza o padrão de organização com traits separadas para Actions e Accessors.

**IMPORTANTE**: Este projeto **NÃO usa scopes**. Métodos de consulta ficam nos Services (exemplo: `getPendingTransactions()` no TransactionService).

## Escopo

### Models a Criar
- [ ] `Transaction` - Transação financeira (débito/crédito)
- [ ] `Tag` - Tag para categorização
- [ ] `NotificationSetting` - Configurações de notificação

### Traits a Criar
- [ ] `TransactionActionTrait` - Ações do modelo Transaction
- [ ] `TransactionAccessorTrait` - Accessors do modelo Transaction
- [ ] `TagAccessorTrait` - Accessors do modelo Tag

## Detalhamento

### Model: Transaction

**Localização**: `app/Models/Transaction.php`

**PHPDoc com Properties**:
```php
/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property float $amount
 * @property TransactionTypeEnum $type
 * @property TransactionStatusEnum $status
 * @property \Carbon\Carbon $due_date
 * @property \Carbon\Carbon|null $paid_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<Tag> $tags
 */
```

**Fillable**:
- `user_id`
- `title`
- `description`
- `amount`
- `type`
- `status`
- `due_date`
- `paid_at`

**Casts**:
- `amount` => 'decimal:2'
- `due_date` => 'date'
- `paid_at` => 'datetime'
- `status` => TransactionStatusEnum::class
- `type` => TransactionTypeEnum::class

**Relationships**:
- `user()` - BelongsTo User
- `tags()` - BelongsToMany Tag (pivot: transaction_tag)

**Traits**:
- `TransactionActionTrait`
- `TransactionAccessorTrait`

**Estrutura Completa**:
```php
<?php

namespace App\Models;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Actions\TransactionActionTrait;
use App\Models\Accessors\TransactionAccessorTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property float $amount
 * @property TransactionTypeEnum $type
 * @property TransactionStatusEnum $status
 * @property \Carbon\Carbon $due_date
 * @property \Carbon\Carbon|null $paid_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<Tag> $tags
 */
class Transaction extends Model
{
    use HasFactory;
    use TransactionActionTrait;
    use TransactionAccessorTrait;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'amount',
        'type',
        'status',
        'due_date',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'status' => TransactionStatusEnum::class,
            'type' => TransactionTypeEnum::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'transaction_tag')
            ->withTimestamps();
    }
}
```

### TransactionActionTrait

**Localização**: `app/Models/Actions/TransactionActionTrait.php`

**Convenções**:
- Todos os métodos retornam `void`
- Métodos devem fazer `$this->save()` após alterações
- Lançar exceções em caso de estado inválido
- Nomes começam com verbos

**Métodos**:

```php
public function markAsPaid(): void
```
- Marca transação como paga
- Define `status = paid` e `paid_at = now()`
- Persiste automaticamente

```php
public function markAsPending(): void
```
- Marca transação como pendente
- Define `status = pending` e `paid_at = null`
- Persiste automaticamente

**Estrutura Completa**:
```php
<?php

namespace App\Models\Actions;

use App\Enums\TransactionStatusEnum;

trait TransactionActionTrait
{
    /**
     * Mark transaction as paid
     */
    public function markAsPaid(): void
    {
        $this->status = TransactionStatusEnum::Paid;
        $this->paid_at = now();
        $this->save();
    }

    /**
     * Mark transaction as pending
     */
    public function markAsPending(): void
    {
        $this->status = TransactionStatusEnum::Pending;
        $this->paid_at = null;
        $this->save();
    }
}
```

### TransactionAccessorTrait

**Localização**: `app/Models/Accessors/TransactionAccessorTrait.php`

**Convenções**:
- Todos os métodos são `public`
- Métodos NÃO têm side effects
- Retornam tipos concretos (string, bool, int, float, etc.)
- Prefixos recomendados: `get`, `calculate`, `format`, `is`, `has`

**Métodos**:

```php
public function getFormattedAmount(): string
```
- Retorna valor formatado como "R$ 1.234,56"

```php
public function getFormattedDueDate(): string
```
- Retorna data formatada como "15/01/2024"

```php
public function isPending(): bool
```
- Verifica se status é pending

```php
public function isPaid(): bool
```
- Verifica se status é paid

```php
public function isOverdue(): bool
```
- Verifica se está vencida (due_date < hoje e status pending)

```php
public function getDaysUntilDue(): int
```
- Retorna dias até vencimento (negativo se vencida)

```php
public function isDebit(): bool
```
- Verifica se type é debit

```php
public function isCredit(): bool
```
- Verifica se type é credit

**Estrutura Completa**:
```php
<?php

namespace App\Models\Accessors;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;

trait TransactionAccessorTrait
{
    /**
     * Get formatted amount in Brazilian currency format
     */
    public function getFormattedAmount(): string
    {
        return 'R$ ' . number_format($this->amount, 2, ',', '.');
    }

    /**
     * Get formatted due date (dd/mm/yyyy)
     */
    public function getFormattedDueDate(): string
    {
        return $this->due_date->format('d/m/Y');
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === TransactionStatusEnum::Pending;
    }

    /**
     * Check if transaction is paid
     */
    public function isPaid(): bool
    {
        return $this->status === TransactionStatusEnum::Paid;
    }

    /**
     * Check if transaction is overdue (past due date and still pending)
     */
    public function isOverdue(): bool
    {
        return $this->isPending() && $this->due_date->isPast();
    }

    /**
     * Get days until due date (negative if overdue)
     */
    public function getDaysUntilDue(): int
    {
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Check if transaction is a debit
     */
    public function isDebit(): bool
    {
        return $this->type === TransactionTypeEnum::Debit;
    }

    /**
     * Check if transaction is a credit
     */
    public function isCredit(): bool
    {
        return $this->type === TransactionTypeEnum::Credit;
    }
}
```

### Model: Tag

**Localização**: `app/Models/Tag.php`

**PHPDoc com Properties**:
```php
/**
 * @property int $id
 * @property string $name
 * @property string|null $color
 * @property \Carbon\Carbon $created_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<Transaction> $transactions
 */
```

**Fillable**:
- `name`
- `color`

**Timestamps**: `const UPDATED_AT = null;` (apenas created_at)

**Relationships**:
- `transactions()` - BelongsToMany Transaction

**Traits**:
- `TagAccessorTrait`

**Estrutura Completa**:
```php
<?php

namespace App\Models;

use App\Models\Accessors\TagAccessorTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $color
 * @property \Carbon\Carbon $created_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<Transaction> $transactions
 */
class Tag extends Model
{
    use HasFactory;
    use TagAccessorTrait;

    public const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'color',
    ];

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class, 'transaction_tag')
            ->withTimestamps();
    }
}
```

### TagAccessorTrait

**Localização**: `app/Models/Accessors/TagAccessorTrait.php`

**Métodos**:

```php
public function getColorWithDefault(): string
```
- Retorna cor da tag ou default (#6B7280) se null

```php
public function getTransactionCount(): int
```
- Conta transações associadas (sem carregar tudo na memória)

**Estrutura Completa**:
```php
<?php

namespace App\Models\Accessors;

trait TagAccessorTrait
{
    /**
     * Get tag color or default gray color
     */
    public function getColorWithDefault(): string
    {
        return $this->color ?? '#6B7280';
    }

    /**
     * Get count of transactions associated with this tag
     */
    public function getTransactionCount(): int
    {
        return $this->transactions()->count();
    }
}
```

### Model: NotificationSetting

**Localização**: `app/Models/NotificationSetting.php`

**PHPDoc com Properties**:
```php
/**
 * @property int $id
 * @property array<string> $emails
 * @property bool $notify_due_today
 * @property bool $notify_overdue
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
```

**Fillable**:
- `emails`
- `notify_due_today`
- `notify_overdue`

**Casts**:
- `emails` => 'array'
- `notify_due_today` => 'boolean'
- `notify_overdue` => 'boolean'

**Método Estático**:
```php
public static function getSettings(): self
```
- Retorna a única configuração do sistema
- Cria se não existir (firstOrCreate)

**Estrutura Completa**:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property array<string> $emails
 * @property bool $notify_due_today
 * @property bool $notify_overdue
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class NotificationSetting extends Model
{
    protected $fillable = [
        'emails',
        'notify_due_today',
        'notify_overdue',
    ];

    protected function casts(): array
    {
        return [
            'emails' => 'array',
            'notify_due_today' => 'boolean',
            'notify_overdue' => 'boolean',
        ];
    }

    /**
     * Get the single notification settings record
     */
    public static function getSettings(): self
    {
        return self::firstOrCreate(
            [],
            [
                'emails' => [],
                'notify_due_today' => true,
                'notify_overdue' => true,
            ]
        );
    }
}
```

### Atualização do Model User

**Localização**: `app/Models/User.php`

**Adicionar ao PHPDoc**:
```php
/**
 * ... existing properties ...
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<Transaction> $transactions
 */
```

**Adicionar Relationship**:
```php
public function transactions(): HasMany
{
    return $this->hasMany(Transaction::class);
}
```

**Adicionar Import**:
```php
use Illuminate\Database\Eloquent\Relations\HasMany;
```

## Comandos Artisan a Usar

```bash
# Criar Models
php artisan make:model Transaction --no-migration
php artisan make:model Tag --no-migration
php artisan make:model NotificationSetting --no-migration

# Criar Traits (usar make:class)
php artisan make:class Models/Actions/TransactionActionTrait
php artisan make:class Models/Accessors/TransactionAccessorTrait
php artisan make:class Models/Accessors/TagAccessorTrait
```

## Convenções

### PHPDoc Properties
- Sempre mapear **todas** as propriedades do model
- Usar `@property` para atributos da tabela
- Usar `@property-read` para relationships e computed properties
- Incluir tipos completos: `\Carbon\Carbon`, `\Illuminate\Database\Eloquent\Collection<Model>`

### Imports
- **Sempre usar imports** no topo do arquivo
- ❌ Evitar: `\App\Enums\TransactionStatusEnum::class`
- ✅ Preferir:
  ```php
  use App\Enums\TransactionStatusEnum;

  // ... depois usar:
  TransactionStatusEnum::class
  ```

### Models
- Models devem usar `protected function casts(): array` (Laravel 12)
- Traits devem estar nos namespaces `App\Models\Actions` e `App\Models\Accessors`
- Relationships devem ter type hints explícitos
- ❌ **NÃO usar scopes** - consultas ficam nos Services
- Fillable deve incluir apenas campos editáveis pelo usuário
- Timestamps padrão (`created_at`, `updated_at`) são automáticos

### Actions (conforme README)
- Todos os métodos retornam `void`
- Nunca retornar `bool`, `array`, `null`, etc.
- Devem lançar exceções em caso de falha
- Nomes começam com verbos: `mark`, `initialize`, `activate`, etc.
- Devem persistir o model (`$this->save()`)

### Accessors (conforme README)
- Métodos NÃO geram side effects
- Retornam tipos concretos
- Prefixos: `get`, `calculate`, `list`, `format`, `is`, `has`
- Nunca modificam o estado do model

## Exemplo de Service com Métodos de Consulta

**Como substituir scopes**: Em vez de `Transaction::pending()`, criar método no Service:

```php
// app/Services/TransactionService.php

public function getPendingTransactions(int $userId): Collection
{
    return Transaction::where('user_id', $userId)
        ->where('status', TransactionStatusEnum::Pending)
        ->orderBy('due_date')
        ->get();
}

public function getOverdueTransactions(int $userId): Collection
{
    return Transaction::where('user_id', $userId)
        ->where('status', TransactionStatusEnum::Pending)
        ->where('due_date', '<', now())
        ->orderBy('due_date')
        ->get();
}

public function getTransactionsDueToday(int $userId): Collection
{
    return Transaction::where('user_id', $userId)
        ->where('status', TransactionStatusEnum::Pending)
        ->whereDate('due_date', today())
        ->get();
}
```

Estes métodos serão implementados na **Task 03** (Services).

## Acceptance Criteria

- [ ] 3 Models criados em `app/Models/`
- [ ] 3 Traits criados (2 Transaction, 1 Tag) nos diretórios corretos
- [ ] Todos os models têm PHPDoc com `@property` mapeando atributos
- [ ] Todos os imports estão no topo (sem paths completos no código)
- [ ] Todos os models têm fillable/guarded definidos
- [ ] Todos os casts implementados corretamente
- [ ] Relacionamentos implementados com type hints
- [ ] **NÃO há scopes** nos models
- [ ] Actions trait com métodos que retornam `void`
- [ ] Accessors trait com métodos que retornam tipos concretos
- [ ] Model User atualizado com relationship `transactions()`
- [ ] Código segue padrão já existente do projeto (verificar User.php)
- [ ] NotificationSetting tem método estático `getSettings()`

## Dependências
- Task 01 completa (Migrations e Enums criados)

## Próxima Task
Task 03: Services (Lógica de Negócio) - onde métodos de consulta serão implementados

## Observações
- Seguir o padrão já estabelecido em `app/Models/User.php`
- Verificar estrutura de traits Actions/Accessors existentes
- Models devem ter apenas: PHPDoc, fillable, casts, relationships
- **Lógica de negócio e consultas vão para Services** (Task 03)
- Scopes não são usados neste projeto
- Actions e Accessors seguem convenções dos READMEs em suas respectivas pastas
