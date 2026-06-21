# UNIT Platform — Architecture Blueprint
**Version 1.0 · June 2026**
*This document is the guiding principle for all platform and worker development.*

---

## 1. What UNIT Is

UNIT is a **worker platform** — a structured environment where autonomous AI agents (Workers) are built, tested, certified, and deployed to perform real business operations on behalf of tenants.

UNIT does not do the work. **Workers do the work.** UNIT provides the infrastructure, trust layer, and routing so Workers can operate safely, independently, and at scale.

---

## 2. Platform Map

```
┌─────────────────────────────────────────────────────────────────────┐
│                          UNIT PLATFORM                              │
│                                                                     │
│  ┌──────────────┐   ┌──────────────┐   ┌──────────────────────┐   │
│  │  Marketplace │   │  QA Studio   │   │   Tenant Dashboard   │   │
│  │              │   │              │   │                      │   │
│  │ Browse/deploy│   │ Test, certify│   │ View activity,       │   │
│  │ workers      │   │ publish      │   │ manage deployments   │   │
│  └──────┬───────┘   └──────┬───────┘   └──────────┬───────────┘   │
│         │                  │                       │               │
│  ┌──────▼──────────────────▼───────────────────────▼───────────┐  │
│  │                    UNIT SDK Layer                            │  │
│  │                                                              │  │
│  │   UnitPlatform::getInput()     UnitPlatform::commitOutput()  │  │
│  │   UnitPlatform::setStatus()    UnitPlatform::emit()          │  │
│  │   UnitPlatform::log()          UnitPlatform::register()      │  │
│  └──────────────────────────────┬───────────────────────────────┘  │
│                                 │                                   │
│  ┌──────────────────────────────▼───────────────────────────────┐  │
│  │                    Platform Core                             │  │
│  │                                                              │  │
│  │  transactions  ·  worker_deployments  ·  worker_events       │  │
│  │  renewal_register  ·  workers  ·  worker_pricing             │  │
│  │  users  ·  user_gmail_credentials  ·  deployment_billing     │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │                   Queue Infrastructure                       │  │
│  │             Laravel Horizon · Redis · Per-worker queues      │  │
│  │             Queue naming: {worker-slug}-{deployment-id}      │  │
│  │             e.g.  ava-4  ·  nycsca-2  ·  invoicer-7         │  │
│  └──────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 3. Core Principles

### 3.1 Workers Are Self-Contained
A Worker is a **folder** — everything it needs to operate lives inside it.

```
app/Workers/{WorkerName}/
├── Jobs/          # Pipeline stages (queue jobs)
├── Services/      # Worker-specific services (Gmail, Slack, etc.)
└── (no Models)    # Workers never own DB models
```

- Remove the folder → remove the Worker. No platform residue.
- Copy the folder to another UNIT instance → Worker is portable.
- Workers **never** import each other. They communicate via events only.

### 3.2 UNIT Is the Only Gateway
Workers never touch the database directly. All platform interaction goes through the SDK:

```
Worker Job → UnitPlatform::* → Platform DB
```

No `DB::table()` calls inside Worker Jobs. No `TransactionService`. No direct model imports.
The SDK is the contract. It can evolve independently of Workers.

### 3.3 Input In, Output Out
Every Worker Job follows the same lifecycle:

```
1. getInput($txId)       → receive immutable context package
2. do the work           → AI call, API call, data transform
3. commitOutput($txId)   → return structured result to UNIT
4. dispatch(next job)    → advance the pipeline
```

A Job is stateless. It reads context from `WorkerInput`, does one thing, writes one `WorkerOutput`.

### 3.4 Break-Injections (Worker Events)
Workers emit typed events at key pipeline moments. Other Workers subscribe and react — without knowing who emitted them.

```
AVA emits  →  renewal.classified   →  InvoiceWorker picks up
AVA emits  →  renewal.draft_ready  →  SlackWorker notifies team
```

UNIT acts as the event broker. Workers never call each other. Loose coupling is enforced by design.

### 3.5 Memory Is Pre-Loaded
Workers do not query memory tables during pipeline execution. UNIT pre-loads all memory into `WorkerInput` at the start of a transaction. Jobs use `$input->memory[...]` — no DB round-trips mid-pipeline.

### 3.6 Tenant Isolation
Every query, every credential, every output is scoped to `user_id` + `deployment_id`. Cross-tenant access is blocked at the application layer. Credentials are never logged, never returned in API responses, and fetched fresh by UNIT — never carried in queue payloads.

---

## 4. Worker Lifecycle

```
draft → in_testing → qa_passed → published → deprecated
```

| Stage        | Meaning                                                  |
|-------------|----------------------------------------------------------|
| `draft`      | Being built. No deployments allowed.                     |
| `in_testing` | QA Studio active. Fast Track tests running.              |
| `qa_passed`  | All QA checks passed. Ready for marketplace review.      |
| `published`  | Live in Marketplace. Tenants can deploy.                 |
| `deprecated` | Superseded. Existing deployments continue, no new ones.  |

---

## 5. Worker Map — AVA

**AVA — Subscription & Renewal Coordinator**
Monitors an inbox for renewal and subscription emails, understands them, matches them to known clients and assets, drafts personalised renewal communications, and pushes them to Gmail as ready-to-send drafts awaiting human approval.

### 5.1 Pipeline

```
Gmail Webhook / Fast Track
         │
         ▼
┌─────────────────┐
│  ReadEmailJob   │  Parse raw email → plain English summary, urgency,
│  (AI)           │  asset name, expiry date, sender company
└────────┬────────┘
         │  read_output
         ▼
┌──────────────────┐
│ ClassifyEmailJob │  Categorise → Domain Renewal / SSL / Hosting /
│ (AI)             │  SaaS / Failed Payment / Security Alert / Other
│                  │  Priority: Low / Medium / High / Critical
│                  │  ── EMITS: renewal.classified ──────────────────►
└────────┬─────────┘
         │  classify_output
         ▼
┌──────────────────┐
│ MemoryLookupJob  │  Match to known clients, contacts, assets, rules
│ (AI)             │  Confidence score → if < 70% flag low_confidence
└────────┬─────────┘
         │  memory_output
         ▼
┌──────────────────────┐
│  LogTransactionJob   │  Write to renewal_register (audit trail)
│  (no AI)             │  category · asset · client · contact · due date
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│  SelectTemplateJob   │  Pick best template by category
│  (no AI)             │  1. User custom template
│                      │  2. Platform default for category
│                      │  3. Generic fallback
└──────────┬───────────┘
           │  template_output
           ▼
┌──────────────────┐
│  DraftEmailJob   │  Fill template placeholders, or
│  (AI)            │  generate with Claude if template is empty
│                  │  or confidence < 70%
└────────┬─────────┘
         │  draft_output
         ▼
┌──────────────────┐
│  PushToGmailJob  │  Create Gmail draft in tenant's Gmail account
│  (no AI)         │  Fast Track → also sends immediately
│                  │  ── EMITS: renewal.draft_ready ─────────────────►
└──────────────────┘
         │
         ▼
   Human reviews draft in Gmail → approves → sends
```

### 5.2 Break-Injection Events

| Event                | Fires At          | Payload Includes                                                  |
|---------------------|-------------------|-------------------------------------------------------------------|
| `renewal.classified` | ClassifyEmailJob  | category, priority, action, asset (name/type/expiry), ava confidence |
| `renewal.draft_ready`| PushToGmailJob    | draft (gmail_draft_id, subject), full client, contact, asset, service, classification, ava confidence |

Any future Worker can subscribe to either event by declaring it in `blueprint.subscribes`. UNIT routes the payload automatically.

### 5.3 Memory Schema

| Key                  | Source Table    | Scope            | Used For                                 |
|---------------------|----------------|------------------|------------------------------------------|
| `clients`            | clients         | user             | Match email sender to known client       |
| `contacts`           | contacts        | user             | Resolve contact name + email             |
| `assets`             | assets          | user             | Match asset name to tracked subscription |
| `rules` (ava_rules)  | ava_rules       | deployment       | Custom routing rules per deployment      |
| `templates`          | email_templates | user + worker    | Response templates by category           |
| `templates_default`  | email_templates | platform default | Fallback templates for any tenant        |

### 5.4 Input / Output Contract

**Inputs:**
- **Gmail Webhook** — `source: gmail_webhook` — arrives via Cloud Pub/Sub push
- **Fast Track Test** — `source: fast_track_test` — manual inject from QA dashboard

**Outputs:**
- **Gmail Draft** — draft created in tenant's authenticated Gmail account
- **Renewal Register Entry** — logged row: asset, client, contact, category, priority, action, confidence
- **Daily Summary Email** — morning digest of all pending renewals (DailySummaryJob, scheduled)

### 5.5 Configuration

Pipeline config is stored on `worker_deployments.pipeline_config` (JSON) and editable per deployment in the QA dashboard. Defaults:

| Stage          | Max Tokens | Timeout | Retries |
|---------------|-----------|---------|---------|
| read           | 1024      | 90s     | 3       |
| classify       | 1024      | 90s     | 3       |
| memory         | 768       | 90s     | 3       |
| template       | —         | 30s     | 3       |
| draft          | 2048      | 90s     | 3       |
| push           | —         | 60s     | 3       |

### 5.6 Folder Structure

```
app/Workers/AVA/
├── Jobs/
│   ├── ReadEmailJob.php
│   ├── ClassifyEmailJob.php
│   ├── MemoryLookupJob.php
│   ├── LogTransactionJob.php
│   ├── SelectTemplateJob.php
│   ├── DraftEmailJob.php
│   ├── PushToGmailJob.php
│   └── DailySummaryJob.php
└── Services/
    ├── GmailService.php        # Draft creation, email sending
    └── GmailWatchService.php   # Gmail Pub/Sub watch setup
```

---

## 6. SDK Reference

The SDK lives at `app/Platform/SDK/`. It is the only interface between Workers and the platform.

### UnitPlatform

| Method                               | Purpose                                                        |
|-------------------------------------|----------------------------------------------------------------|
| `getInput(txId)`                     | Returns `WorkerInput` — full context for this transaction      |
| `commitOutput(txId, WorkerOutput)`   | Writes stage result to transaction + updates status            |
| `setStatus(txId, status)`            | Lightweight status-only update                                 |
| `emit(txId, WorkerEvent)`            | Fire break-injection event to subscribed workers               |
| `register(txId, record)`             | Write/update renewal register entry                            |
| `log(worker, txId, event, data)`     | Structured event log (Laravel Log with context)                |
| `getDeploymentContext(depId)`        | For scheduled jobs — load deployment without a txId            |
| `getRegisterEntries(userId, depId)`  | For summary jobs — pull register rows for a deployment         |

### WorkerInput (immutable DTO)

```php
$input->txId              // Transaction ID (TX-032)
$input->deploymentId      // Deployment this job belongs to
$input->userId            // Tenant owner
$input->workerSlug        // 'ava'
$input->queue             // 'ava-5' — dispatch next job here
$input->source            // 'gmail_webhook' | 'fast_track_test'
$input->raw               // Raw webhook/test payload
$input->stages            // All completed stage outputs keyed by stage name
$input->memory            // Pre-loaded memory tables
$input->credential        // Gmail OAuth credential (fetched fresh, never serialized)
$input->tenantEmail       // Fallback recipient
$input->pipelineConfig    // Per-stage config (max_tokens, timeout, tries)

// Helpers
$input->stage('read')               // Output of a completed stage
$input->isFastTrack()               // true if source === 'fast_track_test'
$input->maxTokens('draft', 2048)    // Stage token budget with default
$input->timeout('push', 60)         // Stage timeout with default
$input->tries('read', 3)            // Stage retry count with default
```

### WorkerOutput (immutable DTO)

```php
new WorkerOutput(
    stage:        'draft',          // Maps to draft_output column
    status:       'drafting',       // Must match transactions.status ENUM
    data:         $output,          // Array written to stage column as JSON
    category:     'Domain Renewal', // Optional — written to transactions.category
    priority:     'High',           // Optional — written to transactions.priority
    gmailDraftId: $draftId,         // Optional — written to transactions.gmail_draft_id
)
```

### WorkerEvent (break-injection)

```php
new WorkerEvent(
    name:    'renewal.draft_ready',  // Dot-notation event name
    payload: [...]                   // Full handover data for subscribing workers
)
```

### Transaction Status ENUM

Valid values only — writing any other value causes a DB truncation error:

```
received → reading → classifying → memory_lookup → logging
→ templating → drafting → draft_ready → human_review
→ approved → sent → failed
```

---

## 7. Platform Data Map

```
users
  └── worker_deployments          (one deployment per worker per tenant)
        ├── pipeline_config       (JSON — per-stage token/timeout/retry overrides)
        └── credential_id ──────► user_gmail_credentials

workers                           (worker registry — one row per worker type)
  ├── blueprint                   (JSON — pipeline, memory, emits, structure)
  ├── input_schema / output_schema / emit_schema  (JSON — displayed in QA Studio)
  └── qa_checklist               (JSON — certification requirements)

transactions                      (one row per email processed)
  ├── read_output / classify_output / memory_output
  ├── template_output / draft_output / gmail_draft_id
  ├── status (ENUM)
  ├── category / priority
  └── deployment_id / user_id

renewal_register                  (human-readable log of processed renewals)
  └── asset · client · contact · category · priority · status · due_date

worker_events                     (break-injection audit trail)
  └── event_name · tx_id · source_deployment_id · payload · routed_to · status

ava_rules                         (per-deployment routing rules)
assets                            (tenant's tracked assets)
contacts                          (tenant's known contacts)
clients                           (tenant's clients)
email_templates                   (response templates — user + platform defaults)
fast_track_scenarios              (QA test scenarios per deployment)
```

---

## 8. Future Worker Standards

Any new Worker built for UNIT **must** follow these rules to be eligible for Marketplace listing:

1. **Self-contained folder** — `app/Workers/{Name}/Jobs/` + `app/Workers/{Name}/Services/`
2. **SDK-only** — all platform interaction through `UnitPlatform::*`
3. **Stateless Jobs** — each Job reads `WorkerInput`, does one task, writes `WorkerOutput`
4. **Typed events** — break-injections use dot-notation names and carry complete handover payloads
5. **Blueprint declared** — `workers.blueprint` JSON defines pipeline, memory, emits, subscribes, SDK requirements
6. **Schema documented** — `input_schema`, `output_schema`, `emit_schema` seeded with sample payloads
7. **QA certified** — must pass QA Studio checklist before `published` status
8. **No credentials in payloads** — credentials fetched fresh by UNIT, never serialised into queue jobs
9. **Status ENUM compliant** — only write valid transaction status values
10. **Tenant scoped** — every DB operation filtered by `user_id` and `deployment_id`

---

## 9. Marketplace Vision

Workers are organised by **organisation type** — not generic categories.

```
Marketplace
├── NYCSCA Workers     (NYC School Construction Authority)
├── DOB Workers        (Dept of Buildings)
├── FDNY Workers       (Fire Department)
├── MTA Workers        (Metropolitan Transit Authority)
├── General Business
│   ├── AVA — Renewal Coordinator  ← current
│   ├── InvoiceWorker              ← future (subscribes to renewal.draft_ready)
│   └── ...
└── [Future Orgs]
```

Workers are plug-and-play. A tenant deploys from the Marketplace, configures credentials, and the Worker operates autonomously. UNIT handles routing, queueing, memory, and event brokering. The Worker handles the domain logic.

---

*Built with Laravel 11 · Laravel Horizon · Redis · Claude AI · Gmail API*
*UNIT Platform — Franklin, 2026*
