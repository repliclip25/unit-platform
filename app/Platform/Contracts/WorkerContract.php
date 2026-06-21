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
 * Four platform primitives — every worker declares all four:
 *
 *   input()   What enters the pipeline at the start
 *   pipeline() Typed stage nodes — each stage declares what it accepts,
 *              produces, connects to, and which events it can emit
 *   emit()    Canonical list of all events the worker can fire across all
 *              stages, with full payload shape. Pipeline stages reference
 *              these by event name only.
 *   commit()  What a human or another worker can inject into this worker's
 *              pipeline mid-run. Returns null if the worker needs no injection.
 *
 * output() is NOT a separate method — it is the `produces` array of the
 * terminal pipeline stage. One source of truth.
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
     * The credential type this worker needs to operate.
     * Drives the Connect step of the deployment wizard.
     *
     * Returns:
     *   type           'gmail_oauth' | 'api_key' | 'oauth2' | 'webhook' | 'none'
     *   label          UI label shown in the connect step  e.g. 'Gmail Account'
     *   hint           Helper text shown beneath the credential selector
     *   multiple       Whether one deployment can connect multiple credentials (multi-inbox)
     *   connect_route  Named route that starts the credential connect flow
     *   authorize_route Named route that triggers the OAuth/API-key entry
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
     * Synthetic input payload for the authenticated tenant fast track test.
     * Fired from the worker detail page after deployment. Tenant must confirm
     * name and email (pre-filled from their account) before it fires.
     * Source is always 'fast_track_test'.
     */
    public function fastTrack(): array;

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
     *   connects_to   Job class of the next stage, or null if terminal
     *   can_emit      Array of event name strings this stage may fire
     *                 Full payload shape lives in emit(), not here
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
}
