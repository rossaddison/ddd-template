# Progress — July 2026

Status snapshot of `rossaddison/ddd-template`: what's built, what's
deliberately deferred, and how to pick the work back up.

## What this repo is

A lean Yii3 starter, extracted from `rossaddison/invoice`, meant to carry
only the reusable skeleton (auth, 2FA, RBAC, a bare Shell) plus one fully
worked **Domain / Application / Infrastructure / Shell** DDD example
(Settings + Telegram bot connectivity) — a pattern to copy when adding your
own modules, not another invoicing app.

## Done

- **Package selection resolved.** `composer install` succeeds clean
  (198 packages). Notable decisions, in case they need revisiting:
  - `cycle/annotated` stays at `^4.6.0` (not downgraded) — this repo uses
    `rossaddison/yii-cycle-1: dev-master`, not the official
    `yiisoft/yii-cycle`, because the latter only publishes `1.0.0`, which
    pins `cycle/annotated ^3.5`.
  - `yiisoft/yii-auth-client: dev-master` (official package, no tagged
    release yet) is used for OAuth — this was tried and works, in
    preference to the `rossaddison/yii-auth-client` fork.
  - PHP constraint is `8.3 - 8.5` (widened from an initial 8.4-only pass)
    to ease dependency resolution.
- **Domain / Application / Infrastructure / Shell example is fully ported**
  and Psalm-clean: `src/Domain/{Setting,Telegram}`,
  `src/Application/{Setting,Telegram}`,
  `src/Infrastructure/Persistence/{Setting,Telegram}`,
  `src/Infrastructure/Telegram/PhptgTelegramGateway.php`, `src/Shell/*`,
  `resources/views/shell/*`, `config/common/routes/routes-shell.php`,
  `config/common/di/shell.php`.
- **`User` entity carries the merged `UserInv` fields**
  (`active`, `language`, `name`, `telegram_chat_id`) directly on
  `src/Infrastructure/Persistence/User/User.php` — `UserInv` and
  `UserRbacLink` were not carried forward at all; RBAC-cycle-db's own
  assignment storage already tracks user↔role by id, so the pairing table
  was pure redundant bookkeeping once `UserInv` stopped existing separately.
- **`vendor/bin/psalm --no-cache` is clean across the whole repo**
  (`errorLevel="1"`, `findUnusedCode="true"`), including config, views, and
  src. See `psalm.xml` for the suppression philosophy — it mirrors
  invoice's own (`PossiblyUnusedMethod`/`PossiblyUnusedProperty` etc.
  suppressed in `src`/`resources` since a template repo's consumers call
  things Psalm can't see), plus two suppressions specific to this repo's
  *current, temporary* incompleteness (see below).

## Deliberately deferred (not started)

The Auth/OAuth/2FA port is the large remaining piece — paused mid-session
by explicit instruction, to be picked up fresh in its own session rather
than tacked onto an already long one. Roughly ~1,900 lines across:

- `src/Auth/Trait/Callback.php`, `Oauth2.php`, `TwoFactorAuth.php`,
  `AuthTfaHelper.php`, `AuthSecurityHelper.php`.
- `AuthController`, `SignupController`, `ForgotPasswordController`,
  `ResetPasswordController`, `ChangePasswordController` — each needs
  `UserInv`/`UserRbacLinkRepository`/`AppConstants` references swapped for
  direct `User` lookups, literal role strings (`shell-admin` / a new
  minimal `user` role), and this repo's own `Application\Setting` layer.
- `src/Auth/Form/{LoginForm,SignupForm,ChangePasswordForm,
  RequestPasswordResetTokenForm,ResetPasswordForm,
  TwoFactorAuthenticationSetupForm,TwoFactorAuthenticationVerifyLoginForm}.php`.
- Auth views (`resources/views/auth/*`, `signup/`, `changepassword/`,
  `forgotpassword/`, `resetpassword/`, the OAuth-related `site/*.php` flash
  pages) and a new `resources/views/layout/guest.php`.
- `resources/rbac/items.php` (doesn't exist yet — needs `shell-admin` +
  a new minimal `user` role).
- `config/common/routes/routes.php` (auth routes — doesn't exist yet).
- DB schema sync verification (`BUILD_DATABASE=true`) — untested since
  there's no controller wiring yet to exercise it.
- Live smoke test: signup → login → 2FA → OAuth → Shell Settings →
  Telegram.
- `testo.php` config + porting `Tests/Testo/Domain`/`Application` over from
  the invoice repo's `ddd-template-shell` branch (253/253 passing there).

### Two temporary Psalm accommodations tied to this gap

- `src/User/Console/CreateCommand.php` depends on `SignupForm`, which
  doesn't exist yet. The `user/create` console command registration is
  commented out in `config/console/params.php`, and the file is excluded
  from `psalm.xml`'s `projectFiles` until `SignupForm` is ported —
  re-enable both at the same time.
- `psalm.xml` suppresses `UnusedParam` and `UnusedClass` in `src/` — several
  classes (e.g. `RateLimiter`, `RecoveryCodeService`) genuinely have no
  callers yet because nothing in `routes.php`/Auth wires them up. Worth
  re-checking whether these suppressions are still earning their keep once
  the Auth port lands and callers exist for real.

## Resuming

Start with the sequencing in the original extraction plan: RBAC + `User` +
Auth controllers (with invoice-domain imports swapped) → schema sync →
signup/login/2FA smoke test → wire routes → Testo tests → final smoke test
covering Shell Settings + Telegram against a real bot.
