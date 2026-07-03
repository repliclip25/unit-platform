<?php

namespace App\Platform\Contracts;

/**
 * WorkerContract — the full declaration of a worker's identity, deployment DNA,
 * pipeline shape, and quality requirements.
 *
 * The platform reads this contract to:
 *   - Render the marketplace listing and worker public page
 *   - Drive the deployment wizard (Deploy → Connect → Train → Fast Track)
 *   - Visualise the pipeline on the Schema tab
 *   - Route and evaluate emitted events
 *   - Run QA checks against completed transactions
 *
 * Every worker blueprint must implement this interface.
 * Workers live in App\Workers\{Slug}\ and register in WorkerRegistry.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Six platform primitives — every worker declares all six:
 *
 *   input()         What enters the pipeline at the start
 *   pipeline()      Typed stage nodes — each stage declares what it accepts,
 *                   produces, connects to (or branches to), and which events it
 *                   can emit. Stages may declare conditional branches instead of
 *                   a single connects_to — the dispatcher follows the first
 *                   matching branch condition, or the default branch.
 *   emit()          Canonical list of all events this worker fires, with full
 *                   payload shape. Stages reference these by event name only.
 *   subscriptions() Events from other workers this worker listens to. The
 *                   platform event router delivers matching events to the
 *                   declared handler stage. Return [] if this worker emits only.
 *   commit()        What a human or another worker can inject mid-pipeline.
 *                   Returns null if the worker needs no external injection.
 *   credentials()   Array of credential requirements. Each entry is a named
 *                   credential slot with its own type, label, and connect flow.
 *                   Replaces the old single-object credential() — workers that
 *                   need both Gmail OAuth AND an API key declare both here.
 *
 * output() is NOT a separate method — it is the `produces` array of the
 * terminal pipeline stage. One source of truth.
 *
 * ── Version changelog ────────────────────────────────────────────────────────
 * versionChangelog() returns an ordered array of version entries so the platform
 * can surface upgrade instructions to tenants on older deployments:
 *   [{version, date, notes, breaking, breaking_reason, upgrade_steps[]}]
 * ─────────────────────────────────────────────────────────────────────────────
 */
interface WorkerContract
{
    // ── Block 1: Identity ────────────────────────────────────────────────────

    /**
     * Core identity of the worker.
     *
     * Returns:
     *   name        Human-readable worker name
     *   slug        Unique lowercase identifier — matches workers.slug and namespace folder
     *   version     Semver string — used to detect contract changes on deployed instances
     *   description One-sentence description shown on the marketplace card
     */
    public function identity(): array;

    /**
     * The worker's employee persona — how they present themselves as a human-role agent.
     *
     * Workers are the product. In production they are employees, not tools.
     * This profile drives the marketplace card, onboarding welcome, Command Center
     * stats, and any first-person UI copy. The platform never says "the worker processed
     * X emails" — it says what the employee profile instructs.
     *
     * Returns:
     *   name             The worker's proper name  e.g. 'AVA', 'NUX', 'DOX'
     *   pronoun          'she' | 'he' | 'they'  — used in "She says", "He reported"
     *   title            Job title  e.g. 'Renewal Coordinator', 'Multi-Channel Publishing Coordinator'
     *   department       Org department  e.g. 'Customer Success', 'Marketing', 'Operations'
     *   employer         Who this worker is built for  e.g. 'Freelancers, Solo Founders, Startup CEOs'
     *   mission          One declarative sentence — the worker's north star, no "I"
     *                    e.g. 'Never let a renewal request go unanswered.'
     *   introduction     First-person paragraph shown on welcome screen and public profile.
     *                    Starts with "Hi, I'm {name}." Explains what they do and how in plain English.
     *   what_i_do        Array of first-person capability strings shown as a feature checklist
     *                    e.g. ['Monitor your Gmail 24/7', 'Detect renewal requests']
     *   activity_labels  Human-readable labels for the Command Center stats card:
     *                      watching      — what the worker is monitoring
     *                      working_on    — the noun for in-progress work  e.g. 'renewal responses'
     *                      waiting_label — what's waiting for the tenant  e.g. 'drafts to review'
     *                      memory_label  — short description of what memory contains
     */
    public function employee(): array;

    /**
     * The organisation or platform this worker is built on.
     * Drives marketplace grouping and filtering.
     *
     * Returns:
     *   name          Full name  e.g. 'Gmail', 'NYC Department of Buildings', 'Salesforce'
     *   abbreviation  Short code e.g. null, 'DOB', 'SF'  (null for commercial platforms)
     *   type          'platform' | 'government' | 'crm' | 'erp' | 'custom'
     *   website       Primary URL of the org/platform
     *   logo          Icon key for UI rendering (maps to asset or icon set)
     */
    public function org(): array;

    /**
     * Public-facing demo payload.
     * Used on the worker's public marketplace page — no credentials needed.
     * Outsiders and prospective tenants submit name + email to fire this
     * and receive the sample output. Submissions are registered in the leads table.
     *
     * This is the shape of what gets injected as raw_input with source = 'public_demo'.
     * It should be representative but contain no real tenant data.
     */
    public function demoPayload(): array;

    // ── Block 2: Onboarding Requirements ────────────────────────────────────

    /**
     * Platform-level verification types this worker requires before its onboarding
     * can proceed. The system checks each against platform_verifications and injects
     * the appropriate step if not yet complete.
     *
     * e.g. ['email'] for AVA, ['email', 'kyc'] for a financial worker
     */
    public function platformRequirements(): array;

    /**
     * Worker-specific onboarding steps (platform steps are injected automatically
     * before these based on platformRequirements()).
     *
     * Each entry:
     *   name        Step identifier used in route + view name  e.g. 'credential'
     *   label       User-facing action label  e.g. 'Connect your Gmail'
     *   description One sentence explaining what this step does and why
     *   optional    Whether this step can be skipped
     *   icon        Icon hint for UI: 'mail' | 'brain' | 'bolt' | 'upload' | 'check'
     */
    public function onboardingSteps(): array;

    // ── Block 2: Deployment DNA ──────────────────────────────────────────────

    /**
     * How many instances of this worker a single tenant may deploy.
     *
     * Returns:
     *   multiple   Whether more than one deployment is allowed per tenant
     *   min        Minimum deployments to be operational (usually 1)
     *   max        Maximum deployments allowed — null means unlimited
     *   label      What each instance represents  e.g. 'inbox', 'project', 'account'
     *   rationale  One sentence explaining why this limit exists
     */
    public function instances(): array;

    /**
     * Credential slots this worker requires to operate.
     * Returns an ARRAY of credential requirements — one entry per distinct
     * credential type. Most workers need one slot; a worker that reads Gmail
     * AND calls a government API declares both here.
     *
     * Each entry:
     *   key             Unique slug for this credential slot  e.g. 'inbox', 'dob_api'
     *   type            'gmail_oauth' | 'api_key' | 'oauth2' | 'webhook' | 'none'
     *   label           UI label shown in the connect step  e.g. 'Gmail Account'
     *   hint            Helper text shown beneath the credential selector
     *   required        Whether the worker cannot function without this credential
     *   multiple        Whether one deployment can connect multiple of this type (multi-inbox)
     *   connect_route   Named route that starts the credential connect flow
     *   authorize_route Named route that triggers the OAuth/API-key entry
     *
     * Backwards compatibility: if a worker still implements the old single-object
     * credential() signature, WorkerRegistry wraps it in an array automatically.
     */
    public function credential(): array;

    /**
     * Worker-specific configuration fields collected at deploy time.
     * Rendered in the Deploy step of the wizard after the shared fields
     * (deployment name, credential).
     *
     * Each entry:
     *   key          Config key stored in worker_deployments.config JSON
     *   label        UI label
     *   type         'text' | 'select' | 'toggle' | 'textarea'
     *   placeholder  Input placeholder
     *   default      Default value
     *   hint         Optional helper text
     *   options      Required when type = 'select' — array of {value, label}
     */
    public function deploymentFields(): array;

    /**
     * The memory layers this worker expects to be trained on.
     * Rendered in the Train step of the deployment wizard.
     *
     * Each entry:
     *   key          Memory key (matches table name or alias in loadMemoryFromBlueprint)
     *   label        UI label shown in the train step
     *   description  What this memory layer is used for
     *   required     Whether the worker cannot operate usefully without this layer
     *   format_hint  How to provide this data  e.g. 'CSV upload', 'Manual entry', 'Imported from CRM'
     */
    public function trainSchema(): array;

    /**
     * Search tags — short lowercase keywords used for marketplace search and
     * onboarding worker picker filtering. Include use-case terms, agency names,
     * platform names, and common synonyms.
     *
     * e.g. ['renewal', 'gmail', 'email', 'subscription', 'domain', 'invoice']
     */
    public function tags(): array;

    /**
     * Static media assets for this worker.
     * Files live in public/workers/{slug}/ — no DB storage.
     *
     * Returns:
     *   avatar   Path to square avatar image (used in worker cards, OG image)
     *   banner   Path to wide banner image 1200×630 (OG image when worker shared)
     *   color    Brand accent hex — used for banner overlay and card accents
     *   quote    First-person voice quote shown on the worker profile card
     */
    public function media(): array;

    /**
     * Synthetic input payload for the authenticated tenant fast track test.
     * Fired from the worker detail page after deployment. Tenant must confirm
     * name and email (pre-filled from their account) before it fires.
     * Source is always 'fast_track_test'.
     */
    public function fastTrack(): array;

    /**
     * Human-language summary shown after the fast track pipeline succeeds.
     * Explains what was accomplished in outcome terms, not technical stage terms.
     *
     * Returns:
     *   headline       One punchy sentence — the "aha" moment. e.g. "That was your first deal, handled."
     *   what_happened  Array of { icon, text } — what the worker actually did, in plain English
     *   where_to_find  { label, hint } — where in the workspace to see the output
     *   going_forward  One sentence on what happens automatically from here
     */
    public function fastTrackOutcome(): array;

    /**
     * Ordered pipeline stage definitions used to render the Fast Track visual.
     * This is the single source of truth for what the pipeline looks like — both
     * during onboarding fast track and the dashboard live pipeline modal.
     *
     * Each entry:
     *   key        Unique stage key — must match QAController's status derivation logic
     *   label      Short display name   e.g. 'Read Email'
     *   sub        One-line description e.g. 'Parse & extract fields'
     *   icon       'check' | 'mail' | 'tag' | 'brain' | 'log' | 'template' | 'draft' | 'send'
     *   job_class  Short class name for fail detection (null for synthetic stages like 'webhook')
     */
    public function pipelineStages(): array;

    /**
     * The fully-qualified class name of the job that receives a raw inbound
     * payload and kicks off the pipeline. The platform dispatches this job
     * when a new transaction arrives — the worker owns all logic after that.
     *
     * Example: return \App\Workers\AVA\Jobs\FilterEmailJob::class;
     */
    public function ingestJobClass(): string;

    // ── Block 3: Pipeline ────────────────────────────────────────────────────

    /**
     * What enters the pipeline at the very start — the raw input shape.
     *
     * Returns:
     *   description  What the input represents
     *   source       Where it comes from  e.g. 'Gmail Pub/Sub webhook'
     *   fields       Array of { key, type, description, required }
     */
    public function input(): array;

    /**
     * Ordered typed stage nodes forming the pipeline.
     * Each stage is a node in the pipeline graph.
     *
     * Each entry:
     *   stage         Stage number (1-based)
     *   total         Total stages in this worker's pipeline
     *   job           Fully-qualified job class name
     *   label         Human-readable stage name  e.g. 'Read Email'
     *   receives_from 'input' for stage 1, or the label of the preceding stage
     *   accepts       Fields this stage reads from the previous stage output or raw input
     *                 Each: { key, type, description }
     *   produces      Fields this stage writes to its output
     *                 Each: { key, type, description }
     *   connects_to   Job class of the next stage for LINEAR pipelines, or null if terminal.
     *                 Must be null when branches is set — the dispatcher follows branches instead.
     *   can_emit      Array of event name strings this stage may fire
     *                 Full payload shape lives in emit(), not here
     *   branches      Optional. Array of conditional routing rules evaluated IN ORDER after
     *                 this stage completes. The dispatcher dispatches the first matching branch.
     *                 When present, connects_to must be null.
     *                 Each branch entry:
     *                   condition     Human-readable expression evaluated against stage output
     *                                 e.g. "category == 'permit_approved'"
     *                                 e.g. "confidence < 0.5"
     *                   next_job      Fully-qualified job class to dispatch when condition matches
     *                   next_stage    Stage key for UI rendering and logging
     *                   is_default    true on exactly one branch — the catch-all when no
     *                                 other condition matches. Must have exactly one default.
     *                   description   One sentence explaining when this branch fires
     */
    public function pipeline(): array;

    /**
     * Canonical list of all events this worker can emit, across all stages.
     * Pipeline stages reference these by event name only via can_emit[].
     *
     * Each entry:
     *   event        Dot-notation event name  e.g. 'renewal.draft_ready'
     *   fired_from   Stage label that fires this event
     *   description  What the event represents
     *   reusable     true = other workers can subscribe; false = internal only
     *   fields       Full payload shape: array of { key, type, description }
     */
    public function emit(): array;

    /**
     * Injection point for human or worker-to-worker context mid-pipeline.
     * Return null if this worker needs no external injection.
     *
     * When not null, returns:
     *   description  What gets injected and why
     *   source       'human' | 'worker' | 'both'
     *   injected_by  Worker slug or 'tenant' for human injections
     *   fields       Shape of what gets committed: array of { key, type, description, required }
     */
    public function commit(): ?array;

    /**
     * Events from other workers this worker subscribes to.
     * The platform event router delivers matching emitted events to the declared
     * handler stage, which receives the event payload as its input.
     *
     * Return [] if this worker only emits and does not subscribe to anything.
     *
     * Each entry:
     *   event          Dot-notation event name  e.g. 'renewal.draft_ready'
     *   from_worker    Slug of the worker that emits this event  e.g. 'ava'
     *   description    What this worker does when it receives the event
     *   handler_stage  Pipeline stage key that processes the incoming event payload
     *   required       Whether the worker needs this event to function (vs. optional enrichment)
     */
    public function subscriptions(): array;

    /**
     * Ordered version history with upgrade instructions for tenants on older deployments.
     * The platform compares this list against deployment.worker_version and surfaces
     * any breaking changes between the tenant's deployed version and the current one.
     *
     * Return [] for v1.0 (no prior versions to upgrade from).
     *
     * Each entry:
     *   version          Semver string  e.g. '2.0'
     *   date             ISO date string  e.g. '2026-06-23'
     *   notes            Plain-English summary of what changed
     *   breaking         true if existing deployments need action to stay compatible
     *   breaking_reason  Why it breaks — what specifically changed
     *   upgrade_steps    Ordered array of strings — what the tenant must do to upgrade
     *                    e.g. ['Re-run the memory upload step', 'Re-deploy the worker']
     */
    public function versionChangelog(): array;

    // ── Block 3b: Notifications ─────────────────────────────────────────────

    /**
     * Worker-specific notification conditions surfaced on the Command Center.
     *
     * The platform evaluates each entry per deployment and fires the notification
     * when the query result satisfies the trigger. Platform-tier checks (failed
     * jobs, stuck pipeline, watch inactive, trial exhausted) always run separately
     * and do not need to be declared here.
     *
     * Registered query keys:
     *   'tx_draft_ready_undecided'   draft_ready with no human_decision
     *   'tx_urgent_open'             High/Critical priority, not approved/sent/failed
     *   'tx_failed_today'            failed transactions created today
     *   'tx_stuck'                   in pipeline status > N minutes (uses threshold_minutes)
     *
     * Each entry:
     *   key              Unique identifier for this notification rule
     *   level            'error' | 'warning' | 'info'
     *   query            Registered query key (see above)
     *   trigger          { operator: '>'|'>='|'=='|'<', value: int }
     *   message          Template string — {count} replaced with live value,
     *                    {plural} replaced with 's' when count != 1
     *   action_label     CTA button text  e.g. 'Review', 'View', 'Fix'
     *   action_route     Named Laravel route for the action link
     *   action_params    Optional query string params passed to the route  e.g. ['filter' => 'draft_ready']
     *   threshold_minutes Optional — only used with 'tx_stuck' query
     */
    public function notifications(): array;

    // ── Block 3c: Employer Overview ──────────────────────────────────────────

    /**
     * Declares the employer-facing overview dashboard for this worker.
     * A generic renderer loops these panels in priority order.
     * Tenants can reorder or hide panels via deployment config — no code change.
     *
     * Panel types (registered library — only use these):
     *   action_queue   Items needing a human decision right now
     *   horizon        Upcoming deadlines bucketed by time window
     *   metric_strip   3-4 KPI numbers at a glance
     *   proof_of_work  What the worker accomplished this period
     *   alert_feed     Issues needing awareness but not immediate action
     *   activity_feed  Human-readable chronological log
     *   insight        AI-generated natural language briefing (optional)
     *   status_map     Visual state breakdown (optional)
     *
     * Each panel entry:
     *   type      Panel type from the registered library above
     *   title     Section heading shown to the employer
     *   priority  Rendering order (1 = top)
     *   + any panel-type-specific keys (windows, metrics, period, limit, empty, etc.)
     *
     * Returns:
     *   panels    Ordered array of panel declarations
     */
    public function overview(): array;

    // ── Block 3d: Dashboard Surface ─────────────────────────────────────────

    /**
     * Declares the visual identity and worker-specific stats shown on this
     * worker's card in the Command Center.
     *
     * The platform top bar uses universal pipeline metrics (total processed,
     * in pipeline, needs review, failed) that apply to every worker type.
     * This method declares only what is unique to THIS worker's output domain.
     *
     * Returns:
     *   accent   Tailwind color key  e.g. 'violet', 'blue', 'emerald', 'amber', 'rose'
     *   icon     SVG <path> d= string (24×24 stroke outline) for the worker card icon
     *   stats    Up to 3 worker-specific stat pills shown on the card.
     *            Each: { key, label, query }
     *              key   — unique identifier
     *              label — display label shown under the number  e.g. 'Drafts Ready'
     *              query — one of the registered per-deployment stat queries:
     *                      'tx_draft_ready'  transactions with status = draft_ready
     *                      'tx_urgent'       High/Critical priority, not yet resolved
     *                      'tx_today'        transactions created today
     *                      'tx_total'        all transactions for this deployment
     *                      'tx_failed'       failed transactions for this deployment
     *                      'tx_approved'     approved/sent transactions
     */
    public function dashboard(): array;

    // ── Block 4: Quality ────────────────────────────────────────────────────

    /**
     * Per-stage pass conditions the QA evaluator runs against completed transactions.
     * Only QACheck constants are valid check types — no freeform strings.
     *
     * Each entry:
     *   stage      Stage name key  e.g. 'read', 'classify', 'push'
     *   check      QACheck constant
     *   label      Human-readable description of what this check verifies
     *
     * Additional keys per check type:
     *   field      Required for FIELD_NOT_NULL, FIELD_NOT_EMPTY, VALUE_ABOVE, VALID_EMAIL, STATUS_IN
     *   threshold  Required for VALUE_ABOVE  (float 0–1)
     *   values     Required for STATUS_IN    (array of allowed strings)
     */
    public function qaRequirements(): array;

    // ── Block 5: Output ──────────────────────────────────────────────────────

    /**
     * The definitive final output of this worker's pipeline.
     * This is the terminal artefact — what the worker actually produces
     * for the tenant after all stages complete. One source of truth.
     *
     * NOT the same as the terminal stage's `produces` array (which is
     * internal stage output). This is the human-readable output contract:
     * what the tenant sees, stores, or acts on.
     *
     * Returns:
     *   description  What this output represents in plain English
     *   destination  Where it lands  e.g. 'Gmail Drafts', 'renewal_register', 'Slack channel'
     *   format       'email_draft' | 'document' | 'record' | 'notification' | 'api_call'
     *   fields       Array of { key, type, description, nullable }
     *   human_action What a human does with this output  e.g. 'Review and approve/reject the draft'
     *   auto_action  What happens automatically when approval_required = false (null if always manual)
     */
    public function output(): array;

    // ── Block 6: Prompts ─────────────────────────────────────────────────────

    /**
     * The AI prompts used at each pipeline stage that calls an LLM.
     * Declares the system role and user prompt template for each AI stage.
     * Non-AI stages (log, push, template selection) return null for their entry.
     *
     * This is the intellectual core of the worker — the prompt engineering
     * that defines how it thinks. Declared here so the Schema tab can surface
     * it, and so prompt changes trigger a version bump.
     *
     * Each entry:
     *   stage        Stage key matching pipeline() entries  e.g. 'read', 'classify'
     *   label        Human-readable stage name
     *   uses_ai      Whether this stage calls an LLM (false = no prompt needed)
     *   model        Model override for this stage, or null to use deployment default
     *   system       The system prompt (role declaration) sent to the LLM
     *   user         The user prompt template — use {PLACEHOLDER} for dynamic values
     *   output_format 'json' | 'text' — what format the LLM is instructed to return
     *   output_shape  When output_format = 'json': the JSON keys the LLM must return
     *                 When output_format = 'text': a description of the expected text
     *   max_tokens   Approximate token budget for this stage's LLM call
     */
    public function prompts(): array;

    // ── Block 7: Owner ───────────────────────────────────────────────────────

    /**
     * The entity that built, maintains, and is responsible for this worker.
     * Every worker must have a signed owner. UNIT-built workers are owned by
     * the platform. Third-party workers declare their company and contact.
     *
     * Returns:
     *   type         'platform' (UNIT-built) | 'partner' (third-party) | 'custom' (tenant-built)
     *   name         Owner name  e.g. 'UNIT', 'Acme Consulting', 'City Agency IT'
     *   contact      Contact email for support, bug reports, and contract questions
     *   website      Owner's website
     *   license      'proprietary' | 'mit' | 'apache2' | 'gpl' | 'commercial'
     *   sla          Support SLA description  e.g. '99.9% uptime, 4h response'
     *   since        Year this worker was first published  e.g. 2024
     *   verified     Whether the owner identity has been verified by UNIT (bool)
     */
    public function owner(): array;

    // ── Block 8: Platform Integration ────────────────────────────────────────

    /**
     * Jobs this worker needs the platform scheduler to run on a recurring basis.
     * The platform iterates all registered workers and fires these automatically —
     * no scheduler changes needed when adding or removing a worker.
     *
     * Each entry:
     *   job             Fully-qualified job class name
     *   cron            Cron expression  e.g. '0 17 * * *'
     *   queue           Queue name to dispatch on
     *   per_deployment  true = dispatched once per active deployment (passing deployment_id)
     *                   false = dispatched once globally (no deployment_id argument)
     *   name            Unique schedule name (used by Laravel's named schedule dedup)
     *
     * Return [] if this worker has no scheduled jobs.
     */
    public function scheduledJobs(): array;

    /**
     * Fully-qualified class name of the job to dispatch for a Fast Track test run.
     * This may differ from ingestJobClass() when the worker needs to set up a
     * synthetic payload (e.g. insert a test email into Gmail) before the pipeline starts.
     *
     * Return '' (empty string) to use ingestJobClass() directly for fast track.
     */
    public function fastTrackJobClass(): string;

    /**
     * Maps transaction status strings to the job class that should re-dispatch
     * when a transaction is found stuck at that status.
     *
     * Used by the platform's stuck-recovery mechanism. Each key is a value that
     * may appear in transactions.status while a pipeline is mid-flight.
     *
     * Example:
     *   ['received' => ReadEmailJob::class, 'classifying' => ClassifyEmailJob::class]
     *
     * Return [] if this worker has no recovery mapping (stuck transactions will
     * be flagged but not auto-recovered).
     */
    public function stuckRecoveryMap(): array;
}
