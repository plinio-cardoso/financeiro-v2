# Sistema de Controle Financeiro Doméstico

Sistema completo de gestão financeira pessoal desenvolvido com Laravel 12, Livewire 3 e Tailwind CSS.

## Funcionalidades

- ✅ Gestão de transações (receitas e despesas)
- ✅ Categorização com tags
- ✅ Filtros avançados (busca, data, status, tipo)
- ✅ Notificações por e-mail (contas vencidas e a vencer)
- ✅ Dashboard com resumo financeiro
- ✅ Modo escuro/claro
- ✅ Sistema de autenticação completo
- ✅ Responsivo e otimizado

## Requisitos

- PHP 8.3+
- Composer
- Node.js & NPM
- SQLite (desenvolvimento) ou MySQL/PostgreSQL (produção)

## Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/plinio-cardoso/financeiro-v2.git
cd financeiro-v2
```

### 2. Instale as dependências

```bash
composer install
npm install
```

### 3. Configure o ambiente

```bash
# Copie o arquivo de exemplo
cp .env.example .env

# Gere a chave da aplicação
php artisan key:generate
```

### 4. Configure o banco de dados

O projeto usa SQLite por padrão. O banco será criado automaticamente ao rodar as migrations.

```bash
php artisan migrate --seed
```

### 5. Compile os assets

```bash
npm run build
# ou para desenvolvimento com hot reload:
npm run dev
```

### 6. Inicie o servidor

```bash
php artisan serve
```

Acesse: http://localhost:8000

## Testes

### Configuração do Ambiente de Testes

1. **Copie o arquivo de exemplo de testes:**
   ```bash
   cp .env.testing.example .env.testing
   ```

2. **Gere a chave da aplicação para testes:**
   ```bash
   php artisan key:generate --env=testing
   ```

3. **Configure suas credenciais reais (apenas local):**

   Edite o arquivo `.env.testing` e adicione suas chaves reais:
   - `MAILGUN_SECRET` - Sua chave do Mailgun (se for testar envio de emails)
   - `OPENAI_API_KEY` - Sua chave da OpenAI (se for testar funcionalidades com IA)

   ⚠️ **IMPORTANTE**: O arquivo `.env.testing` está no `.gitignore` e **NUNCA** deve ser commitado!

### Executando os Testes

```bash
# Todos os testes
php artisan test

# Testes específicos
php artisan test --filter=NotificationSettingTest

# Com cobertura
php artisan test --coverage
```

### Estrutura de Testes

- `tests/Feature/` - Testes de integração (API, controllers, Livewire)
- `tests/Unit/` - Testes unitários (services, models)

## Comandos Artisan

### Notificações

```bash
# Enviar notificações de contas que vencem hoje
php artisan notify:due-today

# Enviar notificações de contas vencidas
php artisan notify:overdue
```

### Desenvolvimento

```bash
# Limpar cache
php artisan optimize:clear

# Rodar code style (Pint)
vendor/bin/pint

# Rodar testes
php artisan test
```

## Estrutura do Projeto

```
app/
├── Console/Commands/     # Comandos Artisan
├── Http/
│   ├── Controllers/      # Controllers HTTP
│   ├── Requests/         # Form Requests (validação)
│   └── Middleware/       # Middlewares
├── Livewire/            # Componentes Livewire
├── Models/              # Eloquent Models
├── Services/            # Camada de serviços (business logic)
├── Enums/               # Enumerações
└── Events/              # Eventos Laravel

resources/
├── views/
│   ├── livewire/        # Views Livewire
│   ├── components/      # Componentes Blade
│   └── emails/          # Templates de email
└── css/                 # Estilos Tailwind

tests/
├── Feature/             # Testes de integração
└── Unit/               # Testes unitários
```

## Tecnologias Utilizadas

- **Backend**: Laravel 12, PHP 8.3
- **Frontend**: Livewire 3, Alpine.js, Tailwind CSS 3
- **Banco de Dados**: SQLite (dev), MySQL/PostgreSQL (prod)
- **Autenticação**: Laravel Fortify + Sanctum
- **Email**: Mailgun
- **Testes**: PHPUnit 11

## Configuração de Produção

### Variáveis de Ambiente Importantes

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com

# Banco de dados
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=financeiro
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

# Email (Mailgun)
MAILGUN_DOMAIN=seu-dominio.mailgun.org
MAILGUN_SECRET=key-sua-chave-mailgun
MAILGUN_ENDPOINT=api.mailgun.net

# Cache e Sessão
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Deploy

1. Configure as variáveis de ambiente
2. Execute as migrations: `php artisan migrate --force`
3. Compile os assets: `npm run build`
4. Otimize a aplicação: `php artisan optimize`
5. Configure o cron para os comandos de notificação

## Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiciona MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

## Segurança

- Nunca commite arquivos `.env*` com credenciais reais
- Use `.env.testing.example` como template (sem secrets)
- Mantenha `.env.testing` apenas local (está no .gitignore)
- Revogue e regenere qualquer credencial que seja exposta acidentalmente

## Licença

Este projeto é de código aberto sob a licença MIT.

## Autor

**Plínio Cardoso**
- GitHub: [@plinio-cardoso](https://github.com/plinio-cardoso)
