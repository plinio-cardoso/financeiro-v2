# Tasks - Sistema de Controle Financeiro Doméstico

## Visão Geral

Este diretório contém todas as tasks (tarefas) necessárias para implementar o sistema completo de controle financeiro doméstico com Laravel + Jetstream + Livewire.

As tasks estão organizadas em sequência lógica e devem ser executadas em ordem, pois cada uma depende das anteriores.

## Estrutura das Tasks

Cada task é um arquivo Markdown independente que contém:
- **Objetivo**: O que será implementado
- **Contexto**: Por que essa task é importante
- **Escopo**: Lista detalhada do que será feito
- **Detalhamento**: Especificações técnicas e exemplos de código
- **Comandos Artisan**: Comandos necessários para gerar arquivos
- **Convenções**: Padrões e boas práticas a seguir
- **Acceptance Criteria**: Checklist para validar conclusão
- **Dependências**: Quais tasks devem estar completas antes
- **Próxima Task**: Qual task executar depois

## Ordem de Execução

### Task 01: Database - Migrations e Enums
**Arquivo**: `task-01-database-migrations-enums.md`

**O que faz**:
- Cria enums: `TransactionStatusEnum`, `TransactionTypeEnum`
- Cria migrations: `transactions`, `tags`, `transaction_tag`, `notification_settings`
- Define estrutura do banco de dados

**Tempo estimado**: 30-45 minutos

---

### Task 02: Models e Relacionamentos
**Arquivo**: `task-02-models-relacionamentos.md`

**O que faz**:
- Cria models: `Transaction`, `Tag`, `NotificationSetting`
- Implementa traits: `TransactionActionTrait`, `TransactionAccessorTrait`, `TagAccessorTrait`
- Define relationships, scopes, casts
- Atualiza model `User` com relationship `transactions()`

**Tempo estimado**: 1-1.5 horas

---

### Task 03: Services - Lógica de Negócio
**Arquivo**: `task-03-services-logica-negocio.md`

**O que faz**:
- Cria services: `TransactionService`, `DashboardService`, `NotificationService`, `MailgunService`
- Implementa toda lógica de negócio
- Cálculos, filtros, notificações

**Tempo estimado**: 1.5-2 horas

---

### Task 04: Form Requests - Validação
**Arquivo**: `task-04-form-requests-validacao.md`

**O que faz**:
- Cria Form Requests: `StoreTransactionRequest`, `UpdateTransactionRequest`, `UpdateNotificationSettingRequest`
- Implementa validação com mensagens em português
- Define regras de autorização

**Tempo estimado**: 30-45 minutos

---

### Task 05: Controllers - HTTP
**Arquivo**: `task-05-controllers-http.md`

**O que faz**:
- Cria controllers: `DashboardController`, `TransactionController`, `NotificationSettingController`
- Implementa rotas em `routes/web.php`
- Controllers finos que delegam para services

**Tempo estimado**: 45 minutos - 1 hora

---

### Task 06: Livewire Components
**Arquivo**: `task-06-livewire-components.md`

**O que faz**:
- Cria componentes: `DashboardStats`, `TransactionList`, `TransactionForm`, `TransactionActions`
- Implementa interatividade (filtros, ordenação, AJAX)
- Integra com Jetstream (modals, banners)

**Tempo estimado**: 2-2.5 horas

---

### Task 07: Views e Frontend - Tailwind
**Arquivo**: `task-07-views-frontend-tailwind.md`

**O que faz**:
- Customiza menu lateral do Jetstream
- Cria views: `dashboard.index`, `transactions.*`, `settings.notifications`
- Cria views Livewire com Tailwind CSS
- Implementa dark mode e responsividade

**Tempo estimado**: 2-3 horas

---

### Task 08: Commands e Integração Mailgun
**Arquivo**: `task-08-commands-integracao-mailgun.md`

**O que faz**:
- Configura Mailgun em `config/services.php`
- Cria views de email: `emails.transactions.due-today`, `emails.transactions.overdue`
- Cria commands: `NotifyDueTodayCommand`, `NotifyOverdueCommand`
- Configura schedule em `routes/console.php`

**Tempo estimado**: 1-1.5 horas

---

### Task 09: Testes - Unitários e Funcionais
**Arquivo**: `task-09-testes-unitarios-funcionais.md`

**O que faz**:
- Cria testes para: Models, Services, Controllers, Livewire, Commands
- Implementa factories: `TransactionFactory`, `TagFactory`
- Testa todos os cenários: happy paths, erros, edge cases

**Tempo estimado**: 3-4 horas

**⚠️ IMPORTANTE**: Esta é a última task e deve ser executada após todas as outras.

---

## Total Estimado

**Tempo total**: 12-15 horas de desenvolvimento

## Observações Importantes

### Não Execute Migrations Ainda
- As tasks criam as migrations, mas **NÃO as executam**
- Execute `php artisan migrate` manualmente após revisar todas as migrations
- Isso permite revisão antes de aplicar ao banco de dados

### Ordem é Importante
- Tasks têm dependências entre si
- Não pule tasks ou execute fora de ordem
- Cada task valida a anterior através dos "Acceptance Criteria"

### Configurações Manuais
Algumas configurações devem ser feitas manualmente:
- Variáveis de ambiente do Mailgun (`.env`)
- Configuração do cron no servidor (Task 08)
- Criação de tags iniciais (via seeders ou interface)

### Padrões do Projeto
Todas as tasks seguem os padrões definidos em:
- `.claude/CLAUDE.md` - Laravel Boost Guidelines
- `.claude/rules/backend-architecture.md` - Arquitetura
- `.claude/rules/code-quality.md` - Qualidade de código

### Model Actions/Accessors Pattern
O projeto usa um padrão específico para organizar models:
- **Actions** (`app/Models/Actions/*Trait.php`): Métodos que modificam estado, retornam `void`
- **Accessors** (`app/Models/Accessors/*Trait.php`): Métodos que retornam dados derivados/formatados
- **Model**: Apenas fillable, casts, relationships e scopes

Esse padrão é **OBRIGATÓRIO** e está documentado em `backend-architecture.md`.

## Como Usar Este Guia

### Para Desenvolvedores Humanos
1. Leia o planejamento em `.claude/planejamento_controle_financeiro_domestico_laravel.md`
2. Execute as tasks em ordem
3. Use os "Acceptance Criteria" como checklist
4. Revise o código gerado antes de prosseguir

### Para IAs (Claude Code)
1. Leia a task específica solicitada
2. Siga exatamente as especificações
3. Use os exemplos de código fornecidos
4. Valide contra os "Acceptance Criteria"
5. Não pule etapas ou tome atalhos
6. Siga os padrões definidos em `.claude/rules/`

## Perguntas Frequentes

### Posso executar tasks em paralelo?
Não. Tasks devem ser executadas sequencialmente devido às dependências.

### Posso modificar a estrutura proposta?
Sim, mas mantenha consistência com os padrões do projeto definidos em `.claude/rules/`.

### E se eu encontrar um erro em uma task anterior?
Corrija a task anterior antes de prosseguir. A ordem garante que problemas sejam detectados cedo.

### Preciso criar todos os testes (Task 09)?
Idealmente sim. Mas você pode priorizar testes de Services e Models primeiro (lógica de negócio crítica).

### Como sei se uma task está completa?
Use a seção "Acceptance Criteria" de cada task como checklist de validação.

## Próximos Passos Após Conclusão

Após completar todas as 9 tasks:

1. **Execute as migrations**
   ```bash
   php artisan migrate
   ```

2. **Crie tags iniciais** (opcional)
   ```bash
   php artisan tinker
   >>> Tag::create(['name' => 'Moradia', 'color' => '#3B82F6']);
   >>> Tag::create(['name' => 'Alimentação', 'color' => '#10B981']);
   >>> Tag::create(['name' => 'Transporte', 'color' => '#F59E0B']);
   ```

3. **Configure Mailgun**
   - Adicione credenciais no `.env`
   - Teste envio de email manualmente

4. **Execute os testes**
   ```bash
   php artisan test
   ```

5. **Configure o cron** (produção)
   ```cron
   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
   ```

6. **Build do frontend**
   ```bash
   npm install
   npm run build
   ```

7. **Acesse o sistema**
   - Registre um usuário via Jetstream
   - Crie suas primeiras transações
   - Configure notificações

## Suporte

Para dúvidas sobre:
- **Especificação**: Consulte `.claude/planejamento_controle_financeiro_domestico_laravel.md`
- **Arquitetura**: Consulte `.claude/rules/backend-architecture.md`
- **Qualidade**: Consulte `.claude/rules/code-quality.md`
- **Laravel Boost**: Consulte `.claude/CLAUDE.md`

---

**Boa implementação! 🚀**
