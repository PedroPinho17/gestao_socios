# Gestão de Sócios — Laravel + Filament + React

Sistema para clubes gerirem sócios, quotas, pagamentos e cartões de sócio.

- **Backoffice** (`/admin`) — painel Filament para administradores e tesoureiros
- **Área do sócio** (`frontend/`) — SPA React onde cada sócio consulta a quota e o histórico de pagamentos

## Requisitos

- PHP 8.3+ (extensões: `intl`, `zip`, `pdo`, `mbstring`, `openssl`, `fileinfo`)
- Composer
- Node.js 20+ e npm (área do sócio; opcionalmente exportação profissional de cartões)
- MySQL/MariaDB (produção / cPanel) ou SQLite (desenvolvimento local)

## Instalação local

### Backend (Laravel)

```bash
composer install
cp .env.example .env   # se ainda não existir
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Abra **http://localhost:8000/admin**

### Área do sócio (React)

```bash
cd frontend
cp .env.example .env   # se ainda não existir
npm install
npm run dev
```

Abra **http://localhost:5173**

No `.env` da raiz, confirme `CORS_ALLOWED_ORIGINS=http://localhost:5173`. No `frontend/.env`, use `VITE_API_URL=http://localhost:8000/api`.

### Arranque rápido (só backend + assets Filament)

Na raiz do projeto:

```bash
composer run dev
```

Isto corre o servidor Laravel, fila, logs e Vite dos assets do painel. A área do sócio continua a precisar de `npm run dev` em `frontend/` noutro terminal.

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
| `APP_KEY`, `APP_ENV`, `APP_DEBUG`, `APP_URL` | Perfis (`permissoes`) e utilizadores do backoffice |
| Ligação MySQL (`DB_*`) | Clube, cartão, logótipo (`club_settings` → **Definições**) |
| `CORS_ALLOWED_ORIGINS` (URL do frontend React) | Contas de acesso dos sócios (email/password na ficha do sócio) |
| `CLUB_LOGO` (fallback em `public/`) | 2FA obrigatório, dias de alerta de quota (`app_settings` → **Sistema**, só imperador) |
| `MAIL_*` (SMTP para envio de comprovativos) | |
| `CLUB_MEMBER_AREA_*` (textos opcionais da área do sócio) | Planos de quota, sócios, pagamentos |
| `BROWSERSHOT_*` (opcional — export PDF/PNG profissional) | |

No `.env` só precisa de **APP_KEY** + **DB_*** + **APP_URL** (em produção) + **CORS** (se usar a área do sócio). O resto configura-se no painel após login.

## Funcionalidades

### Backoffice (`/admin`)

- **Painel** — resumo de sócios e alertas de quotas em atraso / a vencer
- **Sócios** — CRUD, pesquisa por nome/n.º/email, filtros por estado de quota, pagamentos na ficha
- **Conta de acesso** — na ficha do sócio, criar ou atualizar email/password para a área do sócio
- **Planos de quota** — periodicidade, valor, regra de vencimento
- **Pagamentos** — registo na ficha do sócio; ao registar, o comprovativo (PDF) é enviado automaticamente por email ao sócio. Cada pagamento tem ações **Comprovativo** (download) e **Enviar por email** (reenvio)
- **Comunicações** — enviar email a sócios (todos, por estado de quota, ou selecionados) com assunto e mensagem; alternativa por WhatsApp gerando links `wa.me` com a mensagem preenchida (admin/imperador)
- **Definições** — nome do clube, logótipo, cores e campos do cartão (título do painel usa o nome do clube)
- **Sistema** — 2FA obrigatório, dias de aviso de quota, lembretes automáticos de quota por email (só imperador)
- **Utilizadores** — criar/editar contas do backoffice (imperador: todos os perfis; administrador: admin e tesoureiro)
- **Cartão** — impressão no browser ou PDF/PNG no servidor (`/cartao/{id}`, requer login)
- **Validação QR** — URL assinada no cartão (`/validar/{id}`) mostra situação da quota sem login
- **Relatórios** — sócios em atraso e sócios pagantes (quota em dia) em PDF ou Excel/CSV; export em lote de cartões ZIP (agrupados no botão **Relatórios**)

### Área do sócio (`frontend/`)

- Login com email e password (token Sanctum)
- Alteração obrigatória de password no 1.º acesso
- Consulta da situação da quota (em dia, a vencer, em atraso)
- Histórico de pagamentos com download do comprovativo (PDF) de cada pagamento
- Branding do clube (nome, cores, logótipo) via API pública `/api/branding`

### API (`/api`)

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/branding` | Nome, cores e logótipo do clube (público) |
| POST | `/api/login` | Login do sócio → token Bearer |
| POST | `/api/logout` | Terminar sessão (autenticado) |
| GET | `/api/me` | Perfil do sócio autenticado |
| PUT | `/api/me/password` | Alterar password |
| GET | `/api/me/quota` | Situação da quota |
| GET | `/api/me/payments` | Histórico de pagamentos |
| GET | `/api/me/payments/{payment}/receipt` | Comprovativo (PDF) de um pagamento do próprio sócio |

## Exportação de cartões (opcional)

Por defeito, PDF usa DomPDF e PNG usa GD. Para qualidade profissional (Chrome headless), instale Node.js + Google Chrome e configure no `.env`:

```env
BROWSERSHOT_CHROME_PATH="C:\Program Files\Google\Chrome\Application\chrome.exe"
BROWSERSHOT_NODE_BINARY="C:\Program Files\nodejs\node.exe"
BROWSERSHOT_NPM_BINARY="C:\Program Files\nodejs\npm.cmd"
```

## Email (SMTP)

O email é usado em vários sítios: **comprovativos de pagamento**, **comunicações aos sócios** e **lembretes automáticos de quota**.

- **Desenvolvimento:** com `MAIL_MAILER=log`, os emails (incluindo PDFs anexados) são escritos em `storage/logs/laravel.log` — útil para testar sem enviar a sério.
- **Produção:** configure um servidor SMTP no `.env`.

> Depois de alterar o `.env`, reinicie o servidor (`php artisan serve`) ou corra `php artisan config:clear` se a config estiver em cache.

### Opção A — SMTP do alojamento do clube (cPanel)

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.seuclube.pt
MAIL_PORT=465
MAIL_SCHEME=smtps
MAIL_USERNAME=geral@seuclube.pt
MAIL_PASSWORD=********
MAIL_FROM_ADDRESS="geral@seuclube.pt"
MAIL_FROM_NAME="${APP_NAME}"
```

### Opção B — Gmail / Google Workspace

O Gmail **não aceita a password normal**: é preciso uma **App Password** (requer verificação em 2 passos ativa na conta Google → https://myaccount.google.com/apppasswords).

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_SCHEME=smtps
MAIL_USERNAME=oseuemail@gmail.com
MAIL_PASSWORD=apppasswordsemespacos
MAIL_FROM_ADDRESS="oseuemail@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

- `MAIL_USERNAME` é o **email** (não o nome) e não pode ter espaços.
- A App Password tem 16 caracteres; introduza-a **sem espaços** (o Google mostra-a com espaços só para leitura).
- `MAIL_FROM_ADDRESS` deve ser o mesmo email do Gmail (o Google reescreve o remetente para a conta autenticada).

### Comprovativos de pagamento

Ao registar um pagamento na ficha do sócio, o sistema gera o comprovativo em PDF e envia-o por email ao sócio (se tiver email). Pode reenviar a qualquer momento com a ação **Enviar por email** em cada pagamento.

Notas:
- Se o sócio não tiver email na ficha, o pagamento é registado na mesma e aparece um aviso (não bloqueia o registo).
- Se o envio falhar (SMTP mal configurado, etc.), o pagamento fica registado, o erro vai para o log e pode reenviar mais tarde.
- O envio é síncrono. Para não atrasar o registo com servidores SMTP lentos, pode trocar para fila (`QUEUE_CONNECTION=database` + worker) e usar `Mail::to(...)->queue(...)` — o `Mailable` já usa o trait `Queueable`.

## Comunicações aos sócios

Página **Configuração → Comunicações** (admin/imperador) para enviar mensagens em massa:

- **Destinatários:** todos os sócios ativos, por estado de quota (em dia / a vencer / em atraso), ou sócios específicos.
- **Email:** assunto + mensagem (editor de texto); cada email começa por «Olá {nome},» e leva o branding do clube. Mostra contagem de enviados/falhas e regista na Auditoria.
- **WhatsApp (grátis):** gera uma lista de links `wa.me` com a mensagem já preenchida — o operador clica em cada sócio e confirma o envio no WhatsApp. Os números são normalizados para formato internacional (assume **+351** quando têm 9 dígitos).

> O WhatsApp por `wa.me` é semi-manual (um a um). Para envio automático em massa de WhatsApp/SMS é necessário um fornecedor pago (Twilio/Meta).

## Lembretes automáticos de quota (email)

O sistema pode avisar os sócios por email quando a quota está prestes a vencer e ainda não foi paga.

- Ative em **Configuração → Sistema → «Lembretes automáticos de quota por email»**.
- Usa o valor de **«Dias de aviso antes do vencimento»** (mesma página): se faltarem ≤ N dias para o vencimento e o sócio não tiver pago, recebe o lembrete.
- É enviado **uma vez por vencimento** (não repete todos os dias). Quando o sócio paga, o ciclo seguinte volta a poder avisar.
- Só envia a sócios **ativos, com plano e com email**.

O comando que faz o envio:

```bash
php artisan gestao:send-quota-reminders          # envio real (respeita o interruptor)
php artisan gestao:send-quota-reminders --dry-run # mostra quem seria avisado, sem enviar
```

Está agendado para correr **todos os dias às 09:00** (`routes/console.php`). Para o agendador funcionar, é preciso o cron do Laravel ativo:

- **Local/servidor próprio:** `php artisan schedule:work` (processo a correr) — ou um cron de minuto.
- **cPanel:** crie um Cron Job (a cada minuto):

```
* * * * * /usr/local/bin/php /home/UTILIZADOR/app/artisan schedule:run >> /dev/null 2>&1
```

(ajuste o caminho do PHP e do projeto). O `schedule:run` corre a cada minuto e o Laravel decide quando executar a tarefa diária.

> Nota: o WhatsApp **não** é enviado automaticamente — o envio gratuito por `wa.me` exige confirmação manual. Para WhatsApp/SMS automáticos é necessário um fornecedor pago (Twilio/Meta).

## Deploy no cPanel

### Backend

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

CORS_ALLOWED_ORIGINS=https://socios.seuclube.pt
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

### Área do sócio (frontend)

Num subdomínio separado (ex. `socios.seuclube.pt`):

```bash
cd frontend
cp .env.example .env
# VITE_API_URL=https://app.seuclube.pt/api
npm ci
npm run build
```

Publique o conteúdo de `frontend/dist/` no document root desse subdomínio. Confirme que `CORS_ALLOWED_ORIGINS` no backend inclui o URL exacto do frontend.

### phpMyAdmin

A base de dados MySQL pode ser gerida no **phpMyAdmin** do cPanel (backups, consultas). Em produção, use as **migrations** do Laravel para alterar a estrutura.

## Segurança

| Funcionalidade | Descrição |
|----------------|-----------|
| **Password obrigatória no 1.º login** | Contas novas (backoffice e sócios) podem exigir alteração de password; mínimo 12 caracteres |
| **Perfis na BD** | Tabela `permissoes` com 3 níveis; utilizadores do backoffice ligados por `permissao_id` |
| **Contas de sócio** | Utilizadores com perfil «sócio» (`isMember()`); acesso só à API da área do sócio |
| **2FA** | Perfil → autenticação por app. Obrigatoriedade em **Configuração → Sistema** (só imperador) |
| **Ficheiros privados** | Fotos e logótipos servidos só com login (`/files/...`) |
| **Validação QR** | Links assinados com expiração; não expõem dados sensíveis |
| **Auditoria** | Menu «Auditoria» (só imperador) regista alterações a sócios, pagamentos, planos e definições |

### Perfis de utilizador (backoffice)

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

### Dar acesso à área do sócio

Na ficha do sócio (**Sócios → editar**), use **Criar conta de acesso** (ou **Atualizar conta de acesso**). O sócio entra em `https://socios.seuclube.pt` (ou `http://localhost:5173` em desenvolvimento) com esse email e password.

## Estrutura

```
app/
  Filament/          — painel administrativo
  Http/Controllers/
    Api/             — autenticação e dados da área do sócio
  Models/            — sócios, planos, pagamentos, definições
  Services/          — quotas, cartões, contas de sócio
frontend/            — SPA React (área do sócio)
  src/api/           — cliente HTTP (Sanctum Bearer)
  src/pages/         — login, dashboard, pagamentos
resources/views/
  cards/             — templates do cartão de sócio
routes/
  api.php            — API da área do sócio
  web.php            — cartões, validação QR, ficheiros seguros
```
