# Sistema de Transações Recorrentes — Definição Técnica

## Visão Geral

O sistema separa **regra de recorrência** de **transações reais**.

- **RecurringTransaction**: define a regra (contrato)
- **Transaction**: representa um fato financeiro (histórico imutável)

Nunca calcular recorrência a partir de `transactions`.

---

## Conceitos

- Transação = boleto / evento financeiro
- Recorrência = contrato / regra geradora
- Histórico não muda
- Regra pode mudar

Duplicação de dados é **intencional e correta**.

---

## Tabelas

### recurring_transactions

Responsável apenas por definir a recorrência.

Campos sugeridos:
- id
- user_id
- title
- description
- amount
- type (debit | credit)
- frequency (weekly | monthly | custom)
- interval (int)
- start_date
- end_date (nullable)
- occurrences (nullable)
- generated_count
- next_due_date
- active (bool)
- created_at
- updated_at

---

### transactions

Representa transações reais, pagáveis e editáveis.

Campos adicionais:
- recurring_transaction_id (nullable, FK)
- sequence (opcional)

Campos duplicados propositalmente:
- title
- amount
- type

---

## Relacionamentos

- RecurringTransaction hasMany Transactions
- Transaction belongsTo RecurringTransaction (nullable)

---

## Geração de Transações

### Comando Laravel

- Comando customizado iniciando com `app:`
- Exemplo:
  - `php artisan app:generate-transactions`

### Execução
- Rodar diariamente via cron
- Idempotente
- Gera transações futuras até um limite (ex: +30 dias)

---

## Frontend — Criação de Transação

### Modal único: “Nova transação”

Campos sempre visíveis:
- Título
- Valor
- Tipo
- Data de vencimento
- Tags

### Toggle
```
[ ] Transação recorrente
```

---

### Não recorrente
- Cria apenas 1 `transaction`
- Sem recorrência associada

---

### Recorrente
Campos adicionais:
- Frequência
- Data de início
- Finalização (para sempre / até data / X vezes)

---

## Grid / Listagem

### Não recorrentes
- Editáveis direto na grid
- Sem lápis

### Recorrentes
- Editáveis na grid (afeta só a transação)
- Badge 🔁
- Botão lápis

---

## Edição

- Grid: altera só a transação
- Lápis: ações avançadas
  - Editar apenas esta
  - Editar recorrência (somente futuras)

---

## Regras

- Nunca alterar recorrência silenciosamente
- Histórico imutável
- Transações pagas não são alteradas
