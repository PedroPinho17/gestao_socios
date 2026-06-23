# Gestão de Sócios — Laravel + Filament

Backoffice com login para gerir sócios, quotas, pagamentos e cartões de sócio.

## Requisitos

- PHP 8.3+ (extensões: `intl`, `zip`, `pdo`, `mbstring`, `openssl`, `fileinfo`)
- Composer
- MySQL/MariaDB (produção / cPanel) ou SQLite (desenvolvimento local)

## Instalação local

```bash
composer install
cp .env.example .env   # se ainda não existir
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Abra **http://localhost:8000/admin**

### Performance local (Windows)

O `php artisan serve` usa PHP em modo CLI. Se estiver lento (5–10s por página), active o OPcache no `php.ini`:

```ini
opcache.enable=1
opcache.enable_cli=1
```

Reinicie o terminal e volte a correr `php artisan serve`. A 1.ª página pode demorar ~2s; as seguintes devem ficar abaixo de 1s.

Em produção (cPanel) isto não é problema — o PHP-FPM já usa OPcache.

### Utilizadores iniciais (desenvolvimento)

| Perfil | Email | Password |
|--------|-------|----------|
| Imperador (programador) | `imperador@dev.local` | `password` |
| Administrador (clube) | `admin@clube.pt` | `password` |

Altere as passwords após o primeiro login.

Os perfis ficam na tabela `permissoes` (IDs fixos: 1 Imperador, 2 Administrador, 3 Tesoureiro).

## O que fica no `.env` vs base de dados

| `.env` (só infraestrutura) | Base de dados / painel |
|-----------------------------|-------------------------|
| `APP_KEY`, `APP_ENV`, `APP_DEBUG`, `APP_URL` | Perfis (`permissoes`) e utilizadores |
| Ligação MySQL (`DB_*`) | Clube, cartão, logótipo (`club_settings` → **Definições**) |
| | 2FA obrigatório, dias de alerta de quota (`app_settings` → **Sistema**, só imperador) |
| | Planos de quota, sócios, pagamentos |

No `.env` só precisa de **APP_KEY** + **DB_*** + **APP_URL** (em produção). O resto configura-se no painel após login.

## Funcionalidades

- **Painel** — resumo de sócios e alertas de quotas em atraso / a vencer
- **Sócios** — CRUD, pesquisa por nome/n.º/email, filtros por estado de quota, pagamentos na ficha
- **Planos de quota** — periodicidade, valor, regra de vencimento
- **Definições** — nome do clube, logótipo, cores e campos do cartão (título do painel usa o nome do clube)
- **Sistema** — 2FA obrigatório, dias de aviso de quota (só imperador)
- **Utilizadores** — criar/editar contas (imperador: todos os perfis; administrador: admin e tesoureiro)
- **Cartão** — impressão no browser ou PDF no servidor (`/cartao/{id}`, requer login)
- **Relatórios** — sócios em atraso em PDF ou Excel/CSV (botões na lista de sócios)

## Deploy no cPanel

1. Crie uma base de dados **MySQL** e anote host, nome, utilizador e password.
2. Envie os ficheiros do projeto para o servidor.
3. Aponte o **document root** para `public/` (subdomínio recomendado, ex. `app.seuclube.pt`).
4. Configure `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.seuclube.pt

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nome_da_bd
DB_USERNAME=utilizador
DB_PASSWORD=password
```

5. No terminal SSH do cPanel (ou tarefa única):

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan gestao:create-imperador seu@email.pt --name="Seu Nome"
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

6. Permissões de escrita em `storage/` e `bootstrap/cache/`.
7. Active HTTPS (Let's Encrypt no cPanel).

### phpMyAdmin

A base de dados MySQL pode ser gerida no **phpMyAdmin** do cPanel (backups, consultas). Em produção, use as **migrations** do Laravel para alterar a estrutura.

## Segurança

| Funcionalidade | Descrição |
|----------------|-----------|
| **Password obrigatória no 1.º login** | Contas novas podem exigir alteração de password; mínimo 12 caracteres |
| **Perfis na BD** | Tabela `permissoes` com 3 níveis; utilizadores ligados por `permissao_id` |
| **Perfis** | Ver tabela abaixo |
| **2FA** | Perfil → autenticação por app. Obrigatoriedade em **Configuração → Sistema** (só imperador) |
| **Ficheiros privados** | Fotos e logótipos servidos só com login (`/files/...`) |
| **Auditoria** | Menu «Auditoria» (só imperador) regista alterações a sócios, pagamentos, planos e definições |

### Perfis de utilizador

| Perfil | Acesso |
|--------|--------|
| **Imperador** | Tudo: sócios, planos, definições, sistema, utilizadores (todos os perfis), auditoria |
| **Administrador** | Gestão do clube + criar administradores e tesoureiros |
| **Tesoureiro** | Sócios, pagamentos, cartões e relatórios (não cria utilizadores) |

### Produção (cPanel)

Com `APP_URL=https://...` as cookies de sessão ficam automaticamente seguras (HTTPS).

Após `migrate` e `db:seed`, crie a conta imperador (só uma vez):

```bash
php artisan gestao:create-imperador seu@email.pt --name="Seu Nome"
```

Depois, no painel (**Configuração → Utilizadores**), crie administradores e tesoureiros para o clube.

### Criar utilizadores no painel

Menu **Configuração → Utilizadores** (`/admin/users`):

| Quem | Pode criar |
|------|------------|
| **Imperador** | Imperador, Administrador, Tesoureiro |
| **Administrador** | Administrador, Tesoureiro |
| **Tesoureiro** | — (sem acesso a esta página) |

O novo utilizador terá de alterar a password no primeiro login.

## Estrutura

- `app/Models` — Sócios, planos, pagamentos, definições
- `app/Services/QuotaService.php` — cálculo de vencimentos
- `app/Filament` — painel administrativo
- `resources/views/member-card.blade.php` — cartão para impressão
