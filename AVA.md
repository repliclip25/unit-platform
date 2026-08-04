# AVA — Automated Virtual Agent

AVA is UNIT's first deployed worker. She is a Gmail-connected AI agent that monitors inboxes for renewal and compliance emails, classifies them, matches them against tenant memory, selects the right template, drafts a personalized response, and pushes it to Gmail Drafts — ready for human review. From there she carries the renewal through the full lifecycle: reminders, invoice/document collection, payment confirmation, updating the asset's next renewal date, and a signed PDF closeout record — not just the first draft.

**Worker slug:** `ava`
**Class:** `App\Workers\AVA\AvaWorker`
**Contract version:** `2.0` (declared in `identity()`)

---

## What AVA Does

1. Watches a Gmail inbox via Google Pub/Sub (push notifications — not polling), **or** picks up an asset directly from the tenant's own asset registry (see **Ingest Paths** below) — a transaction doesn't require an inbound email at all.
2. Classifies the trigger: category, priority, urgency, and action required.
3. Looks up the tenant's memory: which client, which asset(s), which contact.
4. Logs the transaction to the renewal register.
5. Selects the best-matching email template and drafts a personalized reply using Claude.
6. Pushes the draft to Gmail Drafts and waits for a human decision (**Approve & send**, **Approve & proceed**, or **Reject**).
7. Once approved, carries the renewal through fulfillment: invoice, documents, payment confirmation, advancing the asset's renewal date, notifying stakeholders and the client, and generating a signed PDF archive of the entire cycle.
8. Clears the asset's watch state so it re-enters monitoring for its next cycle.

**AVA never sends autonomously** on the drafting side. Every draft is deposited in Gmail Drafts. The tenant reviews it there and sends it themselves (unless `send_mode = 'direct'` is explicitly opted into on approval). Approve records the human decision — it does not by itself trigger sending. Reject deletes the draft from Gmail Drafts so it cannot be sent accidentally.

---

## Ingest Paths — Three Ways a Transaction Starts

A transaction does not require an inbound email. AVA has three distinct entry points, converging on the same pipeline from the `memory` stage onward:

| Path | Trigger | Entry point | Notes |
|---|---|---|---|
| **Gmail webhook** | A real email arrives in the connected inbox | `ReadEmailJob` → `ClassifyEmailJob` (AI-driven) | The only path that runs `ReadEmailJob`/`ClassifyEmailJob` against real AI — everything below synthesizes `read`/`classify`/`memory` output directly since there's no email to parse. |
| **Human Trigger** | Tenant clicks **Renew Now** (single asset) or **Renew Group Now** (a `renews_together` group) | `App\Workers\AVA\Services\AssetTransactionSynthesizer::create()` / `::createForGroup()` | On-demand, any time — e.g. a client asks directly and the tenant wants to push it into the pipeline right now instead of waiting for tomorrow's scan. Source tagged `human_trigger`. Guarded against creating a duplicate in-flight transaction for the same asset(s) within 24h. |
| **Asset Expiry Watch** | Daily scan (`AssetExpiryWatchJob`, 7AM cron) crosses a renewal threshold | Same synthesizer as Human Trigger | Thresholds: 30/14/7/1 days out, or overdue. `renews_together` groups are clustered *before* the per-asset loop and triggered off whichever member's date comes first — every dated member joins the bundle, not just the one that crossed threshold. `asset_watch_log` dedupes so the same threshold doesn't refire for the same asset. |

Both synthetic paths call `AssetTransactionSynthesizer`, which builds `read`/`classify`/`memory` stage output directly from the asset record (no `ReadEmailJob`/`ClassifyEmailJob` run against nothing) and then calls `UnitPlatform::advance($tx, 'memory')` to enter the normal pipeline from `select_template` onward.

---

## Pipeline (17 Stages)

The pipeline has two halves: a fast synchronous **drafting chain** (stages 0–6, same job dispatches the next one), and a slower, event-driven **fulfillment continuation** (stages 7–16) that waits on human action and can span days or weeks.

As of contract version 2.1, `human_decide` sits **before** `push_draft` — a human reviews the draft on the Transaction Center card itself and decides first; only then does AVA actually create the Gmail draft (or surface it in-app) and, if `send_mode = direct`, send it. This matches how a human coordinator actually works (write, review, then send) and means Reject never has anything to delete — nothing is created until after a decision. See the `2.1` entry in **Contract Versioning** below for the full reasoning and what moved.

```
Gmail Pub/Sub Webhook  ─┐
Human Trigger          ─┼─→ (see Ingest Paths)
Asset Expiry Watch      ─┘
        ↓
[ 0] Inject & Fetch          ← Synthetic / no job class
        ↓
[ 1] ReadEmailJob             ← Parse raw email, extract structured fields (Gmail path only)
        ↓
[ 2] ClassifyEmailJob         ← Category, priority, type, action via Claude (Gmail path only)
        ↓         ↘ [Branch: not_relevant → terminate: dismissed]
[ 3] MemoryLookupJob          ← Match client, contact, asset, fetch rules
        ↓         ↘ [Branch: high_priority → skip to stage 6]
[ 4] LogTransactionJob        ← Write to renewal_register
        ↓
[ 5] SelectTemplateJob        ← Score and pick best-match template
        ↓
[ 6] DraftEmailJob            ← AI-personalized draft via Claude → status: draft_ready
        ↓
── FULFILLMENT (event-driven — waits on human action) ──
[ 7] human_decide             ← HARD GATE. Approve & send / Approve & proceed / Reject — reviewed here, on this card
        ↓
[ 8] PushToGmailJob           ← Create Gmail draft (or surface in-app) → send immediately if send_mode = direct
        ↓
[ 9] request_invoice          ← soft gate. Attach an invoice — never blocks the renewal
        ↓
[10] request_documents        ← skippable. Any documents to send the client?
        ↓
[11] confirm_payment          ← HARD GATE. You confirm — AVA reminds until you do
        ↓
[12] update_renewal_date      ← Advances the asset(s) to their next cycle
        ↓
[13] notify_stakeholders      ← Emails the tenant the renewal is complete
        ↓
[14] notify_customer          ← Tells the client directly (opt-in, off by default)
        ↓
[15] archive_evidence         ← Combines the whole cycle into one signed PDF
        ↓
[16] schedule_next_watch      ← Asset re-enters continuous monitoring
```

Nudges (`ApprovalReminderJob`) are a separate, gate-watching daily cron — not a pipeline stage of their own. They watch `human_decide` directly (`fulfillment_stage = 'human_decide' AND human_decision IS NULL`) on a priority-scaled cadence, independent of stage order. The Transaction Center surfaces them as a small "N nudges sent" badge on the `human_decide` stage header rather than as a stage of their own.

Transactions are queued on a named queue: `ava-{deployment_id}`

**Gate types** (declared per-stage in `pipelineStages()`, generic enough for any future worker to reuse the same Transaction Center UI):

| Gate type | Meaning |
|---|---|
| `hard` | `advance()` halts here entirely. Nothing auto-dispatches the next job until a human acts (`TransactionController::decide()` / `confirmRenewal()` / `cancelRenewal()`), which is what calls `advance()` again to resume. |
| `soft` | A human action is offered (e.g. attach a file) but the pipeline keeps moving regardless — AVA nudges separately if it's ignored. |
| `skippable` | A yes/no decision point that never blocks either way, it just branches. |
| *(none)* | Fully automated, no human involvement. |

Every trigger, stage, and message gate is individually toggleable per deployment — see **Gating Architecture** below. Not every transaction needs to reach stage 16 — a rejected decision, or fulfillment with "no invoice needed," still delivered real value at whatever it did complete.

---

## Branches

AVA declares two pipeline branches on the drafting chain. Branch logic is evaluated inside the relevant job class; the contract documents the branches, the jobs enforce them.

| Key | Trigger Stage | Condition | Action |
|---|---|---|---|
| `not_relevant` | classify | `category === "not_relevant"` | Terminate → sets status `dismissed` |
| `high_priority` | memory_lookup | `priority === "critical"` | Skip to stage 6 (Draft) — bypasses Log and Template selection |

The `not_relevant` branch prevents AI token spend on emails AVA cannot act on (spam, internal messages, unrecognized patterns). The `high_priority` branch fast-paths critical-deadline items directly to drafting using the default template fallback.

---

## Job Classes

All in `App\Workers\AVA\Jobs\`:

**Drafting chain**

| Job | Status on start | Status on complete |
|---|---|---|
| `ReadEmailJob` | `reading` | → dispatches `ClassifyEmailJob` |
| `ClassifyEmailJob` | `classifying` | → dispatches `MemoryLookupJob`, OR terminates if `not_relevant` |
| `MemoryLookupJob` | `memory_lookup` | → dispatches `LogTransactionJob`, OR skips to `DraftEmailJob` if `high_priority` |
| `LogTransactionJob` | `logging` | → dispatches `SelectTemplateJob` |
| `SelectTemplateJob` | `templating` | → dispatches `DraftEmailJob` |
| `DraftEmailJob` | `drafting` | → dispatches `PushToGmailJob` |
| `PushToGmailJob` | `pushing` | `draft_ready` |
| `FastTrackIngestJob` | `ingesting` | → dispatches `ReadEmailJob` |

**Fulfillment chain** (dispatched by `advance()` once a human clears each gate)

| Job | Purpose |
|---|---|
| `RequestInvoiceJob` | Opens the invoice-attach step; on upload, `InvoiceOcrService` extracts amount/currency/dates via Claude |
| `RequestDocumentsJob` | Skippable document-attach step |
| `UpdateRenewalDateJob` | Advances `assets.renewal_date` by `renewal_cadence_days` — for a bundle, every member ID in `line_items`, each by its own cadence |
| `NotifyStakeholdersJob` | Emails the tenant that the renewal closed |
| `NotifyCustomerJob` | Emails the client directly (gated off by default) — next renewal date and roughly when to expect the next reminder |
| `ArchiveEvidenceJob` | Builds the closing PDF: every draft round, gate decisions, invoice/documents, payment confirmation, bundle breakdown if applicable |
| `ScheduleNextWatchJob` | Clears `asset_watch_log` for the asset(s) so the daily scan can re-trigger their next cycle |

**Nudge / cadence jobs** (scheduled — see `scheduledJobs()` below)

| Job | Purpose |
|---|---|
| `ClientReminderCycleJob` | Drives the 30/15/0-day client reminder cadence — re-drafts and re-dispatches for review at each threshold |
| `PaymentReminderJob` | Nudges the tenant while `confirm_payment` is outstanding |
| `ApprovalReminderJob` | Nudges the tenant while a draft is awaiting Approve/Reject |
| `InvoiceNudgeJob` | Nudges the tenant while `request_invoice` is outstanding |
| `InvoiceFollowUpJob` | Follows up with the *client* after an invoice was attached (gated: `request_invoice_followup`) |
| `WeeklySummaryJob` | Weekly digest email to the tenant |
| `AssetExpiryWatchJob` | Daily scan — see **Ingest Paths** |
| `FilterEmailJob` | Pre-classification filtering support |

Any stage failure sets status to `failed`. Failed transactions can be re-fired from the Transactions page (resets to `received`, re-dispatches `ReadEmailJob` — this recovery path is drafting-chain only, see `stuckRecoveryMap()` below). Fast Track test transactions cannot be re-fired.

---

## Gating Architecture

Every trigger, stage, and client-facing message in the fulfillment lifecycle is individually toggleable per deployment, via the **AVA Settings** page (`WorkerController::settings()`/`updateSettings()`). Backed by the `deployment_stage_settings` table (`deployment_id`, `type`, `key`, `enabled`) — no row for a key means "use the default."

`UnitPlatform::gateEnabled(?int $deploymentId, string $key, bool $default = true): bool` is the single lookup point every gated stage checks. In `advance()`'s scan loop, a disabled stage (including hard gates) is skipped entirely and the scan continues — a chain of several consecutive disabled stages all get skipped in one pass, all the way to the end if needed.

**`WorkerController::GATE_SECTIONS`** — the source of truth for what's shown on the AVA Settings page:

| Section | Key | Label | Default |
|---|---|---|---|
| Trigger | `gmail_watch` | Connect Gmail Watch | on |
| Trigger | `asset_watch` | Connect Asset Watch | on |
| Stage | `request_invoice` | Request Invoice | on |
| Stage | `request_documents` | Request Document | on |
| Stage | `confirm_payment` | Request Payment | on |
| Stage | `archive_evidence` | Generate Closeout Report | on |
| Message | `client_cadence` | Send Reminders (3 cadence) | on |
| Message | `nudge_me` | Nudge Me | on |
| Message | `request_invoice_followup` | Invoice Follow-up to Client | on |
| Message | `notify_stakeholders` | Renewal Complete Notice | on |
| Message | `notify_customer` | Renewal Complete Notice to Client | **off** |

`notify_customer` is the one gate that defaults **off** — it's the newest capability and the only one that emails a tenant's customer directly on AVA's behalf, so every existing deployment has to opt in rather than suddenly starting to do this. Every other gate defaults on, matching legacy behavior before gating existed.

`human_decide` is deliberately **not** in this list — it is not toggleable. A human always makes the approve/reject decision; only *what happens automatically once they do* (cadence timing, invoice follow-up, the closing notice) is configurable.

The Transaction Center shows a dashed "skipped — disabled in settings" tag on any stage that was bypassed via a gate, and `ArchiveEvidenceJob`'s PDF is honest about it too — a stage with no data because it was turned off reads differently from one that simply had nothing to report.

---

## Client Reminder Cadence & Human Decisions

A real (non-Fast-Track) transaction can draft up to **3 client-facing reminder rounds** on a 30/15/0-day cadence, driven by `ClientReminderCycleJob` (daily at 8AM). Since 2.1, `DraftEmailJob` decides whether a round even needs a fresh decision — a first round always halts at `human_decide`; a round 2/3 redraft, once round 1 has been approved once, dispatches `PushToGmailJob` directly without asking again. `TransactionController::decide()` is where a human acts on a round that does reach the gate:

- **Approve & send** — approves the current round and dispatches `PushToGmailJob` to actually deliver it (create the Gmail draft, or surface in-app; send immediately if `send_mode = direct`). If it's not yet the final round (and `client_cadence` is on), fulfillment does *not* unblock — this round's draft still gets delivered, but the pipeline waits for `ClientReminderCycleJob` to draft and surface the next scheduled round. Only approving the 3rd round (or the only round, if `client_cadence` is off) unblocks fulfillment.
- **Approve & proceed** — a second button, for when the tenant already closed the renewal with the client *outside* AVA (phone, WhatsApp, in person) and doesn't want to wait through the remaining rounds. Forces the current approval to be treated as final regardless of round number, sets `transactions.cadence_skipped = true`, and immediately advances into fulfillment *without ever creating a draft* — nothing should go out over email for a deal already closed elsewhere. Logged as a distinct `human_decide_skip_cadence` event, and the Archive PDF notes it explicitly rather than silently looking like an incomplete 1-of-3 cadence.
- **Reject** — records the decision. Nothing is ever created before a decision (since 2.1), so there's nothing to delete. Never advances past `human_decide`.

`send_mode` (`draft` default, or `direct`) is a per-deployment setting: `direct` means UNIT sends the approved draft immediately via Gmail instead of leaving it for the tenant to send themselves. Never applies to Fast Track, `skip_cadence` approvals, or when there's no draft/credential to send.

---

## Asset Groups & Bundled Renewals

Asset Groups (`asset_groups` / `asset_group_items` tables, worker-scoped via `groupTypes()`) let a tenant organize memory assets into logical bundles — e.g. a client's domain + SSL + hosting, all one real-world service.

**`renews_together`** is an explicit, tenant-set boolean on a group — *not* inferred from renewal-date proximity. Real-world precedent: a tenant's actual invoice can bundle a domain + SSL + hosting renewal into one line-itemed bill even when each asset's individually-tracked `renewal_date` doesn't line up — the bundle is billed together because that's how the tenant actually runs it.

When a `renews_together` group's earliest member crosses a watch threshold (or a tenant clicks **Renew Group Now**), `AssetTransactionSynthesizer::createForGroup()` builds **one transaction covering every member**, not one per asset:

- `memory.asset` is set to a group label (e.g. `"Baskel — 3 services"`) for backward compatibility with every reader that expects a single string.
- `memory.line_items` is added additively — an array of `{ id, name, type, vendor, renewal_date }` per asset.
- The draft body itemizes every asset via the `{{line_items}}` placeholder (or an automatic append if the template doesn't reference it) — a bundle's draft always shows what's actually being renewed, never just the group label.
- `UpdateRenewalDateJob` and `ScheduleNextWatchJob` both act on every member ID in `line_items`, not just one.
- The Transaction Center and Archive PDF both show the itemized breakdown.
- A mixed-type bundle (e.g. domain + hosting + SSL) falls to category `Other` rather than being mislabeled as whichever type happens to sort first.

**Renew Now** (per-asset) and **Renew Group Now** (per-group) are both Human Trigger actions, available regardless of whether `renews_together` is set — the flag only controls *automated* watch behavior; a human can always explicitly ask for either right now.

**Fix Dates** — a data-integrity tool on the Asset Groups page, separate from the transaction pipeline. A file-imported asset commonly arrives with no `renewal_date`, and `renewal_cadence_days` has no UI anywhere else in the app at all (every asset silently defaults to a 365-day cadence at renewal time otherwise). Fix Dates opens an inline panel per group — one row per asset, an editable date and a cadence dropdown (Monthly/Quarterly/Annual/Biennial), saved as one batch request.

AVA declares 4 group types (`groupTypes()`): `service_bundle`, `vendor_cluster`, `expiry_window`, `contract_scope`.

---

## Employee Profile

AVA's `employee()` implementation — how she introduces herself across the platform.

| Field | Value |
|---|---|
| **Name** | AVA |
| **Pronoun** | she |
| **Title** | AI Renewal Coordinator |
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

## Personas

AVA declares 4 personas (`personas()`), stored on `worker_deployments.persona`. Drives the onboarding persona step, memory step copy/asset types, and per-persona nudge email copy.

| Persona | Role |
|---|---|
| `it_agency` | **The founding use case.** IT / digital agency domain-hosting-vendor renewals — the original problem AVA was built to solve. Asset types: Domain, SSL Certificate, Hosting Plan, SaaS Subscription, Other. Everything else in AVA's default copy and category vocabulary is tuned around this persona first. |
| `insurance_broker` | Secondary expansion segment |
| `compliance` | Secondary expansion segment |
| `other` | Generic fallback |

`insurance_broker` and `compliance` are not co-equal with `it_agency` — they're later additions to widen the addressable market, not the primary design target.

---

## Platform Integration Methods

AVA's implementations of Block 8 contract methods:

**`ingestJobClass()`** — `App\Workers\AVA\Jobs\ReadEmailJob::class`
The platform dispatches this when a new transaction arrives from the Gmail webhook. Human Trigger and Asset Expiry Watch bypass this entirely (see Ingest Paths).

**`fastTrackJobClass()`** — `App\Workers\AVA\Jobs\FastTrackIngestJob::class`
Used for Fast Track test runs — sets up the synthetic email payload before handing off to `ReadEmailJob`.

**`scheduledJobs()`** — 7 cron jobs, all `per_deployment: true`:

| Job | Cron | Purpose |
|---|---|---|
| `WeeklySummaryJob` | `0 8 * * 1` (Mondays 8AM) | Weekly digest to the tenant |
| `AssetExpiryWatchJob` | `0 7 * * *` (daily 7AM) | Renewal-date scan — see Ingest Paths |
| `PaymentReminderJob` | `0 9 * * *` | Nudges while `confirm_payment` is outstanding |
| `ApprovalReminderJob` | `0 9 * * *` | Nudges while a draft awaits Approve/Reject |
| `ClientReminderCycleJob` | `0 8 * * *` | 30/15/0-day client cadence — runs before the 9AM nudges so a fresh draft exists first |
| `InvoiceNudgeJob` | `0 9 * * *` | Nudges while `request_invoice` is outstanding |
| `InvoiceFollowUpJob` | `0 10 * * *` | Client-facing follow-up after an invoice is attached |

**`stuckRecoveryMap()`** — covers the drafting chain only; fulfillment stages don't have a "stuck status" the same way (`transactions.status` stays fixed at `approved`/`sent` through fulfillment — progress there is tracked via `fulfillment_stage` instead, see the doc comment on `WorkerOutput`):

| Status | Recovery job |
|---|---|
| `received` / `reading` | `ReadEmailJob` |
| `classifying` | `ClassifyEmailJob` |
| `memory_lookup` | `MemoryLookupJob` |
| `logging` | `LogTransactionJob` |
| `templating` | `SelectTemplateJob` |
| `drafting` | `DraftEmailJob` |

**`billing()`** — `trial_transactions: 25`, `trial_days: 30`, `billing_unit: 'email'`, `unit_label: 'email processed'`.

**`defaultPlan()`** — `'starter'`.

**`aiStages()`** — `read`, `classify`, `memory`, `template`, `draft` (model slots configurable per plan tier in the admin pricing panel; `request_invoice`'s OCR extraction also calls Claude but is not a per-deployment-configurable model slot here).

**`memoryRequirements()`** — `clients: ['name']`, `contacts: ['name', 'email']`, `assets: ['name', 'renewal_date']`. Used by the platform to compute memory health scores and drive enrichment nudges.

---

## Pricing Tiers

AVA offers three subscription tiers. Stored in `worker_pricing`. Active plan tracked in `deployment_billing.plan_slug`. (This table reflects `worker_pricing` row configuration, which is separate from and layered on top of the `billing()`/`defaultPlan()` contract declarations above.)

| Plan | Price | Transaction Limit | Prompt Overrides |
|---|---|---|---|
| Starter | $49/mo | 100/mo | No |
| Pro | $149/mo | Unlimited | Yes — per stage |
| Enterprise | Custom | Unlimited | Yes + dedicated support |

Pro and Enterprise plans unlock per-deployment prompt overrides (see Per-Deployment Prompt Overrides below).

---

## Changelog

| Version | Date | Summary |
|---|---|---|
| **2.1** | **2026-08-04** | **`human_decide` moved ahead of `push_draft`** — a human now reviews the draft on the Transaction Center card itself and decides *before* AVA creates anything in Gmail, matching how a coordinator actually works (write, review, then send). Reject no longer has anything to delete. Approve & proceed now means no draft is ever created, not just "created but left unsent." Nudges required zero changes — already fully decoupled from stage order. Breaking: any transaction in-flight at the old post-`push_draft` `human_decide` position should be canceled, not resumed. |
| **2.0** | **2026-08-02** | **Version bump reflecting everything below** — the 8-stage drafting pipeline is now the front half of a full 17-stage renewal lifecycle, with two new no-email ingest paths, per-deployment gating, asset bundling, personas, and Approve & proceed. Breaking: `output_shape`/`pipeline()` structure changed past the old `draft_ready` terminal point, and new gate keys were introduced in `deployment_stage_settings`. **Not breaking for existing deployments in practice** — every new gate defaults "on" (matching pre-2.0 behavior) except `notify_customer`, which defaults off and must be opted into. See `versionChangelog()` for full upgrade notes. |
| — | 2026-08-02 | Approve & proceed (skip remaining reminder cadence for deals closed outside AVA); NotifyCustomerJob respects it too |
| — | 2026-08-02 | Fixed: Human Trigger / watch-synthesized transactions never set `transactions.category`/`.priority` — showed "Processing..." forever |
| — | 2026-08-02 | Fix Dates: bulk-set renewal date + cadence per asset group |
| — | 2026-08-02 | Renew Group Now — group-level Human Trigger; Transaction Center + Archive PDF bundle visibility |
| — | 2026-08-02 | `{{line_items}}` draft placeholder — bundled transactions itemize every asset in the client-facing draft, not just the group label |
| — | 2026-08-02 | Asset Groups: `renews_together` flag, `AssetTransactionSynthesizer::createForGroup()`, clustered watch detection |
| — | ~2026-08-01 | Full gating architecture: `deployment_stage_settings`, AVA Settings page, `gateEnabled()`, Human Trigger ("Renew Now") |
| — | ~2026-08-01 | `notify_customer` stage — opt-in client-facing closing message, default off |
| — | earlier | Full fulfillment pipeline added: `human_decide` → `request_invoice` → `request_documents` → `confirm_payment` → `update_renewal_date` → `notify_stakeholders` → `archive_evidence` → `schedule_next_watch` |
| — | earlier | Client reminder cadence (30/15/0-day, `ClientReminderCycleJob`) |
| — | earlier | `personas()` — 4 ICP personas driving onboarding/memory copy |
| — | earlier | Classification accuracy improvements; FDNY added as recognized org |
| — | earlier | Branches added: `not_relevant` terminate, `high_priority` fast-path |
| — | earlier | Per-deployment prompt overrides added (Pro/Enterprise) |
| — | earlier | Fast Track test mode added |
| — | earlier | Subscription tiers declared in contract |
| — | earlier | Multi-inbox support; `deployment_credentials` many-to-many |
| **1.0** | earlier | Initial release |

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

**Token handling:** OAuth refresh tokens are stored encrypted in `user_gmail_credentials.refresh_token` (`Crypt::encryptString`). `GmailWatchService` decrypts at runtime — tokens never travel through queue payloads. **A revoked or expired refresh token surfaces as `RuntimeException: Gmail token refresh failed: invalid_grant` inside `PushToGmailJob`** — this fails every transaction reaching the drafting stage until the tenant reconnects Gmail from the Integrations page. It is not a data problem; the "Data failure" banner on a failed transaction can be misleading in this case since the real cause is an expired credential, not bad memory/asset data. (`failed_jobs` table / `storage/logs/laravel.log` are currently the only places the real exception text surfaces — a tenant-facing fix for this is tracked separately.)

**Credentials table:** `user_gmail_credentials` — one row per connected Gmail account.
**Deployment ↔ inbox:** `deployment_credentials` — many-to-many. One deployment can monitor multiple inboxes; one inbox can only be primary to one deployment.

---

## Memory Layers

AVA uses 5 memory layers, all scoped per-tenant:

| Layer | Table | Purpose |
|---|---|---|
| Clients | `clients` | Company or individual names — used to match emails to the right client |
| Contacts | `contacts` | People AVA addresses in drafts — name, email, role |
| Assets | `assets` | Domains, SSL certs, SaaS subscriptions — includes `renewal_date` and `renewal_cadence_days` |
| Rules | `ava_rules` | Natural-language instructions injected into the draft prompt |
| Templates | `email_templates` | Draft templates selected by `SelectTemplateJob` |

**Asset Groups** (`asset_groups` / `asset_group_items`) sit on top of the Assets layer as an organizational structure, not a separate memory type — see **Asset Groups & Bundled Renewals** above.

Memory is global per tenant (shared across all AVA deployments for that tenant). Per-deployment memory override is not currently supported.

Memory can be loaded via manual entry (UI), CSV upload, or bulk import from template. `memoryRequirements()` declares which fields the platform checks for memory health scoring and enrichment nudges.

---

## The Renewal Register

Every transaction AVA processes creates a row in `renewal_register`. This is AVA's primary structured output — the audit trail of every renewal seen and actioned.

```
renewal_register
├── tx_id           — links to transactions table
├── user_id
├── category        — e.g. 'SSL Expiry', 'Domain Renewal'
├── asset           — the domain / cert / subscription being renewed (or group label for a bundle)
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
- Notify Customer (the client-facing closing message, `stage_key = notify_customer`)

Tenants can customize defaults or create their own. `SelectTemplateJob` scores each template against the classified category and priority, picks the best match, and passes it to `DraftEmailJob`. Reminder rounds 2 and 3 (15-day, 0-day) re-resolve the template independently rather than reusing round 1's frozen output — a tenant may want distinct wording per round.

**Placeholders:** `{{contact_first_name}}`, `{{contact_name}}`, `{{asset}}`, `{{client}}`, `{{due_date}}`, `{{sender_name}}`, `{{renewal_price}}`, `{{days_until_expiry}}`, and **`{{line_items}}`** — the bundled-asset itemization, safe to leave in any template unconditionally (renders empty for a normal single-asset transaction).

**Fallback:** If the matched template body is under 100 characters, or if `low_confidence = true`, AVA ignores the template body and prompts Claude to draft freely — with a low-confidence warning injected into the instruction, and a bundle-aware note when `line_items` is present so the prose acknowledges multiple services renewing together without itemizing them (the itemized list is appended separately either way).

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
- Enter fulfillment the same as a real transaction (so a tenant can preview the full lifecycle), but every fulfillment job individually guards against real vendor/tenant emails and real asset writes when `is_test = true`

---

## Deployment Configuration

Stored in `worker_deployments.config` JSON:

| Key | Default | Purpose |
|---|---|---|
| `capture_scope` | `'All incoming emails'` | What emails this deployment processes |
| `capture_keywords` | `''` | Comma-separated keywords to filter on (blank = all) |

`worker_deployments.send_mode` (`'draft'` default, or `'direct'`) and `worker_deployments.persona` (see Personas above) are separate columns, not part of this JSON blob.

---

## Instances

AVA allows multiple deployments per tenant, limited by connected Gmail inboxes — one deployment per Gmail account. A tenant with 3 Gmail accounts can run 3 AVA instances watching 3 separate inboxes.

---

## Pipeline Prompts

### Stage 1 — Read Email
**Uses AI:** Yes (Gmail webhook path only — Human Trigger / Asset Expiry Watch synthesize this stage's output directly, no AI call)
**Model:** deployment default
**Max tokens:** 512

**System:**
```
You are Ava, UNIT's Subscription & Renewal Coordinator. Return valid JSON only. No extra text.
```

**User template:**
```
Read the email below and explain what it means.

Return valid JSON only with:
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
**Uses AI:** Yes (Gmail webhook path only)
**Model:** deployment default
**Max tokens:** 256

**System:**
```
You are Ava, UNIT's Subscription & Renewal Coordinator. Return valid JSON only. No extra text.
```

**User template:**
```
Classify this transaction using the email understanding below.

Available categories: Domain Renewal, SSL Expiry, Hosting Invoice, SaaS Renewal,
Failed Payment, Security Alert, Meeting Request, Client Support, Other

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
**Uses AI:** Yes (Gmail webhook path — Human Trigger / Asset Expiry Watch resolve this from the asset record directly, `confidence: 100`, no AI call)
**Model:** deployment default
**Max tokens:** 768

**System:**
```
You are Ava, UNIT's Subscription & Renewal Coordinator. Return valid JSON only. No extra text.
```

**User template:**
```
Using the extracted email information and the memory tables below, find who owns
this asset and how it should be handled.

Return JSON:
{
  "asset": "",
  "matched_client": "",
  "primary_contact_name": "",
  "primary_contact_email": "",
  "related_project_or_service": "",
  "client_preference": "",
  "ava_rule": "",
  "matched_rule_id": "",
  "confidence": 0,
  "missing_information": []
}

EXTRACTED EMAIL CONTEXT:
{READ_OUTPUT}

MEMORY TABLES:
{MEMORY_TABLES}
```

**Confidence rule:** If `confidence < 70`, AVA sets `low_confidence_warning` and continues — does not abort. The warning is surfaced on the transaction detail page.

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
**Model:** deployment default
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
- Approval required: {APPROVAL_REQUIRED}
- Sign as: {SENDER_NAME}

Rules:
- Keep it concise
- Do not promise work is done
- Ask for approval when required
- Return only the email body
```

A bundle-aware note is appended to this prompt when `memory.line_items` is present, instructing Claude to acknowledge multiple services renewing together in prose without itemizing them individually — the itemized list is appended to the body separately.

Since 2.1, this job also resolves `approval_required` (template flag, matched rule, or low-confidence match — any one is enough) and decides what happens next: no approval needed at all → dispatch `PushToGmailJob` immediately (auto-send, continue into fulfillment); cadence already approved once (round 2/3) → dispatch `PushToGmailJob` without asking again; otherwise → set status `draft_ready` and halt at `human_decide` for a genuine first decision. Fast Track is exempt from the no-approval-needed bypass — it always demonstrates the full human-decide flow.

---

### Stage 8 — Push to Gmail
**Uses AI:** No
Creates a Gmail draft via the Gmail API (or surfaces it in-app if no inbox is connected). Runs *after* `human_decide` since 2.1 — dispatched by `DraftEmailJob` or `TransactionController::decide()` with explicit `autoSend`/`advanceAfter` flags, rather than deciding delivery/gating itself. Sends immediately if `autoSend` is true (no approval was needed, or `send_mode = direct`); otherwise the draft sits for the tenant to open and send themselves.

---

### Stage 9 — Request Invoice
**Uses AI:** Yes — but only on demand, not on stage entry
**Model:** deployment default
**Max tokens:** 512

`RequestInvoiceJob` itself never calls AI — this describes `InvoiceOcrService`'s on-demand extraction, which only runs once a tenant actually uploads a file via the attach-invoice action.

**System:**
```
You extract structured fields from invoice text. Return valid JSON only, no extra
text. Use null for any field you cannot find — never guess.
```

**User template:**
```
Extract these fields from the invoice text below:
{
  "amount": null,
  "currency": null,
  "issued_date": null,
  "due_date": null,
  "invoice_number": null
}

Dates in YYYY-MM-DD format. Amount as a plain number (no currency symbol).

INVOICE TEXT:
{INVOICE_TEXT}
```

---

### Stages 7, 10–16 — Human Decide, Request Documents, Confirm Payment, Update Renewal Date, Notify Stakeholders, Notify Customer, Archive Evidence, Schedule Next Watch
**Uses AI:** No

These are the remaining fulfillment stages — none call an LLM. `human_decide` and `confirm_payment` are hard human gates; `request_documents` is a skippable branch; the rest (`update_renewal_date`, `notify_stakeholders`, `notify_customer`, `archive_evidence`, `schedule_next_watch`) are fully automated once their preceding gate clears. See **Pipeline (17 Stages)** and **Gating Architecture** above for what each does and how it's individually toggleable.

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

Overrides are per-stage, per-type, per-deployment. They do not affect other tenants. The base contract prompt is always the fallback when an override is deleted. `UnitPlatform::getPromptOverride()` keys these by each stage's full `pipelineStages()` key (`draft_email`, not the short internal alias `draft`) — a mismatch here silently no-ops the override, which is why `AvaWorkerStageConsistencyTest` guards this specific failure mode.

---

## QA Checks

| Stage | Check | Field | Threshold | What it verifies |
|---|---|---|---|---|
| `read` | `OUTPUT_NOT_EMPTY` | — | — | Email was successfully parsed |
| `classify` | `FIELD_NOT_NULL` | `category` | — | Classification produced a result |
| `classify` | `VALUE_ABOVE` | `confidence` | 0.4 | Classification confidence is acceptable |
| `memory` | `OUTPUT_NOT_EMPTY` | — | — | Memory lookup completed |
| `template` | `FIELD_NOT_NULL` | `template_id` | — | Template was selected |
| `draft` | `FIELD_NOT_EMPTY` | `body` | — | A draft body was actually produced |
| `draft` | `VALID_EMAIL` | `to` | — | Valid recipient resolved |
| `push` | `STATUS_IN` | `status` | `draft_ready`, `sent` | Draft was pushed to Gmail |

**Gap worth noting:** none of the 9 fulfillment stages (`human_decide` through `schedule_next_watch`) have QA checks declared — `qaRequirements()` only covers the drafting chain. This is accurate to the current contract, not an oversight in this doc; whether fulfillment needs its own QA checks is a product decision, not a documentation one.

---

## Final Output

AVA's terminal deliverable has grown beyond the Gmail draft — what the tenant sees and acts on depends on how far the transaction has traveled:

**After drafting (stage 7):**

| Field | Type | Description |
|---|---|---|
| `gmail_draft_id` | string | Gmail API draft ID — used to send or delete |
| `to` | string | Recipient email resolved from memory lookup |
| `subject` | string | Subject line from template + placeholders |
| `body` | string | Email body — template-filled or Claude-generated, itemized if a bundle |
| `human_review_note` | string? | Internal caution note on transaction detail |
| `low_confidence` | boolean | True when memory match confidence < 70% |

**Destination:** Gmail Drafts + `renewal_register` table

**After full fulfillment (stage 16), the real terminal output is:**
- The asset's (or every bundled asset's) `renewal_date` advanced by its own `renewal_cadence_days`
- A signed PDF closeout archive (`ArchiveEvidenceJob`) covering every draft round, the human decision, invoice/documents, payment confirmation, and (for a bundle) the itemized breakdown — downloadable from the Transaction Center
- The tenant notified (`notify_stakeholders`, always) and optionally the client notified directly (`notify_customer`, opt-in)
- `asset_watch_log` cleared so the asset re-enters monitoring for its next cycle

**Human action:** Transactions page → Review & Decide
- **Approve & send** — records the decision as `approved`. Draft stays in Gmail Drafts (or sends immediately if `send_mode = direct`). Waits for the next reminder round if `client_cadence` is on and this isn't round 3.
- **Approve & proceed** — same as above, but skips the remaining reminder rounds and moves straight into fulfillment. For renewals already closed with the client outside AVA.
- **Reject** — records the decision as `rejected`. Draft is deleted from Gmail Drafts via the Gmail API so it cannot be sent accidentally.

Then, per fulfillment stage: attach an invoice (or don't — never blocks), attach documents (or skip), confirm payment (blocks until you do), and the rest closes out automatically.

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
| `AssetTransactionSynthesizer` | Builds a real transaction directly from asset record(s), no inbound email — used by Human Trigger and Asset Expiry Watch (see Ingest Paths) |
| `InvoiceOcrService` | Extracts structured fields (amount, currency, dates) from an uploaded invoice via Claude, on demand |

AI calls go through `App\Platform\Services\ClaudeService` — not AVA-specific. Platform-level prompt overrides (non-pipeline AI) go through `PlatformClaude`.

---

## Environment Variables

```
GMAIL_CLIENT_ID=
GMAIL_CLIENT_SECRET=
GMAIL_REDIRECT_URI="${APP_URL}/workers/ava/gmail/callback"
GMAIL_PUBSUB_TOPIC=projects/{project}/topics/ava-gmail-inbox
```
