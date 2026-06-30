# UNIT Worker Specification

## What a Worker Is

A worker is a deployable AI agent with a declared contract. It is not a script, not a chatbot, and not a configurable workflow. It is a **typed, self-describing pipeline** that declares — in code — everything the platform needs to run it: its identity, onboarding flow, credentials, pipeline stages, AI prompts, output shape, quality checks, and scheduled jobs.

The platform reads the contract at every step. It never hard-codes worker behavior.

Workers live in `app/Workers/{Slug}/` and implement `App\Platform\Contracts\WorkerContract`. Every method must be implemented. No exceptions.

---

## WorkerRegistry

`WorkerRegistry` resolves a slug to a `WorkerContract` instance. It merges two sources:

**`$map` (platform-built workers)** — Hard-coded in `WorkerRegistry::$map`. Always available, even if the DB is down. These are UNIT-owned workers shipped with the platform.

**`worker_registry` DB table (external workers)** — Rows with a `worker_class` column. Installed via the admin worker installer. No `$map` change needed when adding an external worker.

**Resolution order:** `$map` wins if a slug appears in both. External packages cannot override platform-built workers.

```php
WorkerRegistry::resolve('ava');        // always returns a contract (NullWorkerContract if unknown)
WorkerRegistry::resolveActive('ava');  // returns NullWorkerContract if worker is decommissioned
WorkerRegistry::all();                 // all resolvable workers, merged from $map + DB
WorkerRegistry::isNull($contract);     // true if the slug resolved to nothing
```

**Never use `resolve()` in ingest paths.** Use `resolveActive()` — it returns `NullWorkerContract` for dead workers so the pipeline safely no-ops instead of crashing.

### Worker Lifecycle States

Stored in `worker_registry.lifecycle_status`:

| State | Meaning |
|---|---|
| `active` | Live — accepting new deployments and ingest |
| `testing` | Admin/test transactions only — not billed, tagged `is_test=true` |
| `decommissioned` | No new deployments — ingest silently drops — data preserved |
| `removing` | `RemoveWorkerJob` running in background |
| `removed` | All tenant data soft-deleted — worker fully retired |

Platform-built workers in `$map` default to `active` unless a `worker_registry` row explicitly overrides it.

---

## Quick Reference — All 30 Contract Methods

| Block | Method | Returns | Nullable / Empty? |
|---|---|---|---|
| 1 | `identity()` | array | No |
| 1 | `employee()` | array | No |
| 1 | `org()` | array | No |
| 1 | `demoPayload()` | array | No |
| 2 | `platformRequirements()` | array | `[]` if none |
| 2 | `onboardingSteps()` | array | No |
| 2 | `instances()` | array | No |
| 2 | `credential()` | array | No |
| 2 | `deploymentFields()` | array | `[]` if none |
| 2 | `trainSchema()` | array | `[]` if stateless |
| 2 | `tags()` | array | `[]` if none |
| 2 | `media()` | array | No |
| 2 | `fastTrack()` | array | No |
| 2 | `fastTrackOutcome()` | array | No |
| 2 | `pipelineStages()` | array | No |
| 2 | `ingestJobClass()` | string | No |
| 3 | `input()` | array | No |
| 3 | `pipeline()` | array | No |
| 3 | `emit()` | array | `[]` if no events |
| 3 | `commit()` | ?array | `null` if no injection |
| 3 | `subscriptions()` | array | `[]` if emits only |
| 3 | `versionChangelog()` | array | `[]` for v1.0 |
| 3 | `notifications()` | array | `[]` if none |
| 3 | `dashboard()` | array | No |
| 4 | `qaRequirements()` | array | `[]` if none |
| 5 | `output()` | array | No |
| 6 | `prompts()` | array | No — list all stages |
| 7 | `owner()` | array | No |
| 8 | `scheduledJobs()` | array | `[]` if none |
| 8 | `fastTrackJobClass()` | string | `''` to use ingestJobClass |
| 8 | `stuckRecoveryMap()` | array | `[]` if no recovery |

---

## Block 1 — Identity

### `identity(): array`
Core identity. Required fields: `name`, `slug`, `version`, `description`.

- `slug` — unique lowercase identifier, matches the folder name under `app/Workers/`
- `version` — semver string compared against `worker_registry.version` on every load; mismatch alerts admin
- `description` — one sentence technical description (registry, schema export, admin tooling)

### `employee(): array`
The worker's human persona. Workers are built as tools but presented as employees — this profile is what the platform uses everywhere a human-facing voice is needed: marketplace cards, onboarding welcome screens, Command Center stats, and any first-person UI copy.

Fields:

| Field | Purpose |
|---|---|
| `name` | The worker's proper name — `AVA`, `NUX`, `DOX` |
| `pronoun` | `she` / `he` / `they` — used in "She says", "He reported" |
| `title` | Job title — `Renewal Coordinator`, `Multi-Channel Publishing Coordinator` |
| `department` | Org department — `Customer Success`, `Marketing`, `Operations` |
| `employer` | Who this worker is built for — target customer profile |
| `mission` | One declarative sentence, no "I" — the worker's north star |
| `introduction` | First-person paragraph starting with "Hi, I'm {name}." — shown on welcome screens and public profile |
| `what_i_do` | Array of first-person capability strings — the feature checklist on marketplace cards and onboarding |
| `activity_labels` | Human-readable labels for the Command Center stats card: `watching`, `working_on`, `waiting_label`, `memory_label` |

**Key principle:** The platform never says "the worker processed 126 emails." It says what `activity_labels` and `what_i_do` instruct. The worker authors its own voice.

### `org(): array`
The organization or platform this worker is built on. Drives marketplace grouping.

Fields: `name`, `abbreviation` (null for commercial platforms), `type`, `website`, `logo`.

Types: `platform` | `government` | `crm` | `erp` | `custom`

### `demoPayload(): array`
Synthetic input payload for the public-facing demo on the worker's marketplace page. No credentials or auth required. Source is always `public_demo`. Contains no real tenant data. Submissions register in the leads table.

---

## Block 2 — Deployment DNA

### `platformRequirements(): array`
Platform-level verification types required before this worker's onboarding can proceed. Checked against `platform_verifications`.

Example: `['email']` — tenant must verify their email before the wizard starts.

### `onboardingSteps(): array`
Worker-specific onboarding steps shown in the deployment wizard, after any platform-level steps from `platformRequirements()` are injected.

Each entry: `name`, `label`, `description`, `optional`, `icon`.

Icon values: `mail` | `brain` | `bolt` | `upload` | `check`

### `instances(): array`
How many instances of this worker a single tenant may deploy.

Fields: `multiple` (bool), `min`, `max` (null = unlimited), `label` (what each instance represents, e.g. `inbox`), `rationale`.

### `credential(): array`
An **array** of credential slots this worker requires. Most workers need one; a worker that reads Gmail AND calls an external API declares both.

Each entry:
- `key` — unique slug for this slot, e.g. `inbox`, `dob_api`
- `type` — `gmail_oauth` | `api_key` | `oauth2` | `webhook` | `none`
- `label` — UI label shown in the connect step
- `hint` — helper text beneath the selector
- `required` — whether the worker cannot function without this credential
- `multiple` — whether one deployment can connect multiple of this type
- `connect_route` — named route that starts the connect flow
- `authorize_route` — named route that triggers OAuth/API key entry

> Note: `credential()` returns an **array of credential objects**, not a single object. Workers still on the old single-object signature are wrapped automatically by `WorkerRegistry`.

### `deploymentFields(): array`
Worker-specific config fields collected at deploy time. Stored in `worker_deployments.config` JSON. Return `[]` if no config is needed.

Each entry: `key`, `label`, `type`, `placeholder`, `default`, `hint`, `options` (required when `type = select`).

Types: `text` | `select` | `toggle` | `textarea`

### `trainSchema(): array`
Memory layers the worker expects to be trained on. Shown in the Train step of the wizard. Return `[]` for a stateless worker with no memory.

Each entry: `key`, `label`, `description`, `required`, `format_hint`.

### `tags(): array`
Short lowercase keywords for marketplace search and the deploy wizard's worker picker. Include use-case terms, agency names, platform names, and synonyms.

### `media(): array`
Static asset paths from `public/workers/{slug}/`:

Fields: `avatar`, `banner`, `color` (hex accent), `quote` (first-person voice shown on profile card).

### `fastTrack(): array`
Synthetic input payload for the tenant's authenticated Fast Track test. Fired from the Worker Detail page after deployment. Tenant confirms name and email before it fires. Source is always `fast_track_test`.

### `fastTrackOutcome(): array`
Human-language summary shown after Fast Track succeeds.

Fields: `headline` (punchy one-liner), `what_happened` (array of `{ icon, text }`), `where_to_find` (`{ label, hint }`), `going_forward` (one sentence).

### `pipelineStages(): array`
Ordered stage definitions for the Fast Track visual and the live pipeline modal on the dashboard. Single source of truth for what the pipeline looks like in the UI.

Each entry: `key`, `label`, `sub` (one-line description), `icon`, `job_class`.

Icons: `check` | `mail` | `tag` | `brain` | `log` | `template` | `draft` | `send`

### `ingestJobClass(): string`
**Fully-qualified class name of the job the platform dispatches when a new transaction arrives.** This is the entry point for every pipeline run.

```php
public function ingestJobClass(): string
{
    return \App\Workers\AVA\Jobs\ReadEmailJob::class;
}
```

The platform dispatches this job onto the queue named `{worker_slug}-{deployment_id}`. The worker owns all logic after that.

---

## Block 3 — Pipeline

### `input(): array`
The raw input shape entering the pipeline at stage 1.

Fields: `description`, `source` (where the input comes from), `fields` (array of `{ key, type, description, required }`).

### `pipeline(): array`
Ordered typed stage nodes forming the pipeline graph. Each stage declares what it accepts, produces, and connects to.

Each entry:
- `stage` — stage number (1-based)
- `total` — total stages in this pipeline
- `job` — fully-qualified job class
- `label` — human-readable name
- `receives_from` — `'input'` for stage 1, or the label of the preceding stage
- `accepts` — fields read from prior stage output: `[{ key, type, description }]`
- `produces` — fields written to this stage's output: `[{ key, type, description }]`
- `connects_to` — job class of the next stage for linear pipelines, or `null` if terminal or branching
- `can_emit` — event name strings this stage may fire (full shape declared in `emit()`)
- `branches` — conditional routing rules (see Branches below); when present, `connects_to` must be `null`

**Branches within a stage:**

Each branch entry:
- `condition` — human-readable expression evaluated against stage output, e.g. `"category == 'not_relevant'"`
- `next_job` — fully-qualified job class to dispatch when condition matches
- `next_stage` — stage key for UI rendering and logging
- `is_default` — `true` on exactly one branch per stage (the catch-all when no other condition matches)
- `description` — when this branch fires

Branching logic lives in the job class. `branches()` in the contract makes it auditable and visible in the Schema tab.

### `emit(): array`
Canonical list of all events this worker can fire, across all stages. Pipeline stages reference these by event name only via `can_emit`.

Each entry: `event` (dot-notation, e.g. `renewal.draft_ready`), `fired_from`, `description`, `reusable` (true = other workers can subscribe), `fields` (full payload shape).

### `commit(): ?array`
Injection point for human or worker-to-worker context mid-pipeline. Return `null` if this worker needs no external injection.

When not null: `description`, `source` (`human` | `worker` | `both`), `injected_by`, `fields`.

### `subscriptions(): array`
**Events from other workers this worker listens to.** The platform event router delivers matching events to the declared handler stage. Return `[]` if this worker only emits.

> This is worker-to-worker event subscriptions — not billing plan subscriptions. Pricing tiers are stored in `worker_pricing` and managed separately.

Each entry: `event`, `from_worker` (slug), `description`, `handler_stage` (pipeline stage key that processes the event), `required`.

### `versionChangelog(): array`
Ordered version history with upgrade instructions for tenants on older deployments. The platform compares this against `deployment.worker_version` and surfaces breaking changes between the tenant's deployed version and current.

Return `[]` for v1.0 (no prior versions to upgrade from).

Each entry:
- `version` — semver string
- `date` — ISO date
- `notes` — plain-English summary
- `breaking` — bool
- `breaking_reason` — what specifically changed
- `upgrade_steps` — ordered array of strings (what the tenant must do to upgrade)

### `notifications(): array`
Worker-specific notification conditions evaluated per deployment on the Command Center. Platform-tier checks (Gmail watch inactive, trial exhausted, failed jobs) always run separately — do not redeclare them here.

Each entry: `key`, `level`, `query`, `trigger` (`{ operator, value }`), `message`, `action_label`, `action_route`, `action_params`, `threshold_minutes` (only for `tx_stuck` query).

Available query keys: `tx_draft_ready_undecided`, `tx_urgent_open`, `tx_failed_today`, `tx_stuck`

Levels: `error` | `warning` | `info`

### `dashboard(): array`
Visual identity and worker-specific stats for this worker's card on the Command Center.

Fields: `accent` (Tailwind color key), `icon` (SVG `<path>` d= string), `stats` (up to 3 pills: `{ key, label, query }`).

Available stat queries: `tx_draft_ready`, `tx_urgent`, `tx_today`, `tx_total`, `tx_failed`, `tx_approved`

---

## Block 4 — Quality

### `qaRequirements(): array`
Per-stage pass conditions the QA evaluator runs against completed transactions. Only `QACheck` constants are valid — no freeform strings.

Each entry: `stage`, `check` (QACheck constant), `label`.

Additional keys per check type:
- `field` — required for `FIELD_NOT_NULL`, `FIELD_NOT_EMPTY`, `VALUE_ABOVE`, `VALID_EMAIL`, `STATUS_IN`
- `threshold` — required for `VALUE_ABOVE` (float 0–1)
- `values` — required for `STATUS_IN` (array of allowed strings)

Available constants: `FIELD_NOT_NULL`, `FIELD_NOT_EMPTY`, `VALUE_ABOVE`, `VALID_EMAIL`, `STATUS_IN`

---

## Block 5 — Output

### `output(): array`
The definitive final artefact this worker produces. Not the same as a stage's `produces` (internal plumbing). `output()` answers: *"What did this worker actually deliver?"*

Fields: `description`, `destination` (where it lands), `format`, `fields`, `human_action` (what a human does with this output, or null if automatic), `auto_action` (what happens automatically when no human action required, or null if always manual).

Formats: `email_draft` | `document` | `record` | `notification` | `api_call`

> The interface comment notes that `output()` is conceptually the terminal stage's `produces` — one source of truth. In practice both exist: `output()` is the human-readable output contract; `produces` on the terminal stage is the technical field map. Keep them in sync.

---

## Block 6 — Prompts

### `prompts(): array`
The AI prompts for every pipeline stage. Non-AI stages must still be listed with `uses_ai: false`. Every stage in `pipeline()` must have a corresponding entry here.

Each entry:
- `stage` — stage key matching `pipelineStages()`
- `label` — human-readable stage name
- `uses_ai` — bool
- `model` — model override for this stage, or null for deployment default
- `system` — system prompt (role declaration) sent to the LLM
- `user` — user prompt template with `{PLACEHOLDER}` tokens for dynamic values
- `output_format` — `'json'` or `'text'`
- `output_shape` — JSON key array when json; description string when text
- `max_tokens` — approximate token budget

**Version rule:** Any change to `system`, `user`, or `output_shape` must increment `version` in `identity()`. This flags deployed instances as running an outdated contract.

**Non-AI stage declaration:**
```php
['stage' => 'log', 'label' => 'Log Entry', 'uses_ai' => false,
 'model' => null, 'system' => null, 'user' => null,
 'output_format' => null, 'output_shape' => null, 'max_tokens' => null]
```

### Per-Deployment Prompt Overrides

Tenants on eligible plans can override the system or user prompt for any AI stage. Overrides are stored in `deployment_prompt_overrides` and scoped per-deployment — they do not affect other tenants.

```php
$override = DB::table('deployment_prompt_overrides')
    ->where('deployment_id', $this->deploymentId)
    ->where('stage_key', 'classify')
    ->where('prompt_type', 'system')
    ->value('value');

$systemPrompt = $override ?? $this->worker->prompts()['classify']['system'];
```

Overrides do not increment the worker version.

---

## Block 7 — Owner

### `owner(): array`
The entity responsible for this worker. Every worker must declare a signed owner.

Fields: `type`, `name`, `contact`, `website`, `license`, `sla`, `since`, `verified`.

Types:
- `platform` — UNIT-built. `contact: hello@unit.report`, `verified: true`
- `partner` — Third-party approved builder. `verified: false` until reviewed by UNIT
- `custom` — Tenant-built, private. `verified: false`

License values: `proprietary` | `mit` | `apache2` | `gpl` | `commercial`

---

## Block 8 — Platform Integration

### `scheduledJobs(): array`
Jobs the platform scheduler runs for this worker on a recurring basis. The platform iterates all registered workers and fires these automatically — no changes to `app/Console/Kernel.php` needed.

Return `[]` if this worker has no scheduled jobs.

Each entry:
- `job` — fully-qualified job class
- `cron` — cron expression, e.g. `'0 17 * * *'`
- `queue` — queue name to dispatch on
- `per_deployment` — `true` = dispatched once per active deployment (passes `deployment_id`); `false` = dispatched once globally
- `name` — unique schedule name (used by Laravel's named schedule dedup)

### `fastTrackJobClass(): string`
The job to dispatch for a Fast Track test run. May differ from `ingestJobClass()` when the worker needs to set up a synthetic payload (e.g. inject a test item) before the pipeline starts.

Return `''` (empty string) to use `ingestJobClass()` directly for Fast Track.

### `stuckRecoveryMap(): array`
Maps mid-flight transaction status strings to the job class that re-dispatches them. Used by the platform's stuck-transaction recovery mechanism.

Return `[]` to opt out — stuck transactions will be flagged but not auto-recovered.

Example:
```php
public function stuckRecoveryMap(): array
{
    return [
        'received'    => \App\Workers\AVA\Jobs\ReadEmailJob::class,
        'classifying' => \App\Workers\AVA\Jobs\ClassifyEmailJob::class,
    ];
}
```

---

## The NULL Contract Rule

Every method must be implemented. Returning `null` or `[]` is valid for optional methods. Omitting any method causes a PHP fatal on contract resolution.

**These cannot return null or empty:**

| Method | Why |
|---|---|
| `identity()` | Every worker must have an identity |
| `output()` | Every worker produces something |
| `prompts()` | Every stage must be listed, even non-AI stages |
| `ingestJobClass()` | Platform cannot dispatch without this |
| `owner()` | Every worker must be signed |

**Valid null/empty declarations:**

```php
public function commit(): ?array          { return null; }  // no mid-pipeline injection
public function deploymentFields(): array { return []; }    // no config fields at deploy
public function trainSchema(): array      { return []; }    // stateless worker
public function emit(): array             { return []; }    // fires no events
public function subscriptions(): array    { return []; }    // subscribes to no events
public function scheduledJobs(): array    { return []; }    // no recurring jobs
public function stuckRecoveryMap(): array { return []; }    // no auto-recovery
public function fastTrackJobClass(): string { return ''; }  // use ingestJobClass for fast track
```

---

## Worker Versioning

The `version` in `identity()` is compared against `worker_registry.version` on every deployment load. A mismatch surfaces a notice to admin — no auto-migration.

**When to bump the version:**

| Change | Bump required? |
|---|---|
| System or user prompt change | Yes |
| `output_shape` change | Yes |
| New pipeline stage added | Yes |
| Stage removed or reordered | Yes |
| Branch logic added or changed | Yes |
| `output()` field shape changed | Yes |
| New subscription plan added | No |
| Media or copy change | No |
| Bug fix with no behavior change | No |

Use `versionChangelog()` to document breaking changes with upgrade instructions. Tenants on older deployments see these surfaced in the platform.

---

## Worker File Structure

```
app/Workers/{Slug}/
├── {Slug}Worker.php          ← implements WorkerContract
├── Jobs/
│   ├── IngestJob.php         ← ingestJobClass() points here
│   ├── Stage2Job.php
│   └── ...
├── Services/
│   └── (worker-specific services)
└── Memory/
    └── (worker-specific memory helpers)

public/workers/{slug}/
├── avatar.png
└── banner.jpg
```

---

## Building a New Worker

> **Important:** Workers are built independently and are never scaffolded without explicit agreement. Do not create worker code or files speculatively.

### Manual process

1. Create `app/Workers/{Slug}/`
2. Implement all 30 contract methods in `{Slug}Worker.php`
3. Write job classes for each pipeline stage in `Jobs/`
4. Add media assets to `public/workers/{slug}/`
5. Add to `WorkerRegistry::$map` if platform-built, or insert a `worker_registry` DB row if external
6. Add pricing in `worker_pricing`: `worker_slug`, `monthly_flat_rate`, `per_tx_rate`, `free_transactions`
7. Set `lifecycle_status = 'testing'` initially — test with admin accounts before going `active`
8. Write `{Slug}.md` documenting this worker's specific implementation

### Via Worker Builder

The admin Worker Builder at `/admin/workers/new` scaffolds the contract class, registers the DB row, and generates starter job stubs. After scaffolding:

1. Fill in all contract methods in the generated `{Slug}Worker.php`
2. Write the job classes in `Jobs/`
3. Add media assets
4. Write `versionChangelog()` — `[]` for v1.0
5. Set `lifecycle_status = 'testing'`, validate with Fast Track
6. Flip to `active` when ready to publish

---

## Worker-to-Worker Communication

Workers pass context to each other via the event bus:

- Upstream worker declares an event in `emit()` with `reusable: true`
- Downstream worker declares it in `subscriptions()` with the handler stage
- The platform event router delivers the emitted payload to the handler stage as its input

This is the foundation of org-specific worker suites — e.g. a NYCSCA inspection worker that enriches output from a NYCSCA permit worker. Workers within the same org share a common memory namespace and can pass context through `commit()` as well.

---

## Testing a Worker

### Fast Track (Authenticated)
Fires a synthetic payload from `fastTrack()` through the full pipeline using real credentials and real memory. Dispatched via `fastTrackJobClass()` (or `ingestJobClass()` if that returns `''`). Accessed from the Worker Detail page. Result is a real transaction tagged `fast_track_test`. Can be permanently deleted after review.

### Prompt Testing (Admin)
Individual AI stage prompts can be tested in isolation from the Worker Builder at `/admin/workers/{slug}/edit`. Sends a custom payload to a single stage without running the full pipeline. No transaction created. Use for iterating on prompt wording before bumping the version.

### Public Demo
`demoPayload()` powers the public-facing demo on the worker's marketplace page. No auth required. Source is `public_demo`. Does not write to the transaction log. Prospect submissions register in the leads table.

### Lifecycle: Testing Mode
Set `worker_registry.lifecycle_status = 'testing'` before going live. In testing mode: only admin and accounts with `testing_access` can deploy; transactions are tagged `is_test = true`; billing is not applied. Flip to `active` when the worker is ready for tenants.
