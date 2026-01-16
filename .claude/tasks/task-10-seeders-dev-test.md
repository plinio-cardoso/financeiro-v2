# Task 10 - Seeders para Ambiente de Desenvolvimento e Teste

## Objetivo
Criar seeders completos para popular o banco de dados com dados realistas para desenvolvimento e testes.

## Contexto
Precisamos de dados de exemplo para testar todas as funcionalidades do sistema:
- Usuários com diferentes perfis
- Tags variadas para categorização
- Transações em diferentes estados (pendentes, pagas, vencidas, a vencer hoje)
- Configurações de notificação

## Requisitos

### 1. UserSeeder
- Criar 3 usuários de exemplo:
  - Admin (admin@example.com / password)
  - User comum (user@example.com / password)
  - Test user (test@example.com / password)
- Cada usuário deve ter perfil completo (name, email, password)

### 2. TagSeeder
- Criar tags comuns para transações:
  - Alimentação (#FF6B6B)
  - Transporte (#4ECDC4)
  - Moradia (#45B7D1)
  - Saúde (#96CEB4)
  - Educação (#FFEAA7)
  - Lazer (#DFE6E9)
  - Salário (#55E6C1)
  - Freelance (#A29BFE)
  - Investimento (#FD79A8)
  - Outros (#B2BEC3)

### 3. TransactionSeeder
Criar transações variadas para cada usuário:

**Débitos (Despesas):**
- 5 transações pagas (mês anterior)
- 3 transações pendentes (vencimento futuro)
- 2 transações vencendo hoje
- 2 transações vencidas (1-5 dias atrás)
- Valores entre R$ 50,00 e R$ 2.000,00
- Associar tags relevantes

**Créditos (Receitas):**
- 2 salários pagos (início do mês)
- 1 freelance pendente
- Valores entre R$ 3.000,00 e R$ 8.000,00

### 4. NotificationSettingSeeder
- Criar configurações padrão para o usuário admin:
  - emails: ['admin@example.com']
  - notify_due_today: true
  - notify_overdue: true

## Estrutura de Arquivos

```
database/seeders/
├── DatabaseSeeder.php (chamar todos os seeders)
├── UserSeeder.php
├── TagSeeder.php
├── TransactionSeeder.php
└── NotificationSettingSeeder.php
```

## Implementação

### 1. Criar Seeders

```bash
php artisan make:seeder UserSeeder
php artisan make:seeder TagSeeder
php artisan make:seeder TransactionSeeder
php artisan make:seeder NotificationSettingSeeder
```

### 2. Implementar Cada Seeder

**UserSeeder:**
- Usar `User::factory()` com dados fixos para emails conhecidos
- Hash de senha: `bcrypt('password')`

**TagSeeder:**
- Inserir diretamente ou usar factory
- Cores em hexadecimal
- Nomes em português

**TransactionSeeder:**
- Usar `Transaction::factory()` com states customizados
- Associar tags aleatórias (1-3 tags por transação)
- Distribuir datas de forma realista:
  - Pagas: 15-45 dias atrás
  - Pendentes futuro: 5-30 dias à frente
  - Vencendo hoje: due_date = hoje
  - Vencidas: 1-5 dias atrás

**NotificationSettingSeeder:**
- Criar apenas para user_id = 1 (admin)
- Usar método `getSettings()` ou criar diretamente

### 3. Atualizar DatabaseSeeder

Chamar seeders na ordem correta:
1. UserSeeder
2. TagSeeder
3. NotificationSettingSeeder
4. TransactionSeeder (por último, pois depende dos outros)

### 4. Comando para Executar

```bash
# Resetar e popular
php artisan migrate:fresh --seed

# Apenas popular (sem apagar)
php artisan db:seed
```

## Dados de Exemplo Sugeridos

### Tags (nome e cor)
- Alimentação: #FF6B6B (vermelho)
- Transporte: #4ECDC4 (azul claro)
- Moradia: #45B7D1 (azul)
- Saúde: #96CEB4 (verde claro)
- Educação: #FFEAA7 (amarelo)
- Lazer: #DFE6E9 (cinza claro)
- Salário: #55E6C1 (verde água)
- Freelance: #A29BFE (roxo)
- Investimento: #FD79A8 (rosa)
- Outros: #B2BEC3 (cinza)

### Exemplos de Transações Débito
- Supermercado - R$ 350,00 - Alimentação
- Uber - R$ 45,00 - Transporte
- Aluguel - R$ 1.500,00 - Moradia
- Plano de Saúde - R$ 450,00 - Saúde
- Curso Online - R$ 197,00 - Educação
- Cinema - R$ 80,00 - Lazer
- Farmácia - R$ 120,00 - Saúde
- Netflix - R$ 39,90 - Lazer
- Gasolina - R$ 250,00 - Transporte

### Exemplos de Transações Crédito
- Salário Empresa X - R$ 5.500,00 - Salário
- Projeto Freelance - R$ 2.000,00 - Freelance
- Rendimento Investimento - R$ 350,00 - Investimento

## Validação

Após executar os seeders, verificar:
- [ ] Usuários criados e podem fazer login
- [ ] Tags aparecem nos filtros e formulários
- [ ] Dashboard mostra estatísticas corretas
- [ ] Lista de transações mostra todos os estados
- [ ] Configurações de notificação estão ativas
- [ ] Comandos `notify:due-today` e `notify:overdue` encontram transações

## Observações

- Usar `DB::transaction()` nos seeders para garantir atomicidade
- Adicionar comentários explicativos no código
- Usar `faker` para descrições variadas quando apropriado
- Considerar adicionar método `truncate()` antes de inserir dados
- Garantir que IDs sejam previsíveis para facilitar testes
