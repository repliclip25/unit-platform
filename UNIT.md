# UNIT Platform

## What UNIT Is

UNIT is a multi-tenant SaaS platform for deploying purpose-built AI workers. Each worker is a specialized agent with a declared contract — not a generic chatbot, not a prompt-and-respond tool, but a structured pipeline that reads inputs, reasons with memory, drafts outputs, and routes decisions back to humans.

**UNIT's thesis: AI works best when it has a job title, not a job description.** A worker knows exactly what it processes, what it produces, and when to stop and ask a human. It never guesses outside its domain.

---

## What UNIT Is NOT

- Not a general-purpose AI assistant
- Not a prompt interface or drag-and-drop automation builder
- Not a chatbot framework
- Workers are **not generic** — each is built for a specific org, workflow, or use case
- The marketplace is organized by **organization** (NYCSCA, DOB, FDNY, Gmail, Salesforce), not by category

---

## The Multi-Tenant Model

```
UNIT Platform
├── Tenant A (org: Acme Corp)
│   ├── Deployment 1 — AVA (inbox: accounts@acme.com)
│   └── Deployment 2 — AVA (inbox: compliance@acme.com)
├── Tenant B (org: City Agency)
│   ├── Deployment 1 — AVA (inbox: renewals@agency.gov)
│   └── Deployment 2 — [future worker]
└── Admin
    ├── Manage all tenants
    ├── Block / spend caps / trial reset
    ├── Worker builder
    └── Influencer partner management
```

**Tenant** — a registered user with one or more worker deployments. Role `tenant` in `users` table.
**Deployment** — one running instance of a worker, scoped to a tenant. Lives in `worker_deployments`.
**Admin** — platform staff with `role = 'admin'`. Full access to `/admin/*`.

A single tenant can deploy multiple workers. A single worker type can be deployed multiple times by the same tenant (e.g. separate inboxes, separate clients, separate pipelines).

---

## How Workers Connect to the Platform

Workers are the product. The platform is the infrastructure. The platform never hard-codes worker behavior — it reads everything from a worker's declared contract (`WorkerContract`).

The platform is responsible for:
- Tenant authentication and onboarding
- Deployment lifecycle (deploy, pause, remove)
- Billing and trial enforcement per deployment
- Queue infrastructure (Horizon + Redis)
- Credential storage and OAuth flows
- The transaction model and human-review gate
- Notification engine and policy enforcement
- Admin tooling

Workers are responsible for:
- Declaring their own pipeline stages and job classes
- Defining their memory schema, templates, and rules
- Declaring their onboarding steps and credential requirements
- Defining their subscription tiers and billing behavior
- Producing a typed output that the platform routes to human review

See **WORKER.md** for the full WorkerContract specification.

---

## The Transaction Model

A **transaction** is one unit of work processed through a worker's pipeline. Each worker defines what constitutes a transaction (one email, one lead, one document).

```
received → [worker-defined pipeline stages] → draft_ready
                                                    ↓
                                          Human reviews in platform
                                                    ↓
                              Approve (draft stays in output destination)
                              Reject  (draft deleted from output destination)
                                                    ↓
                                    terminal: approved | rejected
                          failed | dismissed | blocked (other terminals)
```

**Terminal statuses:**

| Status | Meaning |
|---|---|
| `draft_ready` | Pipeline complete, awaiting human review |
| `approved` | Human reviewed and approved — decision recorded, worker defines what happens next |
| `rejected` | Human rejected — output deleted from destination, decision recorded |
| `failed` | Pipeline error — logged, can be refired |
| `dismissed` | Tenant dismissed without deciding — removed from active queue |
| `blocked` | Billing or policy block stopped the pipeline before completion |

**Key principle:** The platform records human decisions but does not act on them. When a tenant approves a transaction, the output (e.g. a Gmail draft) stays in the destination for the tenant to review and send themselves. The platform never sends, submits, or transmits on the tenant's behalf. This is intentional — the human always has the final action.

Human decision notes are recorded at review time and will feed worker memory enrichment in a future release.

---

## The Review & Decide Model

When a worker completes a pipeline run, the output is routed to the human-review gate. The tenant sees the transaction detail — what the worker read, classified, matched in memory, and drafted.

**Approve** — marks the transaction as reviewed and approved. The decision is recorded. What happens next is worker-defined — some workers may leave output in a destination for the tenant to act on, others may trigger a downstream step. The platform itself never acts on approval automatically.

**Reject** — marks the transaction as rejected. The draft is deleted from the output destination so it cannot be sent accidentally. The decision is recorded.

**Notes field** — both decisions accept optional internal notes. These notes are the foundation of AVA's future learning loop: approved + notes = what worked, rejected + notes = what to avoid.

---

## Billing Model

```
Deploy worker
    → deployment_billing created (status: trial)
    → trial_transactions_limit set from worker_pricing.free_transactions
    → trial_transactions_used++ per transaction processed
    → Trial exhausted → PolicyEngine blocks pipeline (TRIAL_EXHAUSTED)
        → Tenant subscribes via Stripe
            → deployment_billing.status = active
                → usage_events records every AI token and cost
                    → Stripe billing at period end
```

Trial limit is sourced from `worker_pricing.free_transactions` for the worker's plan. Each worker defines its own pricing in `worker_pricing` keyed by `worker_slug`. Different workers can have different trial counts and price tiers.

Trial is **per deployment** — a new deployment always starts a new trial.

---

## Onboarding Flow

Onboarding is entirely driven by what the deployed worker declares. The platform has no hardcoded onboarding steps.

```
Register → Platform verifications (declared by worker via platformRequirements())
         → Worker-specific steps  (declared by worker via onboardingSteps())
         → Fast Track test        (declared by worker via fastTrack())
         → onboarding_completed_at set → dashboard access granted
```

Two different workers can have completely different setup flows. The `OnboardingController` reads the active worker's contract at each step — it never references a worker by name.

---

## Notification Engine

`NotificationEngine::evaluate($userId, $role)` returns a prioritized collection of active alerts for a user. Called on every dashboard load.

**Notification sources:**

| Source | Who sees it | Examples |
|---|---|---|
| Platform (admin-only) | Admin | Failed jobs in queue, stuck transactions |
| Platform (shared) | Both | Gmail watch inactive, trial exhausted, trial nearly exhausted |
| Worker contract | Both | Draft ready for review, urgent items open, pipeline stuck |

Worker-declared notifications are defined in `notifications()` on the WorkerContract. Each rule declares a query key, trigger condition, level, message template, and action route. This keeps worker-specific alert logic inside the worker, not the platform.

Notification delivery beyond in-app display (email, push) is gated by platform config and not active by default.

---

## Policy Engine

`PolicyEngine::evaluate($userId, $deploymentId)` returns all active policy violations for a user/deployment pair. `UsageGuard` wraps this and throws `BillingException` to halt pipeline execution.

**Policy codes:**

| Code | Severity | What triggers it |
|---|---|---|
| `TRIAL_EXHAUSTED` | Soft block | Trial transactions used ≥ limit |
| `PAST_DUE` | Hard block | Stripe subscription past due |
| `CANCELED` | Hard block | Subscription canceled |
| `PAUSED` | Soft block | Deployment manually paused |
| `SPEND_CAP_REACHED` | Hard block | Monthly spend cap exceeded |
| `ACCOUNT_SUSPENDED` | Hard block | Admin blocked the account |
| `GMAIL_WATCH_EXPIRED` | Soft block | Gmail inbox watch inactive |

Soft blocks prevent new transactions but do not interrupt in-progress ones. Hard blocks halt the pipeline mid-run and set the transaction to `blocked`.

All enforcement actions are logged to `policy_enforcement_log`.

---

## Influencer / Referral System

Influencer partners apply at `/r/apply` and receive a vanity referral slug `/r/{slug}`. All traffic through that slug is tracked: clicks, signups, paid conversions, MRR attributed, total earned, pending payout.

### Commission formula

Commission has two parts per referred subscriber:

**Part 1 — Acquisition bonus (one-time, paid on activation)**
```
acquisition = subscription_amount × 30%
```

**Part 2 — Retention commission (paid monthly while subscriber is active)**
```
monthly = subscription_amount × rate(C, I)
rate     = base_rate(C) + interval_bonus(I)
```

Where:
- `C` = influencer's total cumulative paid conversions (lifetime)
- `I` = conversions in rolling 90-day window

**Base rate by C:**
| C | Base rate |
|---|---|
| C < 5 | 10% |
| 5 ≤ C < 15 | 15% |
| 15 ≤ C < 30 | 20% |
| C ≥ 30 | 25% |

**Interval bonus by I (rolling 90 days):**
| I | Bonus |
|---|---|
| I ≥ 3 | +2% |
| I ≥ 5 | +3% |
| I ≥ 10 | +5% |

Maximum effective rate: 30% (25% base + 5% bonus).

### Hard ceiling — per subscriber

Retention commission accrues until the influencer hits a per-subscriber lifetime cap:

```
LTV     = subscription_amount × avg_lifetime_months   (platform constant: 18)
ceiling = LTV × ceiling_rate                          (platform constant: 25%)
```

Once cumulative earnings from a single subscriber (acquisition + all retention months) reach the ceiling, no further commission is paid from that subscriber — regardless of how long they remain active.

**Example at $49/mo Pro plan, C=6, I=6 (rate = 18%):**
- Acquisition: $49 × 30% = **$14.70**
- Monthly retention: $49 × 18% = **$8.82/mo**
- LTV estimate: $49 × 18 = $882
- Ceiling: $882 × 25% = **$220.50**
- Cap reached at approximately month 24

The ceiling is plan-proportional — a higher-tier subscriber produces a higher ceiling — but the payback timeline (~24 months) is consistent across plans because the ceiling rate and retention rate are fixed platform constants.

### Payout
- Retention commission paid monthly, auto-calculated against active subscriber list
- Acquisition bonuses paid on the first billing cycle after activation
- Payouts processed via platform wallet (applied to payout balance; manual withdrawal request)

---

## Admin Capabilities

Block/unblock tenants, set spend caps, reset trials, reset passwords, send platform messages, manage influencer partners, void invoices, set billing status, build and publish new workers via the Worker Builder, manage platform AI prompts, review pipeline health, inspect platform events audit log.

---

## Customer Personas

### 1. The Operations Manager
- **Role:** Ops Manager or IT Director at a professional services firm (law, accounting, real estate, government contractor)
- **Pain:** Manual, repetitive workflows that eat coordinator time — renewal tracking, lead processing, compliance filing
- **Budget authority:** $200–$1,000/month on productivity tools
- **Decision trigger:** A deadline missed, a process that failed under headcount pressure
- **What they want:** Something that works without needing to understand AI. Configure it once, trust it runs.
- **Objection:** "We already have a system." (It's a spreadsheet or a shared inbox.)

### 2. The Agency IT Lead
- **Role:** Technical lead at a digital or government agency managing multiple clients or departments
- **Pain:** Repetitive, high-stakes work spread across multiple systems and inboxes
- **Budget authority:** Decides within $500/month autonomously
- **Decision trigger:** A failure that had real consequences. Or being asked to scale without adding headcount.
- **What they want:** Automation that handles routine work and surfaces only what needs a human call.
- **Objection:** "I don't want AI acting on things without my review."

### 3. The Government Agency Administrator
- **Role:** Administrator at a public agency managing compliance, inspections, permits, or filings
- **Pain:** Paper-heavy, deadline-driven workflows with legal consequences for errors
- **Budget authority:** Procurement process — needs a formal contract vehicle
- **Decision trigger:** Audit finding, compliance failure, or a mandate to modernize
- **What they want:** Documented, auditable, controllable — AI that follows rules they define
- **Objection:** "How do we know it's compliant with our data policies?"

### 4. The Referral Convert
- **Role:** A colleague referred by an existing UNIT tenant
- **Pain:** Saw what their peer is doing with UNIT and wants the same result
- **Budget authority:** Low — needs to justify to a manager
- **Decision trigger:** Direct demo or word of mouth from someone they trust
- **What they want:** Quick to deploy, low risk to try, specific to their workflow
- **Objection:** "Does it work for my specific situation?"

---

## Security Infrastructure

### Authentication & Access
- Laravel Breeze auth — bcrypt password hashing
- Google OAuth supported — email verification marked automatically on OAuth signup
- Email verification required before dashboard access
- Admin role enforced via `EnsureAdmin` middleware on all `/admin/*` routes
- Onboarding gate middleware blocks dashboard access until setup is complete
- Session stored in database

### Data Isolation
- All tenant data is scoped by `user_id` on every table
- No cross-tenant queries — controllers always filter by `auth()->id()`
- Worker deployments scoped: `worker_deployments.user_id`
- Transactions scoped: `transactions.user_id`
- Memory (clients, contacts, assets, rules) scoped by `user_id`

### Credential Security
- OAuth refresh tokens stored encrypted in DB (`Crypt::encryptString`)
- Tokens are decrypted at runtime inside the service layer — never passed through queue payloads
- Queue payloads contain only `tx_id` — credentials and memory loaded fresh server-side
- Stripe keys stored only in `.env` — never in DB or exposed to frontend

### Pipeline Security
- `UsageGuard::checkNew()` runs at pipeline entry — checks billing block, spend cap, trial quota before any AI work
- `UsageGuard::checkDeployment()` runs before transaction creation — prevents quota bypass via rapid submissions
- Blocked accounts get `status: blocked` — no further processing, no retry
- Failed jobs retried max 3 times, then set to `failed`

### Spend & Abuse Protection
- `monthly_spend_cap` per user — PolicyEnforcer blocks pipeline when exceeded
- Trial quota per deployment — prevents unlimited free AI usage
- `UsageGuard::blockUser()` with a policy code — audited in `policy_enforcement_log`
- Admin can set, override, or remove caps at any time

### Production Hardening Checklist
- [ ] `APP_DEBUG=false`
- [ ] `SESSION_ENCRYPT=true`
- [ ] `APP_ENV=production`
- [ ] HTTPS enforced
- [ ] Queue worker running via Supervisor (not sync)
- [ ] Redis password set
- [ ] MySQL user has minimal permissions (no DROP, no GRANT)
- [ ] `.env` not web-accessible
- [ ] Stripe webhook secret set and validated in `StripeWebhookController`
- [ ] Google Pub/Sub push endpoint set to production HTTPS URL
- [ ] Error logging to external service (Sentry, Flare, or Bugsnag)

---

## Discounting & Promotions

### Trial Policy
- All deployments start with a free trial — transaction limit set by `worker_pricing.free_transactions`
- Trial is per deployment — a new deployment always gets a new trial
- Trial transactions produce real outputs and count toward the register

### Referral Credits
- **$25 platform credit** for each referred tenant who activates
- Credits applied against the next invoice automatically
- No cap on referral earnings

### Volume Discounts (Org Contracts)
| Deployments | Discount |
|---|---|
| 5–9 | 10% off |
| 10–24 | 20% off |
| 25+ | Custom contract — contact hello@unit.report |

---

## Contracts with Organizations

For government agencies and enterprise clients, UNIT supports formal procurement:

1. **Master Service Agreement (MSA)** — platform access, data handling, liability, IP
2. **Statement of Work (SOW)** — specific workers deployed, volume, SLA commitments
3. **Data Processing Agreement (DPA)** — GDPR / CCPA compliance terms for tenant data
4. **Business Associate Agreement (BAA)** — available for healthcare-adjacent use cases

Annual billing available for agencies that cannot do month-to-month SaaS. Contact: hello@unit.report

---

## Future: Worker Marketplace

Workers will be organized by **organization**, not by use case category:

- **Gmail / Google Workspace** — inbox workers, draft coordinators, email classifiers
- **NYC School Construction Authority (NYCSCA)** — contract, compliance, and inspection workers
- **NYC Department of Buildings (DOB)** — permit, violation, and inspection workers
- **FDNY** — inspection, certification, and compliance workers
- **MTA** — operations, reporting, and compliance workers
- **Salesforce / HubSpot** — CRM-connected lead, follow-up, and enrichment workers

Each org gets its own marketplace section. Tenants browse by the org or system they work with, not by a generic category. Workers within an org share memory schemas and can pass context between each other via `commit()`.

Third-party worker builders can apply to list their workers. UNIT verifies the owner, reviews the contract implementation, and takes a revenue share on subscriptions driven through the marketplace.
