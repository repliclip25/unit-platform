<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\QAController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\WorkerMemoryController;
use App\Http\Controllers\WorkerTemplateController;
use App\Http\Controllers\MemoryController;
use App\Http\Controllers\WorkerRulesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\GmailController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AdminTenantController;
use App\Http\Controllers\AdminInfluencerController;
use App\Http\Controllers\AdminPlatformController;
use App\Http\Controllers\WorkerBuilderController;
use App\Http\Controllers\AdminIntegrationController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\WorkerPublicController;
use App\Http\Controllers\InfluencerController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\AdminWorkerRequestController;
use App\Http\Controllers\AdminPromptController;
use App\Http\Controllers\AdminBlogController;
use App\Http\Controllers\AdminPlatformUsageController;
use App\Http\Controllers\AdminPipelineHealthController;
use App\Http\Controllers\AdminPricingController;
use App\Http\Controllers\AdminMessagingController;
use App\Http\Controllers\AdminWorkerLifecycleController;
use App\Http\Controllers\AdminWorkerRulesController;
use App\Http\Controllers\NuxController;
use App\Http\Controllers\AdminSelfLearnController;
use App\Http\Controllers\MemoryAccessController;
use App\Http\Controllers\AssetGroupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ─── Health check (uptime monitoring / load balancer probes) ─────────────────

Route::get('/health', function () {
    $db = 'fail';
    try { \Illuminate\Support\Facades\DB::select('SELECT 1'); $db = 'ok'; } catch (\Throwable) {}
    $cache = 'fail';
    try { \Illuminate\Support\Facades\Cache::put('health', 1, 10); $cache = 'ok'; } catch (\Throwable) {}
    $status   = ($db === 'ok' && $cache === 'ok') ? 'healthy' : 'degraded';
    $httpCode = $status === 'healthy' ? 200 : 503;
    return response()->json(['status' => $status, 'database' => $db, 'cache' => $cache, 'queue' => config('queue.default')], $httpCode);
})->name('health');

// ─── Public routes ────────────────────────────────────────────────────────────

Route::get('/', function () {
    return view('welcome');
});

// Account deletion confirmation (token-authenticated, no login required)
Route::get('/account/delete-confirm/{token}',  [\App\Http\Controllers\AccountDeletionController::class, 'confirm'])->name('account.delete-confirm');
Route::post('/account/delete-confirm/{token}', [\App\Http\Controllers\AccountDeletionController::class, 'execute'])->name('account.delete-execute');

// Public marketing pages
Route::get('/about',                [PublicPageController::class, 'about'])->name('about');
Route::get('/pricing',              [PublicPageController::class, 'pricing'])->name('pricing');
Route::get('/privacy',              [PublicPageController::class, 'privacy'])->name('privacy');
Route::get('/terms',                [PublicPageController::class, 'terms'])->name('terms');
Route::get('/marketplace',          [PublicPageController::class, 'marketplace'])->name('marketplace');
Route::post('/marketplace/request', [PublicPageController::class, 'requestWorker'])->middleware('throttle:5,1')->name('marketplace.request');
Route::get('/blog',                 [PublicPageController::class, 'blog'])->name('blog');
Route::get('/blog/{slug}',          [PublicPageController::class, 'blogPost'])->name('blog.show');

// Memory access invite accept (public — email link may arrive before login)
Route::get( '/memory/access/accept/{token}', [MemoryAccessController::class, 'acceptShow'])->name('memory.access.accept');
Route::post('/memory/access/accept/{token}', [MemoryAccessController::class, 'acceptStore'])->middleware('auth')->name('memory.access.accept.store');

// Gmail OAuth callback (must be public — Google redirects here)
Route::get('/workers/ava/gmail/callback', [GmailController::class, 'callback'])->name('ava.gmail.callback');

// NUX OAuth callbacks (must be public — LinkedIn/X redirect here)
Route::get('/nux/linkedin/callback', [NuxController::class, 'linkedinCallback'])->name('nux.linkedin.callback');
Route::get('/nux/x/callback',        [NuxController::class, 'xCallback'])->name('nux.x.callback');

// Stripe webhook (must be public and CSRF-exempt — verified by Stripe signature)
Route::post('/stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');

// Gmail Pub/Sub webhook (Google pushes here — must be public)
Route::post('/workers/ava/gmail/webhook', [GmailController::class, 'webhook'])->name('ava.gmail.webhook');

// ─── Authenticated routes ─────────────────────────────────────────────────────

// Onboarding: auth only — gate logic handled inside controllers
Route::middleware(['auth'])->group(function () {
    Route::get('/onboarding',                      [\App\Http\Controllers\OnboardingController::class, 'index'])->name('onboarding');

    // Named step routes (GET = show, POST = handle)
    Route::get('/onboarding/step/{name}',          [\App\Http\Controllers\OnboardingController::class, 'showStep'])->name('onboarding.step');
    Route::post('/onboarding/step/{name}',         [\App\Http\Controllers\OnboardingController::class, 'handleStep'])->name('onboarding.step.handle');

    // Special: email verification screen with edit mode (pre-session, outside step flow)
    Route::get('/onboarding/verify-email',         [\App\Http\Controllers\OnboardingController::class, 'verifyEmailScreen'])->name('onboarding.verify');
    Route::post('/onboarding/update-email',        [\App\Http\Controllers\OnboardingController::class, 'updateEmail'])->name('onboarding.update-email');

    // Memory seed (called from memory step UI, not a step handler)
    Route::post('/onboarding/memory/seed',         [\App\Http\Controllers\OnboardingController::class, 'seedMemory'])->name('onboarding.memory.seed');
    Route::post('/onboarding/memory/quickadd',     [\App\Http\Controllers\OnboardingController::class, 'quickAddMemory'])->name('onboarding.memory.quickadd');

    Route::get('/onboarding/gmail-draft',           [\App\Http\Controllers\OnboardingController::class, 'gmailDraft'])->name('onboarding.gmail-draft');
    Route::get('/onboarding/complete',             [\App\Http\Controllers\OnboardingController::class, 'complete'])->name('onboarding.complete');
    Route::get('/onboarding/skip',                 [\App\Http\Controllers\OnboardingController::class, 'skip'])->name('onboarding.skip');

    // Pipeline status — accessible to any authenticated user (tenant-scoped inside the controller)
    Route::get('/qa/pipeline/{txId}', [\App\Http\Controllers\QAController::class, 'pipelineStatus'])->name('qa.pipeline-status');
});

// All other authenticated routes require verified email + completed onboarding
Route::middleware(['auth', 'verified', 'onboarded', 'not-pending-del'])->group(function () {

    // ── Transaction status polling (used by fast-track pipeline UI) ─────────
    Route::get('/transactions/{txId}/status', [TransactionController::class, 'status'])->name('transactions.status');

    // ── Command Center ──────────────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/desk/save',    [DashboardController::class, 'deskSave'])->name('dashboard.desk.save');
    Route::post('/dashboard/desk/dismiss', [DashboardController::class, 'deskDismiss'])->name('dashboard.desk.dismiss');
    Route::post('/self-learn/dismiss', [DashboardController::class, 'selfLearnDismiss'])->name('self-learn.dismiss');

    // ── Transactions ────────────────────────────────────────────────────────
    Route::get('/transactions',              [TransactionController::class, 'index'])->name('transactions');
    Route::get('/transactions/{txId}',       [TransactionController::class, 'show'])->name('transactions.show');
    Route::post('/transactions/{txId}/refire',  [TransactionController::class, 'refire'])->name('transactions.refire');
    Route::post('/transactions/{txId}/dismiss', [TransactionController::class, 'dismiss'])->name('transactions.dismiss');
    Route::delete('/transactions/{txId}',    [TransactionController::class, 'destroy'])->name('transactions.delete');
    Route::post('/transactions/{txId}/decide',  [TransactionController::class, 'decide'])->name('transactions.decide');

    // ── Renewal Register ────────────────────────────────────────────────────
    Route::get('/register', [RegisterController::class, 'index'])->name('register');

    // ── Memory Management ───────────────────────────────────────────────────
    Route::get('/memory',                         [MemoryController::class, 'index'])->name('memory');
    Route::post('/memory/clients',                [MemoryController::class, 'storeClient'])->name('memory.clients.store');
    Route::delete('/memory/clients/{id}',         [MemoryController::class, 'destroyClient'])->name('memory.clients.destroy');
    Route::post('/memory/contacts',               [MemoryController::class, 'storeContact'])->name('memory.contacts.store');
    Route::delete('/memory/contacts/{id}',        [MemoryController::class, 'destroyContact'])->name('memory.contacts.destroy');
    Route::post('/memory/assets',                 [MemoryController::class, 'storeAsset'])->name('memory.assets.store');
    Route::patch('/memory/assets/{id}',           [MemoryController::class, 'updateAsset'])->name('memory.assets.update');
    Route::delete('/memory/assets/{id}',          [MemoryController::class, 'destroyAsset'])->name('memory.assets.destroy');
    Route::post('/memory/rules',                  [MemoryController::class, 'storeRule'])->name('memory.rules.store');
    Route::delete('/memory/rules/{id}',           [MemoryController::class, 'destroyRule'])->name('memory.rules.destroy');

    // ── Memory access (collaboration) ─────────────────────────────────────────
    Route::get( '/memory/access',                        [MemoryAccessController::class, 'redirectToAccess'])->name('memory.access');
    Route::post('/memory/access/invite',                 [MemoryAccessController::class, 'invite'])->name('memory.access.invite');
    Route::post('/memory/access/{grant}/revoke',         [MemoryAccessController::class, 'revoke'])->name('memory.access.revoke');
    Route::get( '/memory/shared/{grant}',                [MemoryAccessController::class, 'sharedMemory'])->name('memory.shared');
    Route::post('/memory/shared/{grant}/copy',           [MemoryAccessController::class, 'copyRecord'])->name('memory.access.copy');
    Route::post('/memory/shared/{grant}/upload',         [MemoryAccessController::class, 'uploadRecord'])->name('memory.access.upload');

    // ── Memory Import Templates (downloads) ────────────────────────────────
    Route::get('/memory/import/template/{type}',  [MemoryController::class, 'importTemplate'])->name('memory.import.template');

    // ── Memory Import ───────────────────────────────────────────────────────
    Route::post('/memory/import/preview',         [MemoryController::class, 'importPreview'])->name('memory.import.preview');
    Route::post('/memory/import/commit',          [MemoryController::class, 'importCommit'])->name('memory.import.commit');

    // ── Worker Deployments ──────────────────────────────────────────────────
    Route::get('/workers',                                           [WorkerController::class, 'index'])->name('workers.deploy');
    Route::post('/workers',                                          [WorkerController::class, 'store'])->name('workers.store');
    Route::get('/workers/{slug}',                                    [WorkerController::class, 'show'])->name('workers.show');
    Route::delete('/workers/{id}',                                   [WorkerController::class, 'destroy'])->name('workers.destroy');
    Route::patch('/workers/{id}/status',                             [WorkerController::class, 'updateStatus'])->name('workers.status');
    Route::get('/workers/{slug}/connect',                            [WorkerController::class, 'connect'])->name('workers.connect');
    Route::post('/workers/{id}/inboxes',                             [WorkerController::class, 'connectInbox'])->name('workers.inboxes.connect');
    Route::delete('/workers/{id}/inboxes/{pivotId}',                 [WorkerController::class, 'disconnectInbox'])->name('workers.inboxes.disconnect');
    Route::post('/workers/{id}/inboxes/{credentialId}/rewatch',      [WorkerController::class, 'rewatchInbox'])->name('workers.inboxes.rewatch');
    Route::get('/workers/{slug}/configure',                          [WorkerController::class, 'configure'])->name('workers.configure');
    Route::patch('/workers/{id}/config',                             [WorkerController::class, 'updateConfig'])->name('workers.config');
    Route::patch('/workers/{id}/model',                              [WorkerController::class, 'updateModel'])->name('workers.model');
    Route::patch('/workers/{id}/persona',                            [WorkerController::class, 'updatePersona'])->name('workers.persona');
    Route::post('/workers/{id}/prompt-overrides',                    [WorkerController::class, 'savePromptOverride'])->name('workers.prompt-overrides');
    Route::post('/workers/{id}/prompt-test',                         [WorkerController::class, 'testPrompt'])->name('workers.prompt-test');
    Route::get('/workers/{slug}/log',                                [WorkerController::class, 'log'])->name('workers.log');
    Route::get('/workers/{slug}/observe',                            [WorkerController::class, 'observe'])->name('workers.observe');
    Route::get('/workers/{slug}/schema',                             [WorkerController::class, 'schema'])->name('workers.schema');
    Route::get('/workers/{slug}/billing',                            [WorkerController::class, 'billing'])->name('workers.billing');
    Route::post('/workers/{id}/fast-track',                          [WorkerController::class, 'fastTrack'])->name('workers.fast-track');
    Route::get('/workers/ava/status/{txId}',                         [WorkerController::class, 'fastTrackStatus'])->name('ava.status');

    // ── Worker: Memory ──────────────────────────────────────────────────────
    Route::get('/workers/{slug}/memory',                             [WorkerMemoryController::class, 'index'])->name('workers.memory');
    Route::post('/workers/{id}/memory/import/preview',               [WorkerMemoryController::class, 'importPreview'])->name('workers.memory.import.preview');
    Route::post('/workers/{id}/memory/import/commit',                [WorkerMemoryController::class, 'importCommit'])->name('workers.memory.import.commit');
    Route::post('/workers/{id}/memory/clients',                      [WorkerMemoryController::class, 'storeClient'])->name('workers.memory.clients.store');
    Route::patch('/workers/{id}/memory/clients/{cid}',              [WorkerMemoryController::class, 'updateClient'])->name('workers.memory.clients.update');
    Route::delete('/workers/{id}/memory/clients/{cid}',              [WorkerMemoryController::class, 'destroyClient'])->name('workers.memory.clients.destroy');
    Route::post('/workers/{id}/memory/contacts',                     [WorkerMemoryController::class, 'storeContact'])->name('workers.memory.contacts.store');
    Route::patch('/workers/{id}/memory/contacts/{cid}',             [WorkerMemoryController::class, 'updateContact'])->name('workers.memory.contacts.update');
    Route::delete('/workers/{id}/memory/contacts/{cid}',             [WorkerMemoryController::class, 'destroyContact'])->name('workers.memory.contacts.destroy');
    Route::post('/workers/{id}/memory/assets',                       [WorkerMemoryController::class, 'storeAsset'])->name('workers.memory.assets.store');
    Route::patch('/workers/{id}/memory/assets/{aid}',               [WorkerMemoryController::class, 'updateAsset'])->name('workers.memory.assets.update');
    Route::post('/workers/{id}/memory/assets/{aid}/approve',        [WorkerMemoryController::class, 'approveAsset'])->name('workers.memory.assets.approve');
    Route::delete('/workers/{id}/memory/assets/{aid}',               [WorkerMemoryController::class, 'destroyAsset'])->name('workers.memory.assets.destroy');

    // Asset Groups (worker-scoped)
    Route::get('/workers/{id}/memory/groups',                        [AssetGroupController::class, 'index'])->name('workers.memory.groups');
    Route::post('/workers/{id}/memory/groups',                       [AssetGroupController::class, 'store'])->name('workers.memory.groups.store');
    Route::patch('/workers/{id}/memory/groups/{gid}',               [AssetGroupController::class, 'update'])->name('workers.memory.groups.update');
    Route::delete('/workers/{id}/memory/groups/{gid}',               [AssetGroupController::class, 'destroy'])->name('workers.memory.groups.destroy');
    Route::post('/workers/{id}/memory/groups/{gid}/items',           [AssetGroupController::class, 'addItem'])->name('workers.memory.groups.items.add');
    Route::delete('/workers/{id}/memory/groups/{gid}/items/{aid}',   [AssetGroupController::class, 'removeItem'])->name('workers.memory.groups.items.remove');
    Route::post('/workers/{id}/memory/groups/{gid}/reorder',         [AssetGroupController::class, 'reorder'])->name('workers.memory.groups.reorder');

    // ── Worker: Templates ───────────────────────────────────────────────────
    Route::get('/workers/{slug}/templates',                          [WorkerTemplateController::class, 'workerIndex'])->name('workers.templates');
    Route::post('/workers/{id}/templates',                           [WorkerTemplateController::class, 'workerStore'])->name('workers.templates.store');
    Route::delete('/workers/{id}/templates/{tid}',                   [WorkerTemplateController::class, 'workerDestroy'])->name('workers.templates.destroy');
    Route::post('/workers/{id}/templates/{tid}/fork',                [WorkerTemplateController::class, 'workerFork'])->name('workers.templates.fork');
    Route::put('/workers/{id}/templates/{tid}',                      [WorkerTemplateController::class, 'workerUpdate'])->name('workers.templates.update');
    Route::post('/workers/{id}/templates/{tid}/test',                [WorkerTemplateController::class, 'workerTest'])->name('workers.templates.test');

    // ── Worker: Rules ───────────────────────────────────────────────────────
    Route::get('/workers/{slug}/rules',                              [WorkerRulesController::class, 'index'])->name('workers.rules');
    Route::post('/workers/{id}/rules',                               [WorkerRulesController::class, 'store'])->name('workers.rules.store');
    Route::patch('/workers/{id}/rules/{rid}',                        [WorkerRulesController::class, 'update'])->name('workers.rules.update');
    Route::delete('/workers/{id}/rules/{rid}',                       [WorkerRulesController::class, 'destroy'])->name('workers.rules.destroy');
    Route::post('/workers/{id}/rules/reset',                         [WorkerRulesController::class, 'resetToContract'])->name('workers.rules.reset');

    // ── Models & API Keys ───────────────────────────────────────────────────
    Route::get('/settings/api-keys',             [SettingsController::class, 'apiKeys'])->name('settings.api-keys');
    Route::post('/settings/api-keys',            [SettingsController::class, 'storeApiKey'])->name('settings.api-keys.store');
    Route::delete('/settings/api-keys/{provider}',[SettingsController::class, 'destroyApiKey'])->name('settings.api-keys.destroy');

    // ── Custom Model Registration ────────────────────────────────────────────
    Route::post('/settings/custom-models',       [SettingsController::class, 'storeCustomModel'])->name('settings.custom-models.store');
    Route::delete('/settings/custom-models/{id}',[SettingsController::class, 'destroyCustomModel'])->name('settings.custom-models.destroy');
    Route::delete('/settings/account',           [SettingsController::class, 'deleteAccount'])->name('settings.account.delete');

    // ── AVA Connection & Onboarding ─────────────────────────────────────────
    Route::get('/ava/connect',        [GmailController::class, 'connect'])->name('ava.connect');
    Route::get('/ava/gmail/authorize',[GmailController::class, 'authorize'])->name('ava.gmail.authorize');
    Route::get('/ava/gmail/watch',    [GmailController::class, 'watch'])->name('ava.gmail.watch');

    // ── NUX Connection & Onboarding ──────────────────────────────────────────
    Route::get('/nux/connect/linkedin',   [NuxController::class, 'linkedinAuthorize'])->name('nux.connect.linkedin');
    Route::get('/nux/connect/x',          [NuxController::class, 'xAuthorize'])->name('nux.connect.x');
    Route::get('/nux/connect/gmail',      [GmailController::class, 'connect'])->name('nux.connect.gmail');
    Route::get('/nux/gmail/authorize',    [GmailController::class, 'authorize'])->name('nux.gmail.authorize');
    Route::delete('/nux/disconnect/linkedin', [NuxController::class, 'disconnectLinkedIn'])->name('nux.disconnect.linkedin');
    Route::delete('/nux/disconnect/x',        [NuxController::class, 'disconnectX'])->name('nux.disconnect.x');

    // ── NUX: idea submission + performance feedback ─────────────────────────
    Route::post('/workers/{id}/nux/idea',             [NuxController::class, 'submitIdea'])->name('nux.submit.idea');
    Route::post('/nux/performance/{registerId}',      [NuxController::class, 'submitPerformance'])->name('nux.submit.performance');

    // Billing
    Route::get('/billing', [BillingController::class, 'index'])->name('billing');
    Route::get('/billing/checkout/{deployment}',    [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('/billing/success/{deployment}',     [BillingController::class, 'success'])->name('billing.success');
    Route::post('/billing/reactivate/{deployment}', [BillingController::class, 'reactivate'])->name('billing.reactivate');
    Route::get('/billing/portal',                   [BillingController::class, 'portal'])->name('billing.portal');
    Route::get('/billing/invoice/{id}', function (string $id) {
        return request()->user()->downloadInvoice($id);
    })->name('billing.invoice');

    // ── Admin-only: System QA + Tenant Controls + Admin actions ──────────────
    Route::middleware('admin')->group(function () {

        // Reset fast track trial counter for a deployment
        Route::post('/workers/{id}/fast-track/reset', [AdminTenantController::class, 'fastTrackReset'])->name('workers.fast-track.reset');

        // System QA (admin-only — moved to /admin/qa)
        Route::get('/admin/qa', [QAController::class, 'index'])->name('qa');
        Route::post('/admin/qa/fast-track/{deployment}', [QAController::class, 'fastTrack'])->name('qa.fast-track');
        Route::post('/admin/qa/transactions/recover-stuck', [QAController::class, 'recoverStuck'])->name('qa.recover-stuck');
        Route::post('/admin/qa/worker/{deployment}/renew-watch', [QAController::class, 'renewGmailWatch'])->name('qa.renew-watch');
        Route::post('/admin/qa/scenario/{deployment}', [QAController::class, 'updateScenario'])->name('qa.scenario-update');
        Route::post('/admin/qa/worker/{deployment}/pause', [QAController::class, 'pauseWorker'])->name('qa.worker-pause');
        Route::post('/admin/qa/worker/{deployment}/resume', [QAController::class, 'resumeWorker'])->name('qa.worker-resume');
        Route::post('/admin/qa/worker/{deployment}/drain', [QAController::class, 'drainWorker'])->name('qa.worker-drain');
        Route::get('/admin/qa/worker/{deployment}/queue-status', [QAController::class, 'queueStatus'])->name('qa.queue-status');
        Route::post('/admin/qa/horizon/restart', [QAController::class, 'restartHorizon'])->name('qa.horizon-restart');
        Route::post('/admin/qa/worker/{deployment}/pipeline-config', [QAController::class, 'updatePipelineConfig'])->name('qa.pipeline-config');
        Route::post('/admin/qa/marketplace/{worker}/publish', [QAController::class, 'publishWorker'])->name('qa.marketplace-publish');
        Route::post('/admin/qa/marketplace/{worker}/status', [QAController::class, 'updateMarketplaceStatus'])->name('qa.marketplace-status');
        Route::get('/qa/marketplace/{worker}/blueprint', [QAController::class, 'downloadBlueprint'])->name('qa.marketplace-blueprint');
        Route::get('/qa/platform-blueprint', [QAController::class, 'downloadPlatformBlueprint'])->name('qa.platform-blueprint');
        Route::get('/qa/worker/{worker}/markdown-blueprint', [QAController::class, 'downloadWorkerBlueprint'])->name('qa.worker-blueprint');

        // Platform Control Tower
        Route::get('/admin/platform',                                    [AdminPlatformController::class, 'index'])->name('admin.platform');
        Route::get('/admin/platform/{section}',                          [AdminPlatformController::class, 'section'])->name('admin.platform.section');

        // Queue actions
        Route::post('/admin/platform/queue/retry-all',                   [AdminPlatformController::class, 'queueRetryAll'])->name('admin.platform.queue.retry-all');
        Route::post('/admin/platform/queue/clear-failed',                [AdminPlatformController::class, 'queueClearFailed'])->name('admin.platform.queue.clear-failed');
        Route::post('/admin/platform/queue/clear-stuck',                 [AdminPlatformController::class, 'queueClearStuck'])->name('admin.platform.queue.clear-stuck');

        // AI model + circuit breaker
        Route::post('/admin/platform/ai/switch-model',                   [AdminPlatformController::class, 'switchModel'])->name('admin.platform.ai.switch-model');
        Route::post('/admin/platform/ai/circuit-breaker/reset',          [AdminPlatformController::class, 'circuitBreakerReset'])->name('admin.platform.ai.circuit-reset');
        Route::post('/admin/platform/ai/circuit-breaker/settings',       [AdminPlatformController::class, 'circuitBreakerSettings'])->name('admin.platform.ai.circuit-settings');

        // Gmail watches
        Route::post('/admin/platform/watches/{id}/renew',                [AdminPlatformController::class, 'renewWatch'])->name('admin.platform.watch.renew');
        Route::post('/admin/platform/watches/{id}/deactivate',           [AdminPlatformController::class, 'deactivateWatch'])->name('admin.platform.watch.deactivate');
        Route::post('/admin/platform/watches/{id}/reset-history',        [AdminPlatformController::class, 'resetHistoryId'])->name('admin.platform.watch.reset-history');
        Route::post('/admin/platform/watches/renew-all',                 [AdminPlatformController::class, 'renewAllWatches'])->name('admin.platform.watches.renew-all');

        // Worker deployment controls
        Route::post('/admin/platform/workers/{slug}/pause-all',          [AdminPlatformController::class, 'pauseAllDeployments'])->name('admin.platform.worker.pause-all');
        Route::post('/admin/platform/workers/{slug}/resume-all',         [AdminPlatformController::class, 'resumeAllDeployments'])->name('admin.platform.worker.resume-all');
        Route::post('/admin/platform/workers/{slug}/stop-all',           [AdminPlatformController::class, 'stopAllDeployments'])->name('admin.platform.worker.stop-all');
        Route::post('/admin/platform/workers/{slug}/start-all',          [AdminPlatformController::class, 'startAllDeployments'])->name('admin.platform.worker.start-all');

        // SMTP routes
        Route::post('/admin/platform/smtp/routes/{key}/update',          [AdminPlatformController::class, 'updateSmtpRoute'])->name('admin.platform.smtp.update');
        Route::post('/admin/platform/smtp/routes/add',                   [AdminPlatformController::class, 'addSmtpRoute'])->name('admin.platform.smtp.add');
        Route::post('/admin/platform/smtp/routes/{key}/delete',          [AdminPlatformController::class, 'deleteSmtpRoute'])->name('admin.platform.smtp.delete');

        // Messaging templates
        Route::post('/admin/platform/messaging/{key}/save',              [AdminPlatformController::class, 'saveMessageTemplate'])->name('admin.platform.msg.save');
        Route::get('/admin/platform/messaging/{key}/preview',            [AdminPlatformController::class, 'previewMessageTemplate'])->name('admin.platform.msg.preview');

        // SMTP
        Route::post('/admin/platform/smtp/test',                         [AdminPlatformController::class, 'testSmtp'])->name('admin.platform.smtp.test');
        Route::post('/admin/platform/billing/trial-gate',                [AdminPlatformController::class, 'toggleTrialGate'])->name('admin.platform.billing.trial-gate');

        // System
        Route::post('/admin/platform/system/clear-caches',               [AdminPlatformController::class, 'clearCaches'])->name('admin.platform.system.clear-caches');

        // Worker Builder (DNA Registration + Scaffold)
        Route::get('/admin/workers',                                      [WorkerBuilderController::class, 'index'])->name('admin.workers.index');
        Route::get('/admin/workers/new',                                  [WorkerBuilderController::class, 'create'])->name('admin.workers.create');
        Route::post('/admin/workers',                                     [WorkerBuilderController::class, 'store'])->name('admin.workers.store');
        Route::get('/admin/workers/{slug}/edit',                          [WorkerBuilderController::class, 'edit'])->name('admin.workers.edit');
        Route::post('/admin/workers/{slug}',                              [WorkerBuilderController::class, 'update'])->name('admin.workers.update');
        Route::post('/admin/workers/{slug}/media',                        [WorkerBuilderController::class, 'saveMedia'])->name('admin.workers.media');
        Route::post('/admin/workers/{slug}/scaffold',                     [WorkerBuilderController::class, 'generateScaffold'])->name('admin.workers.scaffold');
        Route::post('/admin/workers/{slug}/status',                       [WorkerBuilderController::class, 'updateStatus'])->name('admin.workers.status');
        Route::delete('/admin/workers/{slug}',                            [WorkerBuilderController::class, 'destroy'])->name('admin.workers.destroy');
        Route::get('/admin/workers/{slug}/export',                        [WorkerBuilderController::class, 'exportSchema'])->name('admin.workers.export');
        Route::get('/admin/workers/{slug}/rules',                         [AdminWorkerRulesController::class, 'index'])->name('admin.workers.rules');
        Route::post('/admin/workers/{slug}/rules',                        [AdminWorkerRulesController::class, 'store'])->name('admin.workers.rules.store');
        Route::delete('/admin/workers/{slug}/rules/{id}',                 [AdminWorkerRulesController::class, 'destroy'])->name('admin.workers.rules.destroy');
        Route::post('/admin/workers/{slug}/rules/sync',                   [AdminWorkerRulesController::class, 'syncFromContract'])->name('admin.workers.rules.sync');

        // Integration Registry
        Route::get('/admin/messaging',                    [AdminMessagingController::class, 'index'])->name('admin.messaging');
        Route::post('/admin/messaging',                   [AdminMessagingController::class, 'store'])->name('admin.messaging.store');
        Route::put('/admin/messaging/{id}',               [AdminMessagingController::class, 'update'])->name('admin.messaging.update');
        Route::post('/admin/messaging/{id}/reset',        [AdminMessagingController::class, 'reset'])->name('admin.messaging.reset');
        Route::post('/admin/messaging/{id}/rewrite',      [AdminMessagingController::class, 'rewrite'])->name('admin.messaging.rewrite');
        Route::post('/admin/messaging/{id}/accept',       [AdminMessagingController::class, 'acceptRewrite'])->name('admin.messaging.accept');
        Route::post('/admin/messaging/{id}/test-send',    [AdminMessagingController::class, 'testSend'])->name('admin.messaging.test-send');
        Route::post('/admin/messaging/seed',               [AdminMessagingController::class, 'seed'])->name('admin.messaging.seed');

        Route::get('/admin/pricing',               [AdminPricingController::class, 'index'])->name('admin.pricing');
        Route::post('/admin/pricing',              [AdminPricingController::class, 'store'])->name('admin.pricing.store');
        Route::put('/admin/pricing/{id}',          [AdminPricingController::class, 'update'])->name('admin.pricing.update');
        Route::post('/admin/pricing/{id}/toggle',  [AdminPricingController::class, 'toggle'])->name('admin.pricing.toggle');
        Route::post('/admin/pricing/verify-price',  [AdminPricingController::class, 'verifyStripePrice'])->name('admin.pricing.verify-price');
        Route::post('/admin/pricing/verify-coupon', [AdminPricingController::class, 'verifyStripeCoupon'])->name('admin.pricing.verify-coupon');
        Route::post('/admin/pricing/{id}/billing-mode', [AdminPricingController::class, 'setBillingMode'])->name('admin.pricing.billing-mode');

        Route::get('/admin/integrations',          [AdminIntegrationController::class, 'index'])->name('admin.integrations');
        Route::post('/admin/integrations',         [AdminIntegrationController::class, 'store'])->name('admin.integrations.store');
        Route::put('/admin/integrations/{id}',     [AdminIntegrationController::class, 'update'])->name('admin.integrations.update');
        Route::delete('/admin/integrations/{id}',  [AdminIntegrationController::class, 'destroy'])->name('admin.integrations.destroy');

        // Tenant Controls
        Route::get('/admin/tenants',                                     [AdminTenantController::class, 'index'])->name('admin.tenants');
        Route::get('/admin/tenants/{id}',                                [AdminTenantController::class, 'show'])->name('admin.tenants.show');
        Route::post('/admin/tenants/{id}/block',                         [AdminTenantController::class, 'block'])->name('admin.tenants.block');
        Route::post('/admin/tenants/{id}/unblock',                       [AdminTenantController::class, 'unblock'])->name('admin.tenants.unblock');
        Route::post('/admin/tenants/{id}/spend-cap',                     [AdminTenantController::class, 'setSpendCap'])->name('admin.tenants.spend-cap');
        Route::post('/admin/tenants/{id}/reset-trial',                   [AdminTenantController::class, 'resetTrial'])->name('admin.tenants.reset-trial');
        Route::post('/admin/tenants/{id}/reset-password',                [AdminTenantController::class, 'resetPassword'])->name('admin.tenants.reset-password');
        Route::post('/admin/tenants/{id}/message',                       [AdminTenantController::class, 'sendMessage'])->name('admin.tenants.message');
        Route::post('/admin/tenants/{id}/ai-message',                    [AdminTenantController::class, 'sendAiMessage'])->name('admin.tenants.ai-message');
        Route::post('/admin/tenants/{id}/toggle-block',                  [AdminTenantController::class, 'toggleBlock'])->name('admin.tenants.toggle-block');
        Route::post('/admin/tenants/{id}/flush',                          [AdminTenantController::class, 'flush'])->name('admin.tenants.flush');
        Route::post('/admin/tenants/{id}/request-deletion',              [AdminTenantController::class, 'requestDeletion'])->name('admin.tenants.request-deletion');
        Route::post('/admin/deployments/{id}/backfill-billing',          [AdminTenantController::class, 'backfillBilling'])->name('admin.deployments.backfill-billing');
        Route::post('/admin/deployments/{id}/set-billing-status',        [AdminTenantController::class, 'setBillingStatus'])->name('admin.deployments.set-billing-status');
        Route::post('/admin/invoices/{invoiceId}/void',                  [AdminTenantController::class, 'voidInvoice'])->name('admin.invoices.void');

        // ── Influencer Admin ──────────────────────────────────────────────────
        Route::get('/admin/influencers',                [AdminInfluencerController::class, 'index'])->name('admin.influencers');
        Route::get('/admin/influencers/{id}',           [AdminInfluencerController::class, 'show'])->name('admin.influencers.show');
        Route::post('/admin/influencers/{id}/approve',  [AdminInfluencerController::class, 'approve'])->name('admin.influencers.approve');
        Route::post('/admin/influencers/{id}/update',   [AdminInfluencerController::class, 'update'])->name('admin.influencers.update');
        Route::post('/admin/influencers/{id}/payout',   [AdminInfluencerController::class, 'payout'])->name('admin.influencers.payout');

        // ── Worker Requests ──────────────────────────────────────────────────
        Route::get('/admin/worker-requests',               [AdminWorkerRequestController::class, 'index'])->name('admin.worker-requests');
        Route::get('/admin/worker-requests/{id}',          [AdminWorkerRequestController::class, 'show'])->name('admin.worker-requests.show');
        Route::post('/admin/worker-requests/{id}/status',  [AdminWorkerRequestController::class, 'updateStatus'])->name('admin.worker-requests.status');
        Route::delete('/admin/worker-requests/{id}',       [AdminWorkerRequestController::class, 'destroy'])->name('admin.worker-requests.destroy');

        // ── Prompt Editor ────────────────────────────────────────────────────
        Route::get('/admin/prompts',        [AdminPromptController::class, 'index'])->name('admin.prompts');
        Route::post('/admin/prompts/save',  [AdminPromptController::class, 'update'])->name('admin.prompts.update');
        Route::post('/admin/prompts/reset', [AdminPromptController::class, 'reset'])->name('admin.prompts.reset');

        // ── Blog Management ──────────────────────────────────────────────────
        Route::get('/admin/blog',              [AdminBlogController::class, 'index'])->name('admin.blog');
        Route::get('/admin/blog/new',          [AdminBlogController::class, 'create'])->name('admin.blog.create');
        Route::post('/admin/blog',             [AdminBlogController::class, 'store'])->name('admin.blog.store');
        Route::post('/admin/blog/ai-rewrite',   [AdminBlogController::class, 'aiRewrite'])->middleware('throttle:20,1')->name('admin.blog.ai-rewrite');
        Route::post('/admin/blog/{id}/publish', [AdminBlogController::class, 'publish'])->name('admin.blog.publish');
        Route::get('/admin/blog/{id}/edit',    [AdminBlogController::class, 'edit'])->name('admin.blog.edit');
        Route::put('/admin/blog/{id}',         [AdminBlogController::class, 'update'])->name('admin.blog.update');
        Route::delete('/admin/blog/{id}',      [AdminBlogController::class, 'destroy'])->name('admin.blog.destroy');

        // ── Self Learn content registry ──────────────────────────────────────
        Route::get( '/admin/self-learn',                      [AdminSelfLearnController::class, 'index'])->name('admin.self-learn');
        Route::post('/admin/self-learn/{pageKey}',            [AdminSelfLearnController::class, 'update'])->name('admin.self-learn.update');
        Route::post('/admin/self-learn/{pageKey}/toggle',     [AdminSelfLearnController::class, 'toggle'])->name('admin.self-learn.toggle');
        Route::post('/admin/self-learn/{pageKey}/bump',       [AdminSelfLearnController::class, 'bumpVersion'])->name('admin.self-learn.bump');

        // ── Platform Token Usage ─────────────────────────────────────────────
        Route::get('/admin/platform-usage', [AdminPlatformUsageController::class, 'index'])->name('admin.platform-usage');

        // ── Pipeline Stage Health ─────────────────────────────────────────────
        Route::get('/admin/pipeline-health', [AdminPipelineHealthController::class, 'index'])->name('admin.pipeline-health');

        // Desk card admin
        Route::get( '/admin/desk-cards',                    [\App\Http\Controllers\AdminDeskCardController::class, 'index'])->name('admin.desk-cards');
        Route::post('/admin/desk-cards/save',               [\App\Http\Controllers\AdminDeskCardController::class, 'save'])->name('admin.desk-cards.save');
        Route::post('/admin/desk-cards/{key}/toggle',       [\App\Http\Controllers\AdminDeskCardController::class, 'toggle'])->name('admin.desk-cards.toggle')->where('key', '[a-z0-9._-]+');
        Route::post('/admin/desk-cards/{key}/toggle-default',[\App\Http\Controllers\AdminDeskCardController::class, 'toggleDefault'])->name('admin.desk-cards.toggle-default')->where('key', '[a-z0-9._-]+');

        // Worker lifecycle management
        Route::post('/admin/workers/{slug}/commission',   [AdminWorkerLifecycleController::class, 'commission'])->name('admin.workers.commission');
        Route::post('/admin/workers/{slug}/testing',      [AdminWorkerLifecycleController::class, 'setTesting'])->name('admin.workers.testing');
        Route::post('/admin/workers/{slug}/decommission', [AdminWorkerLifecycleController::class, 'decommission'])->name('admin.workers.decommission');
        Route::post('/admin/workers/{slug}/remove',       [AdminWorkerLifecycleController::class, 'remove'])->name('admin.workers.remove');

    }); // end admin middleware group

}); // end onboarded + not-pending-del group

// Profile — auth only (accessible even when deletion is pending)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile/sessions/{sessionId}', [ProfileController::class, 'revokeSession'])->name('profile.session.revoke');
    Route::delete('/profile/sessions', [ProfileController::class, 'revokeOtherSessions'])->name('profile.sessions.revoke-all');
    Route::post('/profile/cancel-deletion', [ProfileController::class, 'cancelDeletion'])->name('profile.cancel-deletion');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Public Worker Pages ────────────────────────────────────────────────
Route::get('/w/{slug}', [WorkerPublicController::class, 'show'])->name('workers.public.show'); // public — no auth

// ── Public Fast Track Submit (no auth) ────────────────────────────
Route::post('/fast-track/submit', [ReferralController::class, 'fastTrackSubmit'])->name('fast-track.submit');

// ── Public Influencer Redirect ─────────────────────────────────────────
Route::get('/r/{slug}', [ReferralController::class, 'influencerRedirect'])->name('influencer.redirect');

// ── Referral Program Public Page ──────────────────────────────────────
Route::get('/referral', [ReferralController::class, 'index'])->name('referral.index');

// ── Influencer Application ─────────────────────────────────────────────
Route::get('/influencer/apply',  [InfluencerController::class, 'apply'])->name('influencer.apply');
Route::post('/influencer/apply', [InfluencerController::class, 'submitApplication'])->name('influencer.apply.submit');

// ── Homepage A/B test
Route::get('/home2', fn() => view('welcome-2'))->name('home2');
Route::get('/workers', fn() => view('workers'))->name('workers.page');
Route::get('/w2/{slug}', [WorkerPublicController::class, 'show2'])->name('workers.public.show2');

require __DIR__.'/auth.php';
