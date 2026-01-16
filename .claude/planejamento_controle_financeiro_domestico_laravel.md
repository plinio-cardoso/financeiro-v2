# Planejamento do Sistema – Controle Financeiro Doméstico

## Stack
- Laravel + Jetstream (auth, users)
- Livewire (interatividade / AJAX)
- Tailwind CSS
- Mailgun (envio de e-mails)

---

## Escopo Inicial
- Controle de **transações financeiras** (débitos e créditos desde o início)
- Créditos **existem no cadastro**, mas **não entram em totais, dashboards ou notificações** por enquanto
- Dashboard com resumos mensais **considerando apenas débitos**
- Filtros, ordenação e paginação
- Notificações automáticas por e-mail (cron)
- Estrutura seguindo **SOLID** (Controllers finos, Services para regra de negócio)

---

## Entidades / Models

### User (Jetstream)
- id
- name
- email
- password

---

### Transaction
Representa contas a pagar (e futuramente a receber)

**Campos**
- id
- user_id (fk)
- title (string)
- description (text, nullable)
- amount (decimal: 12,2)
- type (enum: debit | credit) *(futuro)*
- status (enum: pending | paid)
- due_date (date)
- paid_at (datetime, nullable)
- created_at
- updated_at

**Casts**
- amount => decimal:2
- due_date => date
- paid_at => datetime
- status => TransactionStatusEnum
- type => TransactionTypeEnum

---

### Tag
**Campos**
- id
- name (string)
- color (string, opcional)
- created_at

---

### transaction_tag (pivot)
- transaction_id
- tag_id

---

### NotificationSetting
Configuração de alertas

**Campos**
- id
- emails (json) → lista de destinatários
- notify_due_today (bool)
- notify_overdue (bool)

**Casts**
- emails => array

---

## Enums

### TransactionStatusEnum
- pending
- paid

### TransactionTypeEnum
- debit
- credit

> Observação: `credit` existe no sistema, mas **não participa de cálculos, dashboards ou notificações** no escopo atual.

---

## Services (Regra de Negócio)

### TransactionService
- createTransaction(data)
- updateTransaction(transaction, data)
- markAsPaid(transaction)
- calculateMonthlyTotals(month) **(somente débitos)**
- getFilteredTransactions(filters)

---

### NotificationService
- sendDueTodayNotifications(date)
- sendOverdueNotifications(date)

---

### MailgunService
- send(to[], subject, view, data)

Abstrai totalmente a API do Mailgun

---

## Controllers (HTTP)
Controllers apenas orquestram chamadas

### DashboardController
- index()

### TransactionController
- index()
- store()
- update()
- destroy()

---

## Livewire Components

### DashboardStats
- Totais do mês atual
- Totais do próximo mês
- Total pago no mês
- Total pendente

---

### TransactionList
- Paginação
- Filtros:
  - intervalo de datas
  - texto (título)
  - tags (multi-select)
  - status
- Ordenação:
  - valor
  - data

---

### TransactionForm
- Cadastro / edição
- Campos básicos
- Seleção de tags

---

### TransactionActions
- Marcar como pago (AJAX)
- Excluir

---

## Rotas

```php
/dashboard
/transactions
/transactions/create
```

---

## Dashboard – Regras

### Cards Superiores
- Total a pagar no mês atual
- Total já pago no mês
- Total previsto para o próximo mês

### Listagem
- Transações do mês atual (default)

---

## Cron / Commands

### transactions:notify-due-today
- Executa diariamente
- Busca transações pendentes com due_date = hoje
- Envia e-mail

### transactions:notify-overdue
- Executa diariamente
- Busca transações pendentes com due_date < hoje
- Envia e-mail de alerta

---

## Integração Mailgun

### Env
- MAILGUN_DOMAIN
- MAILGUN_SECRET
- MAILGUN_ENDPOINT

### Config
- config/services.php

---

## Configuração de Destinatários
- Tela simples de configurações
- Cadastro de 1 ou mais e-mails
- Armazenado em NotificationSetting

---

## Menu Lateral (Jetstream custom)

### Links
- Dashboard
- Transações
- Nova Transação
- Configurações

Responsivo (mobile-first)

---

## Padrões
- Controllers finos
- Services para lógica
- Livewire para UI dinâmica
- Enums para status/tipo
- Pronto para extensão futura (créditos, relatórios)

---

## Overview Geral (para IA implementar)

Este sistema é um **controle financeiro doméstico**, focado em **contas a pagar**.

### Conceitos-chave
- Usuários vêm prontos do Jetstream
- Usuário cadastra **transações financeiras**
- Cada transação pode ser **débito ou crédito**
- **Apenas débitos** entram em:
  - dashboards
  - totais
  - previsões
  - notificações

Créditos existem apenas para **cadastro e persistência**, sem impacto funcional.

### Transações
- Possuem valor, data de vencimento, status e tags
- Status controla se está pendente ou paga
- Ação de marcar como paga é feita via **Livewire (AJAX)**

### Dashboard
- Visão do mês atual (default)
- Cards superiores:
  - total a pagar no mês
  - total já pago no mês
  - total previsto do próximo mês
- Listagem filtrável e ordenável de transações

### Filtros
- Intervalo de datas
- Texto (título)
- Tags (multi-seleção)
- Status
- Ordenação por data ou valor

### Notificações
- Executadas por **Laravel Commands + Cron**
- Dois cenários:
  1. Contas que vencem hoje
  2. Contas vencidas e ainda pendentes
- Envio de e-mail via **Mailgun**
- Destinatários configuráveis (1 ou mais e-mails)

### Arquitetura
- Controllers: apenas entrada/saída HTTP
- Services: regra de negócio
- Livewire: estado, filtros, ações
- Enums: status e tipo
- Código preparado para expansão futura

### UI
- Jetstream com **menu lateral customizado**
- Layout responsivo (desktop/mobile)

### Objetivo do documento
Este arquivo serve como **fonte única de verdade** para que outra IA (Claude Code) implemente o sistema **sem decisões abertas**, apenas seguindo a especificação.

---

## Próximos Passos (fora do escopo inicial)
- Relatórios anuais
- Créditos entrando nos cálculos
- Exportação CSV/PDF
- Integração bancária

