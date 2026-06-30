# CLAUDE.md — UNIT Platform

## Platform Documentation

- **[UNIT.md](UNIT.md)** — What the platform is, the multi-tenant model, the 11 worker module spec, billing, admin, referral, and the future marketplace vision
- **[WORKER.md](WORKER.md)** — The WorkerContract interface, all 31 contract methods across 8 blocks (including `employee()` persona), WorkerRegistry hybrid model, lifecycle states, NULL contract rule, versioning, worker-to-worker communication
- **[AVA.md](AVA.md)** — AVA's specific implementation: Gmail watch, 8-stage pipeline, renewal register, memory layers, Fast Track, QA checks

Read these before working on platform-level features, adding a new worker, or modifying AVA's pipeline.

---

## Project Overview

UNIT is a multi-tenant SaaS platform that lets organizations deploy AI-powered automation workers. The first worker is **AVA** (Automated Virtual Agent), a renewal coordinator that reads Gmail inboxes, classifies emails, drafts renewal responses, and routes them through a human-review pipeline.

- **Local URL:** http://localhost:8888 (MAMP)
- **Root:** `/Applications/MAMP/htdocs/unit-platform`
- **PHP:** via MAMP stack
- **Database:** MySQL in production/dev, SQLite in-memory for tests

---

## Stack

| Layer | Technology |
|---|---|
| Framework | Laravel (latest) |
| Frontend | Blade templates + Tailwind CSS (Vite compiled) |
| Queue | Laravel Horizon (Redis) — `QUEUE_CONNECTION=sync` in tests |
| Billing | Stripe (webhooks via `StripeWebhookController`) |
| Email | Gmail API (OAuth2, watch/webhook flow) |
| AI | Claude API via `App\Platform\Services\ClaudeService` |
| Auth | Laravel Breeze |

**No Eloquent models are used in controllers.** All DB queries use `DB::table()` (raw query builder). The only Eloquent model is `App\Models\User`.

---

## Architecture

### Controllers (`app/Http/Controllers/`)

15 feature controllers + auth + billing + onboarding. No business logic in `routes/web.php` — it is 246 lines of clean route declarations only.

| Controller | Responsibility |
|---|---|
| `DashboardController` | Home/command center |
| `TransactionController` | TX list, show, status poll, refire, dismiss, delete, decide |
| `WorkerController` | Deploy, show, CRUD, status, connect inbox, fast-track |
| `WorkerMemoryController` | Per-worker memory CRUD |
| `WorkerTemplateController` | Email templates per worker |
| `WorkerRulesController` | Capture rules per worker |
| `MemoryController` | Global memory (clients, contacts, assets, rules) |
| `SettingsController` | API keys, custom models |
| `GmailController` | OAuth callback, watch, webhook ingest |
| `AdminTenantController` | Tenant list, block/unblock, spend cap, trial reset |
| `AdminInfluencerController` | Influencer partner management |
| `ReferralController` | Referral index, influencer redirect, fast-track submit |
| `InfluencerController` | Public apply page + form submit |
| `WorkerPublicController` | Public worker profile page |
| `BillingController` | Stripe checkout, portal |
| `StripeWebhookController` | Stripe event handler |
| `OnboardingController` | Onboarding flow |

### AVA Worker Pipeline (`app/Workers/AVA/`)

Jobs run in sequence on a named queue (`ava-{deployment_id}`):

```
ReadEmailJob → ClassifyEmailJob → MemoryLookupJob → SelectTemplateJob
    → LogTransactionJob → DraftEmailJob → PushToGmailJob
```

Terminal statuses: `draft_ready`, `approved`, `sent`, `failed`, `rejected`, `dismissed`, `blocked`

### Services (`app/Platform/Services/`)

- `WorkerRegistry` — resolves worker contracts by slug
- `ClaudeService` — wraps Claude API calls
- `PolicyEngine` / `PolicyEnforcer` / `UsageGuard` — spend enforcement, blocking
- `PlatformVerificationService` — onboarding verification gates
- `TransactionService` — TX state helpers
- `ReferralService` — referral tracking
- `NotificationEngine` — in-app notifications
- `UnitNotifier` — admin alerts

---

## Database

Key tables (all use `DB::table()`, no Eloquent):

| Table | Purpose |
|---|---|
| `users` | Tenants + admins (`role`: `tenant`/`admin`) |
| `worker_deployments` | Tenant worker instances |
| `transactions` | Pipeline transactions (one per email processed) |
| `renewal_register` | Renewal tracking register |
| `deployment_billing` | Per-deployment billing state + trial tracking |
| `usage_events` | AI token/cost metering |
| `user_gmail_credentials` | OAuth tokens per user |
| `deployment_credentials` | Many-to-many: deployments ↔ Gmail inboxes |
| `platform_events` | Audit log |
| `platform_verifications` | Onboarding verification completions |
| `verification_requirements` | Which verifications are required/blocking |
| `ava_rules` | Capture/processing rules |
| `clients`, `contacts`, `assets` | Memory entities |
| `email_templates` | Draft templates |
| `influencers` | Influencer partner applications |
| `policy_enforcement_log` | Automated enforcement audit trail |

### MySQL-only patterns to watch

Two migrations use `ALTER TABLE ... MODIFY COLUMN ENUM(...)` which is MySQL-only. They are guarded with `if (DB::getDriverName() === 'sqlite') return;` so tests work. Do not remove these guards.

---

## CSS / Theming

The platform supports dark (default) and light themes via `data-theme="light"` on `<html>`.

### Core CSS variables (defined in `resources/views/layouts/app.blade.php`)

```css
--accent          /* always #f1d362 — use for backgrounds/buttons */
--accent-text     /* #f1d362 dark / #7a5c00 light — use for text on light bg */
--accent-rgb      /* 241,211,98 — for rgba() usage */
--bg-card         /* card backgrounds */
--bg-surface      /* surface backgrounds */
--bg-raised       /* slightly elevated surfaces */
--border          /* default border */
--border-subtle   /* rgba(255,255,255,0.10) dark / #e8e8e6 light */
--text-primary    /* main text */
--text-secondary  /* secondary text */
--text-muted      /* muted/label text */
--text-faint      /* very faint text */
```

**Critical rule:** Never use `text-brand` or `bg-brand` Tailwind classes for text — they resolve to `var(--accent)` which is yellow and invisible in light mode. Always use `color:var(--accent-text)` via inline style for text, and `background:var(--accent)` for buttons/backgrounds.

### Badge token variables

Defined for both themes — use these for status/tier badges:

```css
--badge-fast-bg / --badge-fast-text
--badge-balanced-bg / --badge-balanced-text
--badge-powerful-bg / --badge-powerful-text
--badge-reasoning-bg / --badge-reasoning-text
--badge-platform-bg / --badge-platform-text
--badge-yourkey-bg / --badge-yourkey-text
--badge-custom-bg / --badge-custom-text
```

Dark theme: pastel colors. Light theme: saturated darker equivalents. Always use these vars — never hardcode badge hex colors.

---

## Roles & Middleware

| Role | Middleware | Access |
|---|---|---|
| `tenant` | `auth`, `verified`, `onboarded` | Dashboard, workers, transactions, memory, settings |
| `admin` | `auth`, `EnsureAdmin` | All of the above + `/admin/*` routes |

`onboarded` middleware checks:
1. `PlatformVerificationService::isPlatformReady()` — all required `verification_requirements` rows completed
2. `$user->hasCompletedOnboarding()` — `onboarding_completed_at !== null` OR `onboarding_skipped === true`

---

## Automated Tests

### Running tests

```bash
# All tests
php artisan test

# Just our feature tests (fast)
php artisan test tests/Feature/TransactionControllerTest.php tests/Feature/WorkerControllerTest.php tests/Feature/AdminTenantControllerTest.php

# Single test by name
php artisan test --filter="test_refire_resets_failed_tx"

# Stop on first failure
php artisan test --stop-on-failure

# Verbose
php artisan test --verbose
```

### Test setup

- **Driver:** SQLite in-memory (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` in `phpunit.xml`)
- **Queue:** `QUEUE_CONNECTION=sync`
- **Trait:** `RefreshDatabase` on every test class
- **Auth:** `actingAs($user)` — never use session/cookie login
- **DB writes in tests:** Always use `DB::table()->insert()`, not Eloquent `create()`
- **Model updates in tests:** Use `DB::table()->update()` then `$model->refresh()` — Eloquent's `update()` respects `$fillable` and may silently skip guarded columns like `blocked_at`

### UserFactory defaults

Factory users are created with `onboarding_completed_at = now()` and `role = 'tenant'` so they pass all middleware automatically. Override with `User::factory()->create(['role' => 'admin'])` for admin tests.

### Known skips

Two tests in `AdminTenantControllerTest` are skipped on SQLite because `AdminTenantController::show()` uses `DATE_FORMAT()` (MySQL only). They pass against a real MySQL database.

### Test coverage areas

| File | Tests | Covers |
|---|---|---|
| `TransactionControllerTest` | 20 | TX index/filter, show, status poll, refire, dismiss, destroy, approve/reject |
| `WorkerControllerTest` | 12 | Deploy auth, blocked user, destroy ownership, status PATCH, show |
| `AdminTenantControllerTest` | 8 | Admin middleware, block/unblock, spend cap, trial reset |

---

## Email / SMTP

- **From address:** `hello@unit.report`
- **From name:** `UNIT`
- **SMTP host:** `premium105.web-hosting.com` — this is the actual Namecheap shared server hostname. Do NOT use `mail.unit.report`; it causes an SSL cert mismatch because the cert is issued to the server, not the domain alias.
- **Port:** 465
- **Scheme/Encryption:** `smtps` / `ssl`
- **Username:** `hello@unit.report`

All set in `.env`. To test mail from CLI:

```bash
php artisan tinker --execute="Mail::raw('Test', fn(\$m) => \$m->to('purizconsulting@gmail.com')->subject('Test')); echo 'sent';"
```

---

## Key Commands

```bash
# Check all routes
php artisan route:list

# Check routes for a specific controller
php artisan route:list --name=transactions
php artisan route:list --name=workers
php artisan route:list --name=admin.tenants

# Clear caches after config/route changes
php artisan config:clear && php artisan route:clear && php artisan view:clear

# Run migrations
php artisan migrate

# Run tests
php artisan test
```

---

## Things to Avoid

- Do not add Eloquent models — use `DB::table()` to stay consistent with the rest of the codebase
- Do not use `text-brand` or `bg-brand/XX` for text — always use CSS var inline styles
- Do not hardcode hex colors in Blade templates — use CSS variables
- Do not add MySQL-specific SQL (`DATE_FORMAT`, `MODIFY COLUMN`) without the SQLite guard
- Do not put business logic in `routes/web.php` — it belongs in controllers
- Do not use `Queue::fake()` unless testing job dispatch — sync queue runs jobs inline in tests
