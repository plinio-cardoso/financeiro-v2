# Task 09: Testes Unitários e Funcionais

## Objetivo
Implementar suite completa de testes para garantir qualidade e funcionamento correto de todas as features do sistema de controle financeiro doméstico.

## Contexto
Esta é a **última task** da implementação. Com toda a funcionalidade implementada, vamos criar testes abrangentes que cobrem models, services, controllers, Livewire components e commands.

## Escopo

### Testes de Models
- [ ] `TransactionTest` - Testar model, relationships, scopes, casts
- [ ] `TagTest` - Testar model e relationships
- [ ] `NotificationSettingTest` - Testar model e método getSettings()

### Testes de Services
- [ ] `TransactionServiceTest` - Testar toda lógica de negócio
- [ ] `DashboardServiceTest` - Testar cálculos e estatísticas
- [ ] `NotificationServiceTest` - Testar envio de notificações
- [ ] `MailgunServiceTest` - Testar abstração do Mailgun

### Testes de Controllers
- [ ] `DashboardControllerTest` - Testar acesso ao dashboard
- [ ] `TransactionControllerTest` - Testar CRUD completo
- [ ] `NotificationSettingControllerTest` - Testar configurações

### Testes de Livewire
- [ ] `DashboardStatsTest` - Testar exibição de estatísticas
- [ ] `TransactionListTest` - Testar filtros, ordenação, paginação
- [ ] `TransactionFormTest` - Testar criação e edição
- [ ] `TransactionActionsTest` - Testar ações (marcar pago, excluir)

### Testes de Commands
- [ ] `NotifyDueTodayCommandTest` - Testar notificação de vencimentos
- [ ] `NotifyOverdueCommandTest` - Testar notificação de vencidas

## Detalhamento por Tipo de Teste

---

## 1. Testes de Models

### TransactionTest

**Localização**: `tests/Feature/Models/TransactionTest.php`

**Cenários a Testar**:

#### Relationships
```php
public function test_transaction_belongs_to_user(): void
public function test_transaction_has_many_tags(): void
```

#### Casts
```php
public function test_amount_is_cast_to_decimal(): void
public function test_due_date_is_cast_to_date(): void
public function test_paid_at_is_cast_to_datetime(): void
public function test_status_is_cast_to_enum(): void
public function test_type_is_cast_to_enum(): void
```

#### Scopes
```php
public function test_pending_scope_returns_only_pending_transactions(): void
public function test_paid_scope_returns_only_paid_transactions(): void
public function test_debits_scope_returns_only_debits(): void
public function test_credits_scope_returns_only_credits(): void
public function test_due_today_scope_returns_transactions_due_today(): void
public function test_overdue_scope_returns_overdue_pending_transactions(): void
public function test_for_month_scope_returns_transactions_for_specific_month(): void
public function test_for_user_scope_returns_transactions_for_specific_user(): void
```

#### Actions (TransactionActionTrait)
```php
public function test_mark_as_paid_sets_status_and_paid_at(): void
public function test_mark_as_pending_clears_paid_at(): void
```

#### Accessors (TransactionAccessorTrait)
```php
public function test_get_formatted_amount_returns_brazilian_format(): void
public function test_get_formatted_due_date_returns_dd_mm_yyyy(): void
public function test_is_pending_returns_true_when_pending(): void
public function test_is_paid_returns_true_when_paid(): void
public function test_is_overdue_returns_true_when_past_due_and_pending(): void
public function test_get_days_until_due_returns_correct_count(): void
public function test_is_debit_returns_true_for_debit_type(): void
public function test_is_credit_returns_true_for_credit_type(): void
```

### TagTest

**Localização**: `tests/Feature/Models/TagTest.php`

**Cenários**:
```php
public function test_tag_has_many_transactions(): void
public function test_get_color_with_default_returns_default_when_null(): void
public function test_get_transaction_count_returns_correct_count(): void
```

### NotificationSettingTest

**Localização**: `tests/Feature/Models/NotificationSettingTest.php`

**Cenários**:
```php
public function test_emails_is_cast_to_array(): void
public function test_get_settings_returns_existing_settings(): void
public function test_get_settings_creates_new_if_not_exists(): void
```

---

## 2. Testes de Services

### TransactionServiceTest

**Localização**: `tests/Feature/Services/TransactionServiceTest.php`

**Cenários**:

#### Create
```php
public function test_create_transaction_creates_record_in_database(): void
public function test_create_transaction_associates_tags(): void
public function test_create_transaction_sets_user_id(): void
```

#### Update
```php
public function test_update_transaction_updates_fields(): void
public function test_update_transaction_syncs_tags(): void
```

#### Delete
```php
public function test_delete_transaction_removes_from_database(): void
public function test_delete_transaction_removes_tag_associations(): void
```

#### Mark as Paid/Pending
```php
public function test_mark_as_paid_sets_status_and_date(): void
public function test_mark_as_pending_clears_paid_at(): void
```

#### Calculations
```php
public function test_calculate_monthly_totals_returns_correct_values(): void
public function test_calculate_monthly_totals_only_includes_debits(): void
public function test_calculate_monthly_totals_separates_paid_and_pending(): void
public function test_get_next_month_total_calculates_correctly(): void
```

#### Filtering
```php
public function test_get_filtered_transactions_filters_by_search(): void
public function test_get_filtered_transactions_filters_by_date_range(): void
public function test_get_filtered_transactions_filters_by_tags(): void
public function test_get_filtered_transactions_filters_by_status(): void
public function test_get_filtered_transactions_filters_by_type(): void
public function test_get_filtered_transactions_sorts_by_field(): void
public function test_get_filtered_transactions_eager_loads_tags(): void
```

### DashboardServiceTest

**Localização**: `tests/Feature/Services/DashboardServiceTest.php`

**Cenários**:
```php
public function test_get_current_month_stats_returns_correct_totals(): void
public function test_get_current_month_stats_only_includes_debits(): void
public function test_get_current_month_stats_includes_overdue_count(): void
public function test_get_monthly_transactions_returns_correct_month(): void
public function test_get_monthly_transactions_defaults_to_current_month(): void
```

### NotificationServiceTest

**Localização**: `tests/Feature/Services/NotificationServiceTest.php`

**Cenários**:
```php
public function test_send_due_today_notifications_sends_emails(): void
public function test_send_due_today_notifications_only_sends_for_debits(): void
public function test_send_due_today_notifications_respects_settings(): void
public function test_send_due_today_notifications_returns_count(): void
public function test_send_overdue_notifications_sends_emails(): void
public function test_send_overdue_notifications_only_sends_for_pending(): void
public function test_send_overdue_notifications_respects_settings(): void
```

**Importante**: Usar `Mail::fake()` ou mock do MailgunService

### MailgunServiceTest

**Localização**: `tests/Feature/Services/MailgunServiceTest.php`

**Cenários**:
```php
public function test_send_method_calls_mailgun_api(): void
public function test_send_method_renders_view_correctly(): void
public function test_send_method_returns_true_on_success(): void
public function test_send_method_logs_errors_on_failure(): void
```

**Importante**: Usar `Http::fake()` para mock da API

---

## 3. Testes de Controllers

### DashboardControllerTest

**Localização**: `tests/Feature/Controllers/DashboardControllerTest.php`

**Cenários**:
```php
public function test_index_requires_authentication(): void
public function test_index_returns_dashboard_view(): void
public function test_index_passes_stats_to_view(): void
```

### TransactionControllerTest

**Localização**: `tests/Feature/Controllers/TransactionControllerTest.php`

**Cenários**:

#### Index
```php
public function test_index_requires_authentication(): void
public function test_index_displays_user_transactions(): void
```

#### Create
```php
public function test_create_requires_authentication(): void
public function test_create_displays_form(): void
public function test_create_loads_tags(): void
```

#### Store
```php
public function test_store_requires_authentication(): void
public function test_store_validates_input(): void
public function test_store_creates_transaction(): void
public function test_store_redirects_with_success_message(): void
```

#### Edit
```php
public function test_edit_requires_authentication(): void
public function test_edit_displays_form_with_transaction(): void
public function test_edit_loads_tags(): void
```

#### Update
```php
public function test_update_requires_authentication(): void
public function test_update_validates_ownership(): void
public function test_update_validates_input(): void
public function test_update_updates_transaction(): void
public function test_update_redirects_with_success_message(): void
```

#### Destroy
```php
public function test_destroy_requires_authentication(): void
public function test_destroy_validates_ownership(): void
public function test_destroy_deletes_transaction(): void
public function test_destroy_redirects_with_success_message(): void
```

### NotificationSettingControllerTest

**Localização**: `tests/Feature/Controllers/NotificationSettingControllerTest.php`

**Cenários**:
```php
public function test_edit_requires_authentication(): void
public function test_edit_displays_form_with_settings(): void
public function test_update_requires_authentication(): void
public function test_update_validates_emails(): void
public function test_update_updates_settings(): void
public function test_update_redirects_with_success_message(): void
```

---

## 4. Testes de Livewire

### DashboardStatsTest

**Localização**: `tests/Feature/Livewire/DashboardStatsTest.php`

**Cenários**:
```php
public function test_component_renders_successfully(): void
public function test_component_displays_current_month_stats(): void
public function test_component_displays_formatted_amounts(): void
```

### TransactionListTest

**Localização**: `tests/Feature/Livewire/TransactionListTest.php`

**Cenários**:

#### Rendering
```php
public function test_component_renders_successfully(): void
public function test_component_displays_transactions(): void
public function test_component_displays_empty_state(): void
```

#### Filtering
```php
public function test_search_filter_works(): void
public function test_date_range_filter_works(): void
public function test_status_filter_works(): void
public function test_type_filter_works(): void
public function test_tags_filter_works(): void
public function test_clear_filters_resets_all_filters(): void
```

#### Sorting
```php
public function test_sort_by_title_works(): void
public function test_sort_by_amount_works(): void
public function test_sort_by_due_date_works(): void
public function test_sort_direction_toggles(): void
```

#### Pagination
```php
public function test_pagination_works(): void
public function test_search_resets_to_first_page(): void
```

### TransactionFormTest

**Localização**: `tests/Feature/Livewire/TransactionFormTest.php`

**Cenários**:

#### Create Mode
```php
public function test_component_renders_in_create_mode(): void
public function test_create_validates_required_fields(): void
public function test_create_saves_transaction_successfully(): void
public function test_create_associates_tags(): void
public function test_create_emits_transaction_saved_event(): void
public function test_create_shows_success_banner(): void
```

#### Edit Mode
```php
public function test_component_renders_in_edit_mode(): void
public function test_edit_loads_transaction_data(): void
public function test_edit_validates_required_fields(): void
public function test_edit_updates_transaction_successfully(): void
public function test_edit_updates_tags(): void
public function test_edit_emits_transaction_saved_event(): void
```

#### Conditional Fields
```php
public function test_paid_at_field_shows_when_status_is_paid(): void
public function test_paid_at_field_validates_when_status_is_paid(): void
```

### TransactionActionsTest

**Localização**: `tests/Feature/Livewire/TransactionActionsTest.php`

**Cenários**:
```php
public function test_component_renders_successfully(): void
public function test_toggle_paid_status_marks_as_paid(): void
public function test_toggle_paid_status_marks_as_pending(): void
public function test_toggle_paid_status_emits_event(): void
public function test_confirm_delete_opens_modal(): void
public function test_delete_removes_transaction(): void
public function test_delete_emits_event(): void
public function test_delete_redirects_to_list(): void
```

---

## 5. Testes de Commands

### NotifyDueTodayCommandTest

**Localização**: `tests/Feature/Commands/NotifyDueTodayCommandTest.php`

**Cenários**:
```php
public function test_command_executes_successfully(): void
public function test_command_sends_notifications_for_due_today(): void
public function test_command_only_sends_for_pending_debits(): void
public function test_command_respects_notification_settings(): void
public function test_command_returns_success_code(): void
public function test_command_outputs_count(): void
public function test_command_does_nothing_when_no_transactions(): void
```

### NotifyOverdueCommandTest

**Localização**: `tests/Feature/Commands/NotifyOverdueCommandTest.php`

**Cenários**:
```php
public function test_command_executes_successfully(): void
public function test_command_sends_notifications_for_overdue(): void
public function test_command_only_sends_for_pending_debits(): void
public function test_command_respects_notification_settings(): void
public function test_command_returns_success_code(): void
public function test_command_outputs_count(): void
public function test_command_does_nothing_when_no_transactions(): void
```

---

## Estrutura de Teste Exemplo

### Model Test
```php
<?php

namespace Tests\Feature\Models;

use App\Models\Transaction;
use App\Models\User;
use App\Enums\TransactionStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();

        $this->assertInstanceOf(User::class, $transaction->user);
        $this->assertEquals($user->id, $transaction->user_id);
    }

    public function test_pending_scope_returns_only_pending_transactions(): void
    {
        Transaction::factory()->create(['status' => TransactionStatusEnum::Pending]);
        Transaction::factory()->create(['status' => TransactionStatusEnum::Paid]);

        $pending = Transaction::pending()->get();

        $this->assertCount(1, $pending);
        $this->assertTrue($pending->first()->isPending());
    }

    // ... mais testes
}
```

### Service Test
```php
<?php

namespace Tests\Feature\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $transactionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transactionService = app(TransactionService::class);
    }

    public function test_create_transaction_creates_record_in_database(): void
    {
        $user = User::factory()->create();

        $data = [
            'user_id' => $user->id,
            'title' => 'Test Transaction',
            'amount' => 100.00,
            'type' => 'debit',
            'status' => 'pending',
            'due_date' => '2024-12-25',
        ];

        $transaction = $this->transactionService->createTransaction($data);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Test Transaction',
            'user_id' => $user->id,
        ]);
    }

    // ... mais testes
}
```

### Controller Test
```php
<?php

namespace Tests\Feature\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('transactions.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_store_creates_transaction(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('transactions.store'), [
                'title' => 'Test Transaction',
                'amount' => 100.00,
                'type' => 'debit',
                'status' => 'pending',
                'due_date' => '2024-12-25',
            ]);

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'title' => 'Test Transaction',
            'user_id' => $user->id,
        ]);
    }

    // ... mais testes
}
```

### Livewire Test
```php
<?php

namespace Tests\Feature\Livewire;

use App\Livewire\TransactionList;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionListTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_successfully(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(TransactionList::class)
            ->assertStatus(200);
    }

    public function test_search_filter_works(): void
    {
        $user = User::factory()->create();
        Transaction::factory()->for($user)->create(['title' => 'Electricity Bill']);
        Transaction::factory()->for($user)->create(['title' => 'Water Bill']);

        Livewire::actingAs($user)
            ->test(TransactionList::class)
            ->set('search', 'Electricity')
            ->assertSee('Electricity Bill')
            ->assertDontSee('Water Bill');
    }

    // ... mais testes
}
```

### Command Test
```php
<?php

namespace Tests\Feature\Commands;

use App\Models\Transaction;
use App\Models\NotificationSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotifyDueTodayCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_executes_successfully(): void
    {
        $this->artisan('transactions:notify-due-today')
            ->assertExitCode(0);
    }

    public function test_command_sends_notifications_for_due_today(): void
    {
        Mail::fake();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => true,
        ]);

        Transaction::factory()->create([
            'due_date' => today(),
            'status' => 'pending',
            'type' => 'debit',
        ]);

        $this->artisan('transactions:notify-due-today')
            ->assertExitCode(0);

        Mail::assertSent(function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    // ... mais testes
}
```

---

## Factories

### TransactionFactory

**Localização**: `database/factories/TransactionFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\User;
use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'type' => fake()->randomElement([TransactionTypeEnum::Debit, TransactionTypeEnum::Credit]),
            'status' => fake()->randomElement([TransactionStatusEnum::Pending, TransactionStatusEnum::Paid]),
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'paid_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatusEnum::Pending,
            'paid_at' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatusEnum::Paid,
            'paid_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function debit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionTypeEnum::Debit,
        ]);
    }

    public function credit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionTypeEnum::Credit,
        ]);
    }

    public function dueToday(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => today(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'status' => TransactionStatusEnum::Pending,
        ]);
    }
}
```

### TagFactory

**Localização**: `database/factories/TagFactory.php`

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'color' => fake()->hexColor(),
        ];
    }
}
```

---

## Convenções de Teste

### Nomenclatura
- Método de teste: `test_<action>_<condition>_<expected_result>`
- Seja descritivo: `test_user_can_create_transaction_when_authenticated`
- Evite abreviações

### Estrutura AAA
- **Arrange**: Configurar dados e estado
- **Act**: Executar ação
- **Assert**: Verificar resultado

### Mocking
- Use `Mail::fake()` para emails
- Use `Http::fake()` para APIs externas
- Use `Queue::fake()` para jobs (se houver)
- Use `Storage::fake()` para arquivos

### Dados de Teste
- Use factories sempre que possível
- Crie estados específicos nas factories (`pending()`, `paid()`, etc.)
- Evite hardcoding de IDs

### Assertions
- Seja específico: prefira `assertDatabaseHas` a `assertDatabaseCount`
- Teste múltiplos aspectos: dados, redirecionamento, mensagens
- Verifique side effects (emails, eventos, etc.)

---

## Comandos Para Executar Testes

```bash
# Executar todos os testes
php artisan test

# Executar teste específico
php artisan test --filter=TransactionTest

# Executar testes de um diretório
php artisan test tests/Feature/Models

# Executar com coverage (requer Xdebug)
php artisan test --coverage

# Executar com output verboso
php artisan test --verbose

# Executar testes em paralelo
php artisan test --parallel
```

---

## Acceptance Criteria

- [ ] Todas as classes de teste criadas
- [ ] Factories criadas para Transaction e Tag
- [ ] Models testados: relationships, scopes, casts, traits
- [ ] Services testados: toda lógica de negócio
- [ ] Controllers testados: autenticação, validação, CRUD
- [ ] Livewire components testados: rendering, interação, eventos
- [ ] Commands testados: execução, notificações, logs
- [ ] Testes passam com sucesso (`php artisan test`)
- [ ] Coverage de pelo menos 80% nas áreas críticas
- [ ] Mocking usado corretamente (Mail, Http)
- [ ] Factories com estados úteis (pending, paid, overdue, etc.)

---

## Dependências
- Tasks 01-08 completas (toda funcionalidade implementada)

---

## Observações Finais

### Prioridade dos Testes
1. **Alta**: Services e Models (lógica de negócio core)
2. **Média**: Controllers e Livewire (interface com usuário)
3. **Baixa**: Commands (menos crítico, executado periodicamente)

### Teste Primeiro os Happy Paths
- Cenários de sucesso primeiro
- Depois cenários de erro
- Por último, edge cases

### Refatoração com Confiança
- Com testes abrangentes, você pode refatorar código com segurança
- Testes devem rodar rápido (< 30 segundos para suite completa)
- Testes lentos devem ser otimizados (eager loading, reduzir queries)

### Documentação via Testes
- Testes servem como documentação viva do sistema
- Nomes descritivos explicam o comportamento esperado
- Facilita onboarding de novos desenvolvedores

### Continuous Integration
- Configurar CI para rodar testes automaticamente
- Bloquear merge se testes não passarem
- Monitorar coverage ao longo do tempo
