---
name: MVP Gestão de Sócios
overview: SaaS Laravel + Filament + React para clubes/associações gerirem sócios, planos de quota, pagamentos e cartões de sócio, com backoffice administrativo e área do sócio. O núcleo funcional está construído; faltam validações finais, testes, deploy de produção e monitoring externo (UptimeRobot/Cloudflare nas consolas).
todos:
  - id: backoffice-socios
    content: Backoffice Filament com CRUD de sócios (pesquisa, filtros por estado de quota, foto, pagamentos na ficha).
    status: completed
  - id: planos-quota
    content: Planos de quota (periodicidade, valor, tipo e dia de vencimento) e tabelas de lookup.
    status: completed
  - id: pagamentos
    content: Registo de pagamentos por sócio, comprovativos PDF por email e cálculo da situação da quota.
    status: completed
  - id: utilizadores-perfis
    content: Utilizadores do backoffice com 3 perfis (Imperador, Administrador, Tesoureiro) e regras de criação por perfil.
    status: completed
  - id: cartao-socio
    content: Cartão de sócio com template CRC VALE, exportação PDF/PNG e validação por QR com URL assinada.
    status: completed
  - id: api-area-socio
    content: API Sanctum para a área do sócio (login, me, password, quota, pagamentos, comprovativos) e branding público.
    status: completed
  - id: frontend-react
    content: SPA React da área do sócio (login, troca de password obrigatória, dashboard de quota, histórico de pagamentos, branding).
    status: completed
  - id: seguranca-base
    content: Segurança base (2FA opcional/obrigatório, ficheiros privados com login, auditoria de alterações, password mínima 12).
    status: completed
  - id: comunicacoes-lembretes
    content: Comunicações em massa (email/WhatsApp wa.me) e lembretes automáticos de quota por email (cron 09:00).
    status: completed
  - id: modulos-funcionalidades
    content: Sistema de módulos/funcionalidades activáveis pelo Imperador (menu dinâmico, área do sócio, catálogo sync).
    status: completed
  - id: infra-monitoring-codigo
    content: "Fase 1 infra no código: Sentry Laravel+React, ping Healthchecks no cron de lembretes, disco R2 preparado, README."
    status: completed
  - id: infra-monitoring-consolas
    content: "Configurar nas consolas: contas Sentry (2 projetos), UptimeRobot ( /up + frontend), Healthchecks.io (URL no .env)."
    status: pending
  - id: validar-fluxo-ponta-a-ponta
    content: Validar o fluxo completo numa instalação limpa (migrate --seed, login admin, criar sócio, registar pagamento, login do sócio na app).
    status: in_progress
  - id: testes-automatizados
    content: Escrever testes automatizados para regras de quota, API do sócio e permissões por perfil.
    status: pending
  - id: preparar-producao
    content: "Deploy produção (cPanel/MySQL): .env, Sentry/Healthchecks, caches, storage:link, imperador, CORS, build frontend, Cloudflare DNS/CDN."
    status: pending
  - id: validar-com-clube-real
    content: Testar com um clube real (10-20 sócios) e recolher feedback sobre quotas, cartões e área do sócio.
    status: pending
isProject: false
---

# MVP — Gestão de Sócios

## Objetivo do MVP
Permitir que um clube ou associação deixe de gerir sócios em folhas de cálculo e passe a ter:

- uma base única de sócios com situação de quota sempre atualizada;
- registo simples de pagamentos e alertas de quotas em atraso/a vencer;
- cartão de sócio digital com validação por QR;
- comunicações e lembretes automáticos por email;
- uma área online onde cada sócio consulta a sua quota, pagamentos e comprovativos.

O produto **não** tenta ser um ERP de associações. É um gestor focado em sócios + quotas + cartão, que depois pode evoluir.

> Estado actual: o **núcleo funcional e a Fase 1 de infra (código)** estão implementados. Falta configurar monitoring nas consolas externas, validar ponta-a-ponta e o primeiro deploy real.

## Stack
- **Laravel 13** — backend, API, autenticação (Sanctum), regras de negócio e base de dados.
- **Filament 5** — backoffice administrativo (`/admin`) para administradores e tesoureiros.
- **React (Vite)** — área do sócio (`frontend/`), focada em consulta rápida.
- **MySQL/MariaDB** em produção; **SQLite** em desenvolvimento.
- **Monitoring** — Sentry (Laravel + React), Healthchecks.io (cron), UptimeRobot + Cloudflare (consolas).

## Módulos da Fase 1

1. **Sócios**
- Número, nome, email, telefone, data de adesão, foto, estado (ativo).
- Plano de quota associado e situação calculada.
- Conta de acesso à área do sócio (email/password).

2. **Planos de Quota**
- Periodicidade e valor.
- Tipo de vencimento (aniversário de adesão ou dia fixo do mês).

3. **Pagamentos**
- Data, valor, referência e notas por sócio.
- Comprovativo PDF automático por email ao registar pagamento.
- Base para calcular se a quota está em dia, a vencer ou em atraso.

4. **Cartão de Sócio**
- Template com branding do clube (CRC VALE e classic).
- Exportação PDF/PNG e validação por QR (URL assinada, sem login).

5. **Área do Sócio**
- Login, troca obrigatória de password no 1.º acesso.
- Situação da quota, histórico de pagamentos e download de comprovativos.

6. **Comunicações e lembretes**
- Email em massa e links WhatsApp (`wa.me`) no backoffice.
- Lembretes automáticos de quota (cron diário 09:00) com ping Healthchecks.

7. **Módulos (Imperador)**
- Activar/desactivar pacotes (Sócios, Catálogos, Área do sócio, etc.) e funcionalidades associadas.

## Infraestrutura (roadmap)

| Fase | Estado | Conteúdo |
|------|--------|----------|
| **1 — Monitoring** | Código ✅ / consolas ⏳ | Sentry, UptimeRobot, Healthchecks.io |
| **2 — CDN** | No deploy | Cloudflare DNS, SSL, cache assets React |
| **3 — Object Storage** | Preparado, adiar | Cloudflare R2 para fotos/logótipos/cartões |

Ver **README § Infraestrutura** para passos detalhados.

## Fluxo Principal
```mermaid
flowchart TD
    SocioNovo["Criar sócio"] --> Plano["Associar plano de quota"]
    Plano --> Pagamento["Registar pagamentos"]
    Pagamento --> Estado["Calcular situação da quota"]
    Estado --> Cartao["Gerar cartão de sócio + QR"]
    SocioNovo --> Conta["Criar conta de acesso"]
    Conta --> AppSocio["Sócio entra na área do sócio"]
    AppSocio --> Consulta["Consulta quota e pagamentos"]
    Estado --> Alertas["Alertas de quotas a vencer / em atraso"]
    Alertas --> Lembretes["Email automático (cron 09:00)"]
```

## Modelo de Dados (implementado)
- `users` — utilizadores do backoffice e contas de sócio (com `permissao_id`, `member_id`, 2FA).
- `permissoes` — perfis (1 Imperador, 2 Administrador, 3 Tesoureiro).
- `members` — sócios.
- `quota_plans` — planos de quota.
- `payments` — pagamentos por sócio.
- `club_settings` — nome, logótipo, cores e campos do cartão.
- `app_settings` — definições de sistema (2FA obrigatório, dias de alerta, lembretes).
- `modules` / `module_features` — pacotes e funcionalidades activáveis.
- tabelas de lookup de periodicidade/tipo de vencimento e `activity_log` (auditoria).

## Divisão Laravel, Filament e React
**Laravel** concentra a regra de negócio:
- cálculo da situação da quota (em dia/a vencer/em atraso);
- autorização por perfil e por módulo;
- emissão de cartão e validação por QR;
- API da área do sócio;
- ping de healthcheck no cron de lembretes;
- integração Sentry (com DSN no `.env`).

**Filament** é o backoffice:
- gestão de sócios, planos, pagamentos;
- utilizadores e perfis;
- definições do clube, sistema, módulos e funcionalidades;
- comunicações, auditoria e relatórios.

**React** é a experiência do sócio:
- login e troca de password;
- consulta de quota e pagamentos;
- branding do clube;
- Sentry (com `VITE_SENTRY_DSN` no build).

## Issues em aberto (o que falta para fechar o MVP)

- **Monitoring nas consolas** — criar projetos Sentry, monitores UptimeRobot, check Healthchecks e colocar URLs no `.env`. (`infra-monitoring-consolas`)
- **Validação ponta a ponta** numa instalação limpa: `php artisan migrate --seed`, login no `/admin`, criar sócio, registar pagamento, criar conta de acesso e entrar na área do sócio. (`validar-fluxo-ponta-a-ponta`)
- **Testes automatizados** mínimos: regras de quota, endpoints `/api/me/*`, permissões por perfil. (`testes-automatizados`)
- **Preparação de produção**: `.env` (MySQL, APP_URL, CORS, Sentry, Healthchecks), caches, `storage:link`, `gestao:create-imperador`, build do frontend, Cloudflare. (`preparar-producao`)
- **Validação com clube real**: usar com 10–20 sócios reais e recolher feedback. (`validar-com-clube-real`)

## O Que Não Fazer Já
Deixar para depois da validação com clube real:

- Pagamentos online / gateway de pagamento.
- Emissão de recibos/faturação legal.
- WhatsApp/SMS automático em massa (fornecedor pago).
- Multi-clube / multi-tenant avançado num único deploy.
- App mobile nativa.
- Relatórios analíticos avançados.
- **Object Storage R2** — até haver volume de ficheiros ou migração de VPS (disco `r2` já preparado).

## Critério de Sucesso da Fase 1
O MVP está validado se um clube real conseguir, sem ajuda técnica:

- Registar e gerir os seus sócios e planos de quota.
- Registar pagamentos e ver quem está em atraso.
- Receber lembretes automáticos (com cron monitorizado).
- Emitir o cartão de sócio com QR válido.
- Dar acesso aos sócios e estes conseguirem consultar a sua quota online.
- Responder positivamente: "isto poupa-me tempo e evita esquecer quotas?"

E a operação técnica souber de falhas **antes** do cliente (Sentry + Uptime + Healthchecks).
