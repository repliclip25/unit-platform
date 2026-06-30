# AVA — Automated Virtual Agent

AVA is UNIT's first deployed worker. She is a Gmail-connected AI agent that monitors inboxes for renewal and compliance emails, classifies them, matches them against tenant memory, selects the right template, drafts a personalized response, and pushes it to Gmail Drafts — ready for human review.

**Worker slug:** `ava`
**Version:** `1.4.2`
**Status:** `live`
**Org:** Gmail / General Inbox (`type: platform`)
**Class:** `App\Workers\AVA\AvaWorker`

---

## What AVA Does

1. Watches a Gmail inbox via Google Pub/Sub (push notifications — not polling)
2. When a new email arrives, fetches and parses the raw message
3. Classifies the email: category, priority, urgency, and action required
4. Looks up the tenant's memory: which client, which asset, which contact
5. Logs the transaction to the renewal register
6. Selects the best-matching email template
7. Drafts a personalized reply using Claude
8. Pushes the draft to Gmail Drafts
9. Marks the transaction `draft_ready` and surfaces it for review

**AVA never sends autonomously.** Every draft is deposited in Gmail Drafts. The tenant reviews it there and sends it themselves. The platform's Approve action records the human decision — it does not trigger sending. Reject deletes the draft from Gmail Drafts so it cannot be sent accidentally.

---

## Pipeline (8 Stages)

```
Gmail Pub/Sub Webhook
        ↓
[Stage 0] Inject & Fetch        ← Synthetic / no job class (webhook handler)
        ↓
[Stage 1] ReadEmailJob          ← Parse raw email, extract structured fields
        ↓
[Stage 2] ClassifyEmailJob      ← Category, priority, type, action via Claude
        ↓         ↘ [Branch: not_relevant → terminate: dismissed]
[Stage 3] MemoryLookupJob       ← Match client, contact, asset, fetch rules
        ↓         ↘ [Branch: high_priority → skip to Stage 6]
[Stage 4] LogTransactionJob     ← Write to renewal_register
        ↓
[Stage 5] SelectTemplateJob     ← Score and pick best-match template
        ↓
[Stage 6] DraftEmailJob         ← AI-personalized draft via Claude
        ↓
[Stage 7] PushToGmailJob        ← Create Gmail draft → status: draft_ready
```

Transactions are queued on a named queue: `ava-{deployment_id}`

---

## Branches

AVA declares two pipeline branches. Branch logic is evaluated inside the relevant job class. The contract documents the branches — the jobs enforce them.

| Key | Trigger Stage | Condition | Action |
|---|---|---|---|
| `not_relevant` | classify | `category === "not_relevant"` | Terminate → sets status `dismissed` |
| `high_priority` | memory_lookup | `priority === "critical"` | Skip to stage 6 (Draft) — bypasses Log and Template selection |

The `not_relevant` branch prevents AI token spend on emails AVA cannot act on (spam, internal messages, unrecognized patterns). The `high_priority` branch fast-paths critical-deadline items directly to drafting using the default template fallback.

---

## Job Classes

All in `App\Workers\AVA\Jobs\`:

| Job | Status on start | Status on complete |
|---|---|---|
| `ReadEmailJob` | `reading` | → dispatches ClassifyEmailJob |
| `ClassifyEmailJob` | `classifying` | → dispatches MemoryLookupJob, OR terminates if `not_relevant` |
| `MemoryLookupJob` | `memory_lookup` | → dispatches LogTransactionJob, OR skips to DraftEmailJob if `high_priority` |
| `LogTransactionJob` | `logging` | → dispatches SelectTemplateJob |
| `SelectTemplateJob` | `templating` | → dispatches DraftEmailJob |
| `DraftEmailJob` | `drafting` | → dispatches PushToGmailJob |
| `PushToGmailJob` | `pushing` | `draft_ready` |
| `FastTrackIngestJob` | `ingesting` | → dispatches ReadEmailJob |
| `DailySummaryJob` | _(scheduled)_ | _(sends daily digest email to tenant)_ |

Any stage failure sets status to `failed`. Failed transactions can be re-fired from the Transactions page (resets to `received`, re-dispatches `ReadEmailJob`). Fast Track test transactions cannot be re-fired.

---

## Employee Profile

AVA's `employee()` implementation — how she introduces herself across the platform.

| Field | Value |
|---|---|
| **Name** | AVA |
| **Pronoun** | she |
| **Title** | Renewal Coordinator |
| **Department** | Customer Success |
| **Employer** | Freelancers, Solo Founders, Startup CEOs, Agency Owners |
| **Mission** | Never let a subscription, contract, invoice, or renewal request go unanswered. |

**Introduction** (welcome screens, public profile):
> "Hi, I'm AVA. I make sure you never miss an important renewal. I watch your inbox, understand each renewal request, use what I know about your customers and business, prepare the reply, and leave it in Gmail for your approval."

**What I do** (marketplace card checklist):
- Monitor your Gmail 24/7
- Detect renewal and subscription requests
- Understand the customer using your memory
- Draft a personalized response
- Save it to Gmail Drafts for your review
- Learn from every interaction

**Activity labels** (Command Center stats card):

| Label key | Value |
|---|---|
| `watching` | Inbox for renewal notices |
| `working_on` | renewal responses |
| `waiting_label` | drafts to review |
| `memory_label` | Customer history, subscription plans, writing style, company policies, past renewals |

---

## Platform Integration Methods

AVA's implementations of Block 8 contract methods:

**`ingestJobClass()`** — `App\Workers\AVA\Jobs\ReadEmailJob::class`
The platform dispatches this when a new transaction arrives from the Gmail webhook.

**`fastTrackJobClass()`** — `App\Workers\AVA\Jobs\FastTrackIngestJob::class`
Used for Fast Track test runs — sets up the synthetic email payload before handing off to ReadEmailJob.

**`scheduledJobs()`** — `DailySummaryJob`, cron `0 17 * * *`, per deployment
Sends a daily digest email to the tenant at 5pm summarizing that day's pipeline activity.

**`stuckRecoveryMap()`**

| Status | Recovery job |
|---|---|
| `received` | `ReadEmailJob` |
| `classifying` | `ClassifyEmailJob` |
| `memory_lookup` | `MemoryLookupJob` |
| `logging` | `LogTransactionJob` |
| `templating` | `SelectTemplateJob` |
| `drafting` | `DraftEmailJob` |
| `pushing` | `PushToGmailJob` |

---

## Pricing Tiers

AVA offers three subscription tiers. Stored in `worker_pricing`. Active plan tracked in `deployment_billing.plan_slug`.

| Plan | Price | Transaction Limit | Prompt Overrides |
|---|---|---|---|
| Starter | $49/mo | 100/mo | No |
| Pro | $149/mo | Unlimited | Yes — per stage |
| Enterprise | Custom | Unlimited | Yes + dedicated support |

Pro and Enterprise plans unlock per-deployment prompt overrides (see Prompt Overrides section below).

---

## Changelog

| Version | Date | Summary |
|---|---|---|
| 1.4.2 | 2026-06-20 | Classification accuracy improvements; FDNY added as recognized org |
| 1.4.0 | 2026-06-10 | Branches added: `not_relevant` terminate, `high_priority` fast-path |
| 1.3.5 | 2026-05-15 | Per-deployment prompt overrides added (Pro/Enterprise) |
| 1.3.0 | 2026-05-01 | Fast Track test mode added |
| 1.2.0 | 2026-04-10 | Subscription tiers declared in contract |
| 1.1.0 | 2026-03-20 | Multi-inbox support; `deployment_credentials` many-to-many |
| 1.0.0 | 2026-02-01 | Initial release |

---

## Gmail Connection

AVA connects to Gmail via OAuth2. Each tenant connects one or more Gmail accounts:

```
Tenant clicks "Connect Gmail"
    → GmailController::authorize() — redirects to Google OAuth
    → Google OAuth consent screen
    → GmailController::callback() — stores tokens in user_gmail_credentials
    → GmailService::watch() — registers Gmail Pub/Sub watch via GmailWatchService
        → Google sends push notifications to:
          {APP_URL}/workers/ava/gmail/webhook
    → GmailController::webhook() — receives notification, dispatches ReadEmailJob
```

**Watch expiry:** Gmail watches expire every 7 days. Re-watch is triggered automatically or from the Connect tab. Watch status (`watch_active`, `watch_expires_at`) is tracked in `user_gmail_credentials` and surfaced per inbox on the worker detail page.

**Token handling:** OAuth refresh tokens are stored encrypted in `user_gmail_credentials.refresh_token` (`Crypt::encryptString`). `GmailWatchService` decrypts at runtime — tokens never travel through queue payloads.

**Credentials table:** `user_gmail_credentials` — one row per connected Gmail account.
**Deployment ↔ inbox:** `deployment_credentials` — many-to-many. One deployment can monitor multiple inboxes; one inbox can only be primary to one deployment.

---

## Memory Layers

AVA uses 5 memory layers, all scoped per-tenant:

| Layer | Table | Purpose |
|---|---|---|
| Clients | `clients` | Company or individual names — used to match emails to the right client |
| Contacts | `contacts` | People AVA addresses in drafts — name, email, role |
| Assets | `assets` | Domains, SSL certs, SaaS subscriptions — includes renewal dates |
| Rules | `ava_rules` | Natural-language instructions injected into the draft prompt |
| Templates | `email_templates` | Draft templates selected by SelectTemplateJob |

Memory is global per tenant (shared across all AVA deployments for that tenant). Per-deployment memory override is not currently supported.

Memory can be loaded via manual entry (UI), CSV upload, or bulk import from template.

---

## The Renewal Register

Every transaction AVA processes creates a row in `renewal_register`. This is AVA's primary structured output — the audit trail of every renewal seen and actioned.

```
renewal_register
├── tx_id           — links to transactions table
├── user_id
├── category        — e.g. 'SSL Expiry', 'Domain Renewal'
├── asset           — the domain / cert / subscription being renewed
├── client          — which client this belongs to
├── contact         — who to address in the draft
├── due_date        — when the asset expires
├── priority        — Low | Medium | High | Critical
├── status          — Draft Ready | Approved | Rejected
├── draft_id        — Gmail draft ID
└── human_decision  — approved | rejected
```

---

## Email Templates

Templates live in `email_templates` scoped by `user_id`. AVA ships with platform defaults for:
- Domain Renewal
- SSL Certificate Renewal
- SaaS Subscription Renewal
- General Renewal Notice

Tenants can customize defaults or create their own. `SelectTemplateJob` scores each template against the classified category and priority, picks the best match, and passes it to `DraftEmailJob`.

**Fallback:** If the matched template body is under 100 characters, or if `low_confidence = true`, AVA ignores the template body and prompts Claude to draft freely — with a low-confidence warning injected into the instruction.

---

## Rules

Rules are natural-language instructions stored in `ava_rules`. Examples:

- "Always CC accounts@company.com on SSL renewal emails."
- "If the client is marked as VIP, use formal tone."
- "Never draft a response for emails from noreply@ addresses."

Rules are fetched by `MemoryLookupJob` and injected into the `DraftEmailJob` prompt as a system constraint block. Platform-wide rules (admin-created) apply to all tenants. Tenant rules apply only to their deployments.

---

## Fast Track

Fast Track fires a synthetic email payload through the full pipeline using real credentials and real tenant memory.

1. Tenant clicks "Run Fast Track" on the worker detail page
2. `FastTrackIngestJob` is dispatched with `source: fast_track_test`
3. Full pipeline runs end-to-end
4. A real Gmail draft is created and the transaction lands in `draft_ready`

Fast Track transactions:
- Cannot be re-fired (`source` check in `TransactionController::refire()`)
- Can be permanently deleted (`source` check in `TransactionController::destroy()`)
- Count against trial transaction usage

---

## Deployment Configuration

Stored in `worker_deployments.config` JSON:

| Key | Default | Purpose |
|---|---|---|
| `capture_scope` | `'All incoming emails'` | What emails this deployment processes |
| `capture_keywords` | `''` | Comma-separated keywords to filter on (blank = all) |

---

## Instances

AVA allows multiple deployments per tenant, limited by connected Gmail inboxes — one deployment per Gmail account. A tenant with 3 Gmail accounts can run 3 AVA instances watching 3 separate inboxes.

---

## Pipeline Prompts

### Stage 1 — Read Email
**Uses AI:** Yes
**Model:** `claude-sonnet-4-6`
**Max tokens:** 512

**System:**
```
You are Ava, UNIT's email coordinator. Return valid JSON only. No extra text.
```

**User template:**
```
Read the email below and explain what it means.

Return valid JSON:
{
  "plain_english_summary": "",
  "what_happened": "",
  "action_needed": "",
  "due_date_or_deadline": "",
  "risk_if_ignored": "",
  "urgency": "Low|Medium|High|Critical",
  "questions_for_memory_lookup": []
}

EMAIL:
{RAW_EMAIL}
```

---

### Stage 2 — Classify
**Uses AI:** Yes
**Model:** `claude-sonnet-4-6`
**Max tokens:** 256

**System:**
```
You are Ava, UNIT's email coordinator. Return valid JSON only. No extra text.
```

**User template:**
```
Classify this transaction using the email understanding below.

Categories: Domain Renewal, SSL Expiry, Hosting Invoice, SaaS Renewal,
Failed Payment, Security Alert, Meeting Request, Client Support, not_relevant, Other

Return JSON:
{
  "category": "",
  "subcategory": "",
  "priority": "Low|Medium|High|Critical",
  "required_action": "",
  "register_to_update": "",
  "status": "",
  "reason": ""
}

CONTEXT:
{READ_OUTPUT}
```

**Branch check:** If `category === "not_relevant"`, `ClassifyEmailJob` terminates the pipeline and sets transaction status to `dismissed`.

---

### Stage 3 — Memory Lookup
**Uses AI:** Yes
**Model:** `claude-sonnet-4-6`
**Max tokens:** 768

**System:**
```
You are Ava, UNIT's email coordinator. Return valid JSON only. No extra text.
```

**User template:**
```
Using the email context and memory tables below, find who owns this asset
and how it should be handled.

Return JSON:
{
  "asset": "",
  "matched_client": "",
  "primary_contact_name": "",
  "primary_contact_email": "",
  "related_project_or_service": "",
  "client_preference": "",
  "ava_rule": "",
  "confidence": 0,
  "missing_information": []
}

EMAIL CONTEXT:
{READ_OUTPUT}

MEMORY TABLES:
{MEMORY_TABLES}
```

**Confidence rule:** If `confidence < 70`, AVA sets `low_confidence_warning` and continues — does not abort. The warning is surfaced on the transaction detail page. Rule: AVA-006.

**Branch check:** If `priority === "critical"` (from Stage 2 output), `MemoryLookupJob` skips `LogTransactionJob` and `SelectTemplateJob` — dispatches `DraftEmailJob` directly with default template fallback.

---

### Stage 4 — Log Transaction
**Uses AI:** No
Writes structured data to `renewal_register`. No branch logic.

---

### Stage 5 — Select Template
**Uses AI:** No
Scores available templates against classified category and priority. Picks best match or falls back to the Generic Renewal Notice. Passes template body and tone to `DraftEmailJob`.

---

### Stage 6 — Draft Email
**Uses AI:** Yes
**Model:** `claude-sonnet-4-6`
**Max tokens:** 1024

**System:**
```
You are Ava, a professional email coordinator. Return only the email body —
no subject line, no JSON, no extra text.
```

**User template:**
```
Write an email body using the template structure below.

Template style: {TEMPLATE_NAME}
Tone: {TONE}
Template body to follow:
{BODY_TEMPLATE}

Fill in:
- Contact first name: {FIRST_NAME}
- Asset: {ASSET}
- Client: {CLIENT}
- Due date: {DUE_DATE}
- Category: {CATEGORY}
- Sign as: {SENDER_NAME}

Rules:
- Keep it concise
- Do not promise work is done
- Return only the email body
```

---

### Stage 7 — Push to Gmail
**Uses AI:** No
Creates a Gmail draft via the Gmail API. Sets transaction status to `draft_ready`. The draft is deposited in the tenant's Gmail Drafts folder and goes no further — the tenant reviews and sends it themselves.

---

## Per-Deployment Prompt Overrides

Available on **Pro** and **Enterprise** plans. Tenants can override the system or user prompt for any AI stage without touching the base contract. Overrides are stored in `deployment_prompt_overrides`:

```
deployment_id | stage_key | prompt_type | value | updated_at
```

Each AI job checks for an active override before using the contract default:

```php
$override = DB::table('deployment_prompt_overrides')
    ->where('deployment_id', $this->deploymentId)
    ->where('stage_key', 'classify')
    ->where('prompt_type', 'system')
    ->value('value');

$systemPrompt = $override ?? $this->worker->prompts()['classify']['system'];
```

Overrides are per-stage, per-type, per-deployment. They do not affect other tenants. The base contract prompt is always the fallback when an override is deleted.

---

## QA Checks

| Stage | Check | Field | Threshold | What it verifies |
|---|---|---|---|---|
| `read` | `FIELD_NOT_NULL` | `sender` | — | Email was successfully parsed |
| `classify` | `FIELD_NOT_NULL` | `category` | — | Classification produced a result |
| `classify` | `VALUE_ABOVE` | `confidence` | 0.6 | Classification confidence is acceptable |
| `draft` | `FIELD_NOT_EMPTY` | `draft_body` | — | A draft body was actually produced |
| `push` | `FIELD_NOT_NULL` | `gmail_draft_id` | — | Draft was pushed to Gmail |

---

## Final Output

AVA's terminal deliverable — what the tenant sees and acts on after all stages complete:

| Field | Type | Description |
|---|---|---|
| `gmail_draft_id` | string | Gmail API draft ID — used to send or delete |
| `to` | string | Recipient email resolved from memory lookup |
| `subject` | string | Subject line from template + placeholders |
| `body` | string | Email body — template-filled or Claude-generated |
| `human_review_note` | string? | Internal caution note on transaction detail |
| `low_confidence` | boolean | True when memory match confidence < 70% |

**Destination:** Gmail Drafts + `renewal_register` table

**Human action:** Transactions page → Review & Decide
- **Approve** — records the decision as `approved`. Draft stays in Gmail Drafts. Tenant opens Gmail and sends it themselves.
- **Reject** — records the decision as `rejected`. Draft is deleted from Gmail Drafts via the Gmail API so it cannot be sent accidentally.

Notes entered at review time are stored on the transaction and will feed AVA's learning loop in a future release.

---

## Owner

| Field | Value |
|---|---|
| Type | `platform` (UNIT-built) |
| Name | UNIT |
| Contact | hello@unit.report |
| Website | https://unit.report |
| License | Proprietary |
| SLA | 99.9% pipeline uptime · 4h support response · daily digest on failures |
| Since | 2026 |
| Verified | ✓ |

---

## Services

All in `App\Workers\AVA\Services\`:

| Service | Purpose |
|---|---|
| `GmailService` | OAuth-authenticated Gmail API wrapper — fetch, create drafts, delete drafts |
| `GmailWatchService` | Register and renew Pub/Sub watches per credential; sets `watch_active` and `watch_expires_at` |

AI calls go through `App\Platform\Services\ClaudeService` — not AVA-specific. Platform-level prompt overrides (non-pipeline AI) go through `PlatformClaude`.

---

## Environment Variables

```
GMAIL_CLIENT_ID=
GMAIL_CLIENT_SECRET=
GMAIL_REDIRECT_URI="${APP_URL}/workers/ava/gmail/callback"
GMAIL_PUBSUB_TOPIC=projects/{project}/topics/ava-gmail-inbox
```
