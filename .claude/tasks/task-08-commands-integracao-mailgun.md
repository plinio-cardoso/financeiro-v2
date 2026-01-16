# Task 08: Commands e Integração Mailgun

## Objetivo
Criar Laravel Commands para notificações automáticas via cron e configurar integração com Mailgun para envio de e-mails.

## Contexto
Commands serão executados diariamente via cron para notificar sobre contas que vencem hoje e contas vencidas. O MailgunService abstrairá completamente o envio de emails.

## Escopo

### Configuração
- [ ] Configurar Mailgun em `config/services.php`
- [ ] Adicionar variáveis de ambiente no `.env.example`

### Views de E-mail
- [ ] `emails.transactions.due-today` - Template para contas que vencem hoje
- [ ] `emails.transactions.overdue` - Template para contas vencidas

### Commands
- [ ] `transactions:notify-due-today` - Notifica contas que vencem hoje
- [ ] `transactions:notify-overdue` - Notifica contas vencidas

### Agendamento
- [ ] Configurar schedule em `routes/console.php`

## Detalhamento

### 1. Configuração do Mailgun

**Arquivo**: `config/services.php`

Adicionar configuração do Mailgun:

```php
'mailgun' => [
    'domain' => env('MAILGUN_DOMAIN'),
    'secret' => env('MAILGUN_SECRET'),
    'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    'scheme' => 'https',
],
```

**Arquivo**: `.env.example`

Adicionar variáveis:

```env
MAILGUN_DOMAIN=
MAILGUN_SECRET=
MAILGUN_ENDPOINT=api.mailgun.net
```

### 2. Views de E-mail

#### Due Today Email View

**Arquivo**: `resources/views/emails/transactions/due-today.blade.php`

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contas que Vencem Hoje</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 20px;
            border: 1px solid #e5e7eb;
        }
        .transaction {
            background-color: white;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #4F46E5;
            border-radius: 4px;
        }
        .transaction-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .transaction-amount {
            color: #4F46E5;
            font-size: 18px;
            font-weight: bold;
        }
        .transaction-date {
            color: #6b7280;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 12px;
        }
        .total {
            background-color: #4F46E5;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⏰ Contas que Vencem Hoje</h1>
            <p>{{ $date->format('d/m/Y') }}</p>
        </div>

        <div class="content">
            <p>Olá! Você tem <strong>{{ count($transactions) }}</strong> conta(s) que vence(m) hoje:</p>

            @foreach($transactions as $transaction)
                <div class="transaction">
                    <div class="transaction-title">{{ $transaction->title }}</div>
                    <div class="transaction-amount">R$ {{ number_format($transaction->amount, 2, ',', '.') }}</div>
                    @if($transaction->description)
                        <p style="margin: 10px 0; color: #6b7280;">{{ $transaction->description }}</p>
                    @endif
                    <div class="transaction-date">Vencimento: {{ $transaction->due_date->format('d/m/Y') }}</div>

                    @if($transaction->tags->count() > 0)
                        <div style="margin-top: 10px;">
                            @foreach($transaction->tags as $tag)
                                <span style="background-color: {{ $tag->color ?? '#6B7280' }}20; color: {{ $tag->color ?? '#6B7280' }}; padding: 3px 8px; border-radius: 3px; font-size: 12px; margin-right: 5px;">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="total">
                Total a Pagar Hoje: R$ {{ number_format($total, 2, ',', '.') }}
            </div>
        </div>

        <div class="footer">
            <p>Este é um e-mail automático do sistema de Controle Financeiro.</p>
            <p>Você está recebendo esta notificação porque está cadastrado nas configurações de notificação.</p>
        </div>
    </div>
</body>
</html>
```

#### Overdue Email View

**Arquivo**: `resources/views/emails/transactions/overdue.blade.php`

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contas Vencidas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #DC2626;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #fef2f2;
            padding: 20px;
            border: 1px solid #fecaca;
        }
        .transaction {
            background-color: white;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #DC2626;
            border-radius: 4px;
        }
        .transaction-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .transaction-amount {
            color: #DC2626;
            font-size: 18px;
            font-weight: bold;
        }
        .transaction-date {
            color: #991b1b;
            font-size: 14px;
        }
        .days-overdue {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            display: inline-block;
            margin-top: 5px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 12px;
        }
        .total {
            background-color: #DC2626;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            border-radius: 4px;
            margin-top: 20px;
        }
        .warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Contas Vencidas</h1>
            <p>{{ $date->format('d/m/Y') }}</p>
        </div>

        <div class="content">
            <div class="warning">
                <strong>Atenção!</strong> Você tem <strong>{{ count($transactions) }}</strong> conta(s) vencida(s) e pendente(s):
            </div>

            @foreach($transactions as $transaction)
                <div class="transaction">
                    <div class="transaction-title">{{ $transaction->title }}</div>
                    <div class="transaction-amount">R$ {{ number_format($transaction->amount, 2, ',', '.') }}</div>
                    @if($transaction->description)
                        <p style="margin: 10px 0; color: #6b7280;">{{ $transaction->description }}</p>
                    @endif
                    <div class="transaction-date">Vencimento: {{ $transaction->due_date->format('d/m/Y') }}</div>
                    <div class="days-overdue">
                        Vencida há {{ abs($transaction->getDaysUntilDue()) }} dia(s)
                    </div>

                    @if($transaction->tags->count() > 0)
                        <div style="margin-top: 10px;">
                            @foreach($transaction->tags as $tag)
                                <span style="background-color: {{ $tag->color ?? '#6B7280' }}20; color: {{ $tag->color ?? '#6B7280' }}; padding: 3px 8px; border-radius: 3px; font-size: 12px; margin-right: 5px;">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="total">
                Total em Atraso: R$ {{ number_format($total, 2, ',', '.') }}
            </div>
        </div>

        <div class="footer">
            <p>Este é um e-mail automático do sistema de Controle Financeiro.</p>
            <p>Você está recebendo esta notificação porque está cadastrado nas configurações de notificação.</p>
            <p>Por favor, regularize suas contas o mais breve possível.</p>
        </div>
    </div>
</body>
</html>
```

### 3. Command: NotifyDueToday

**Localização**: `app/Console/Commands/NotifyDueTodayCommand.php`

```php
<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class NotifyDueTodayCommand extends Command
{
    protected $signature = 'transactions:notify-due-today';

    protected $description = 'Send notifications for transactions due today';

    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Checking for transactions due today...');

        $count = $this->notificationService->sendDueTodayNotifications(
            new \DateTime()
        );

        if ($count > 0) {
            $this->info("Sent {$count} notification(s) for transactions due today.");
        } else {
            $this->info('No transactions due today or notifications disabled.');
        }

        return Command::SUCCESS;
    }
}
```

### 4. Command: NotifyOverdue

**Localização**: `app/Console/Commands/NotifyOverdueCommand.php`

```php
<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class NotifyOverdueCommand extends Command
{
    protected $signature = 'transactions:notify-overdue';

    protected $description = 'Send notifications for overdue transactions';

    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Checking for overdue transactions...');

        $count = $this->notificationService->sendOverdueNotifications(
            new \DateTime()
        );

        if ($count > 0) {
            $this->info("Sent {$count} notification(s) for overdue transactions.");
        } else {
            $this->info('No overdue transactions or notifications disabled.');
        }

        return Command::SUCCESS;
    }
}
```

### 5. Schedule Configuration

**Arquivo**: `routes/console.php`

```php
<?php

use Illuminate\Support\Facades\Schedule;

// Notificação de contas que vencem hoje - executar às 8h diariamente
Schedule::command('transactions:notify-due-today')
    ->dailyAt('08:00')
    ->timezone('America/Sao_Paulo');

// Notificação de contas vencidas - executar às 9h diariamente
Schedule::command('transactions:notify-overdue')
    ->dailyAt('09:00')
    ->timezone('America/Sao_Paulo');
```

### 6. Atualizar NotificationService

O NotificationService (Task 03) deve usar o MailgunService para enviar emails.

**Exemplo de método `sendDueTodayNotifications`**:

```php
public function sendDueTodayNotifications(\DateTime $date): int
{
    if (!$this->shouldSendNotification('due_today')) {
        return 0;
    }

    $transactions = Transaction::dueToday()
        ->pending()
        ->debits()
        ->with(['tags', 'user'])
        ->get();

    if ($transactions->isEmpty()) {
        return 0;
    }

    $emails = $this->getNotificationEmails();

    if (empty($emails)) {
        Log::warning('No emails configured for notifications');
        return 0;
    }

    $total = $transactions->sum('amount');

    $this->mailgunService->send(
        to: $emails,
        subject: 'Contas que Vencem Hoje - ' . $date->format('d/m/Y'),
        view: 'emails.transactions.due-today',
        data: [
            'transactions' => $transactions,
            'total' => $total,
            'date' => $date,
        ]
    );

    Log::info('Due today notifications sent', [
        'count' => $transactions->count(),
        'total' => $total,
        'emails' => count($emails),
    ]);

    return $transactions->count();
}
```

**Exemplo de método `sendOverdueNotifications`**:

```php
public function sendOverdueNotifications(\DateTime $date): int
{
    if (!$this->shouldSendNotification('overdue')) {
        return 0;
    }

    $transactions = Transaction::overdue()
        ->pending()
        ->debits()
        ->with(['tags', 'user'])
        ->get();

    if ($transactions->isEmpty()) {
        return 0;
    }

    $emails = $this->getNotificationEmails();

    if (empty($emails)) {
        Log::warning('No emails configured for notifications');
        return 0;
    }

    $total = $transactions->sum('amount');

    $this->mailgunService->send(
        to: $emails,
        subject: '⚠️ Contas Vencidas - ' . $date->format('d/m/Y'),
        view: 'emails.transactions.overdue',
        data: [
            'transactions' => $transactions,
            'total' => $total,
            'date' => $date,
        ]
    );

    Log::info('Overdue notifications sent', [
        'count' => $transactions->count(),
        'total' => $total,
        'emails' => count($emails),
    ]);

    return $transactions->count();
}
```

## Comandos Artisan a Usar

```bash
# Criar Commands
php artisan make:command NotifyDueTodayCommand
php artisan make:command NotifyOverdueCommand
```

## Testando Commands Manualmente

```bash
# Executar comando de contas que vencem hoje
php artisan transactions:notify-due-today

# Executar comando de contas vencidas
php artisan transactions:notify-overdue

# Ver lista de schedules configurados
php artisan schedule:list

# Executar todos os schedules manualmente (para teste)
php artisan schedule:run
```

## Configuração do Cron (Produção)

Adicionar ao crontab do servidor:

```cron
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Isso executará o Laravel scheduler a cada minuto, e ele decidirá quais commands rodar baseado na configuração.

## Convenções

### Commands
- Signature: `namespace:action-name` (kebab-case)
- Description: Frase clara e concisa
- Injetar Services via constructor
- Usar `$this->info()` e `$this->error()` para output
- Retornar `Command::SUCCESS` ou `Command::FAILURE`

### Emails
- Views em `resources/views/emails/`
- Usar inline styles (email clients não suportam CSS externo)
- Incluir layout responsivo
- Testar em múltiplos clientes de email
- Sempre incluir plain text alternative

### Schedule
- Usar timezone correto (`America/Sao_Paulo`)
- Comandos críticos devem ter ->onOneServer() se múltiplos servidores
- Usar ->withoutOverlapping() para evitar execuções simultâneas
- Log de execuções com ->appendOutputTo()

## Acceptance Criteria

- [ ] Configuração do Mailgun adicionada em `config/services.php`
- [ ] Variáveis de ambiente adicionadas em `.env.example`
- [ ] 2 views de email criadas e estilizadas
- [ ] 2 Commands criados em `app/Console/Commands/`
- [ ] Schedule configurado em `routes/console.php`
- [ ] NotificationService atualizado para usar MailgunService
- [ ] Commands podem ser executados manualmente
- [ ] Emails são enviados corretamente via Mailgun
- [ ] Logs registram execução dos commands
- [ ] Apenas débitos pendentes são notificados

## Dependências
- Task 02 completa (Models com scopes)
- Task 03 completa (Services implementados)

## Próxima Task
Task 09: Testes (Unitários e Funcionais)

## Observações
- **NÃO executar** migrations nesta task
- Configurar variáveis de ambiente manualmente
- Testar envio de emails com conta Mailgun de sandbox primeiro
- Schedule requer cron configurado no servidor
- Em desenvolvimento, executar `php artisan schedule:work` para teste
- Logs são essenciais para debug de notificações
