# ddd-template

A lean [Yii3](https://www.yiiframework.com/) starter, extracted from
[`rossaddison/invoice`](https://github.com/rossaddison/invoice), meant to be
built on top of — not another invoicing app.

It carries over only the reusable skeleton:

- Auth (login/signup/password reset) with 2FA (Aegis/TOTP-compatible) and
  RBAC.
- OAuth social login (`yiisoft/yii-auth-client`) — Facebook, GitHub, Google,
  LinkedIn, Microsoft, VKontakte, X, Yandex.
- A bare nav-menu Shell (no domain-specific frills).

...plus one fully worked example of the layering pattern this repo is
actually here to demonstrate — a real **Domain / Application /
Infrastructure / Shell** DDD split (contrasted with the anemic
Cycle-ORM-entity-as-domain-model pattern most of the invoice app itself
uses):

- `src/Domain/{Setting,Telegram}` — pure PHP entities, value objects,
  repository/gateway interfaces. No framework dependency.
- `src/Application/{Setting,Telegram}` — use-case classes (`__invoke`),
  orchestrating Domain interfaces.
- `src/Infrastructure/Persistence/{Setting,Telegram}`,
  `src/Infrastructure/Telegram` — Cycle ORM + Telegram Bot API
  implementations of those interfaces.
- `src/Shell` — the HTTP/presentation layer: `BaseController`, RBAC
  permissions, view injections, controllers, views.

Settings admin and Telegram bot connectivity (token/webhook/chat-id/test
message) are the worked example — copy the same four-layer shape when
adding your own module.

## Status

This repo is **not yet runnable end-to-end** — the Auth/OAuth/2FA
controllers, forms, and RBAC seed data are still being ported over from the
invoice repo. See [`docs/PROGRESS.md`](docs/PROGRESS.md) for exactly what's
done, what's deferred, and how to resume.

## Requirements

- PHP 8.3 – 8.5
- MySQL (or another Cycle ORM–supported database)
- Composer

## Getting started

```bash
composer install
cp .env.example .env
# fill in DB_*, COOKIE_SECRET_KEY, and any OAuth provider credentials you want
```

Database schema is entity-driven (Cycle ORM), not migration-file-driven:
set `BUILD_DATABASE=true` in `.env`, hit the app once to sync tables, then
set it back to `false`.

```bash
php yii serve
```

## Quality

```bash
vendor/bin/psalm --no-cache   # errorLevel=1, findUnusedCode=true — clean
vendor/bin/testo --suite=Unit # once Testo tests are ported (see docs/PROGRESS.md)
```
