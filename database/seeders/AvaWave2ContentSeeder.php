<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Seeds worker_content_pages / worker_content_faqs from the approved AVA SEO
// Market Map, Wave 2 (Pages #11-23). Run once per page as copy is approved:
//   php artisan db:seed --class=Database\\Seeders\\AvaWave2ContentSeeder
// Idempotent: upserts by url_path so re-running updates rather than duplicates.
class AvaWave2ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPage(
            worker: 'ava',
            tier: 'Tier 2',
            pageFamily: 'Lifecycle Solution',
            urlPath: 'approval/renewal-approval-workflow',
            lifecycleStage: 'Human Review',
            primaryQuery: 'software renewal approval workflow',
            secondaryQueries: ['renewal approval automation', 'renewal review workflow'],
            seoTitle: 'Renewal Approval Workflow with Human Control | AVA by UNITELO',
            metaDescription: "AVA coordinates renewal work through required human review, then keeps the approved transaction moving toward fulfillment, records and completion.",
            h1: 'Renewal Approval Workflow That Keeps Humans in Control',
            ctaLabel: 'Put AVA on Renewal Approvals',
            ctaHeadline: 'Give your team decisions, not renewal administration.',
            ctaSubtext: "Your people should remain in control of consequential decisions. That doesn't mean they should also carry every repetitive operational step required to get a renewal to the decision point and then move it toward completion. AVA can own that responsibility.",
            ctaRoute: 'register',
            body: $this->page11Body(),
            faqs: $this->page11Faqs(),
            heroImage: 'images/ava-selfie.webp',
            heroImageAlt: "AVA, UNITELO's AI Renewal Worker",
            faqImage: 'images/ava-desk.webp',
            faqImageAlt: 'AVA working at her desk, managing renewal operations',
        );
    }

    // Named-route helper so internal links survive any future URL change,
    // usable inside heredoc body strings via {$this->pageUrl(...)} interpolation.
    private function pageUrl(string $worker, string $path): string
    {
        return route('worker.content', [$worker, $path]);
    }

    private function seedPage(
        string $worker,
        string $tier,
        string $pageFamily,
        string $urlPath,
        string $lifecycleStage,
        string $primaryQuery,
        array $secondaryQueries,
        string $seoTitle,
        string $metaDescription,
        string $h1,
        string $ctaLabel,
        string $ctaHeadline,
        string $ctaSubtext,
        string $ctaRoute,
        string $body,
        array $faqs,
        ?string $heroImage = null,
        ?string $heroImageAlt = null,
        ?string $faqImage = null,
        ?string $faqImageAlt = null,
    ): void {
        DB::table('worker_content_pages')->updateOrInsert(
            ['url_path' => $urlPath],
            [
                'worker_slug'              => $worker,
                'tier'                     => $tier,
                'page_family'              => $pageFamily,
                'primary_lifecycle_stage'  => $lifecycleStage,
                'primary_query'            => $primaryQuery,
                'secondary_queries'        => json_encode($secondaryQueries),
                'seo_title'                => $seoTitle,
                'meta_description'         => $metaDescription,
                'h1'                       => $h1,
                'body'                     => $body,
                'hero_image'               => $heroImage,
                'hero_image_alt'           => $heroImageAlt,
                'faq_image'                => $faqImage,
                'faq_image_alt'            => $faqImageAlt,
                'cta_label'                => $ctaLabel,
                'cta_headline'             => $ctaHeadline,
                'cta_subtext'              => $ctaSubtext,
                'cta_route'                => $ctaRoute,
                'publishing_wave'          => 'Wave 2',
                'status'                   => 'published',
                'updated_at'               => now(),
                'created_at'               => now(),
            ]
        );

        $id = DB::table('worker_content_pages')->where('url_path', $urlPath)->value('id');

        DB::table('worker_content_faqs')->where('page_id', $id)->delete();
        foreach ($faqs as $i => $faq) {
            DB::table('worker_content_faqs')->insert([
                'page_id'    => $id,
                'question'   => $faq[0],
                'answer'     => $faq[1],
                'sort_order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function page11Body(): string
    {
        return <<<HTML
<p><strong>Automate the renewal process without automating the decisions that belong to people.</strong></p>
<p>A renewal has been detected. The customer is known. The asset is identified. The responsible contact has been found. The renewal requirements have been understood.</p>
<p>Now someone needs to decide. Should the renewal proceed? Is the communication ready? Does the organization approve the next action?</p>
<p>This is where automation needs a boundary.</p>
<p>AVA is an AI Worker for Renewal Operations designed to move renewal work to the appropriate human review point without taking authority that belongs to people. Once the human decision is made, AVA keeps the renewal transaction moving toward fulfillment, records, evidence, future scheduling, and completion.</p>
<p><strong>AVA owns the process. Humans own the decisions.</strong></p>

<h2>Why renewal approvals become bottlenecks</h2>
<p>Renewal approval sounds simple: send it to someone for approval. In practice, the approval sits inside a much larger operational process. Before someone can make a useful decision, they may need to know what is renewing, which customer is involved, which asset or obligation is involved, when the deadline is, what the renewal requires, who is responsible, what communication has been prepared, and what happens after approval.</p>
<p>If that context is fragmented across inboxes, spreadsheets, documents, vendor portals, and individual employees, the approver inherits the operational problem. The result is familiar: "Can you remind me what this is?" "Which customer is this for?" "When does it expire?" "Who owns this?" "What exactly am I approving?"</p>
<p>The approval isn't necessarily the bottleneck. The missing context around the approval is.</p>

<h2>What is a renewal approval workflow?</h2>
<p>A renewal approval workflow is the controlled process that brings renewal work to the appropriate human review point before consequential action proceeds. A good workflow should establish what requires review (the renewal transaction and proposed next action should be clear), why review is required (the approver should understand the context surrounding the renewal), who needs to review (responsibility for the human decision should be defined), and what happens after the decision (approved work should continue through the renewal lifecycle rather than disappearing back into email).</p>
<p>The objective is not merely to collect an "Approved" response. It is to place human authority inside an accountable operational process.</p>

<h2>Human Review is part of AVA's lifecycle</h2>
<p>AVA's renewal lifecycle is: Detect, Understand, Identify Customer, Identify Asset, Identify Contact, Prepare Communication, Human Review, Fulfillment, Record Outcome, Schedule Next Renewal, Archive, Complete.</p>
<p>Human Review sits directly between preparation and execution. That position is intentional. AVA can do significant operational work before a person needs to intervene. But when the process reaches a point requiring human authority, the Worker does not silently cross that boundary. The renewal moves to review.</p>

<h2>AVA prepares the work before asking for a decision</h2>
<p>Human-in-the-loop automation works best when the human is asked to make a decision, not reconstruct the entire process. Before Human Review, AVA's lifecycle can already have established important renewal context: the renewal has been detected, AVA has worked to understand the renewal intent, the customer has been identified, the renewable asset has been identified, and the responsible contact has been identified. Where communication is required, AVA can prepare the communication.</p>
<p>The person reviewing the renewal therefore enters at a defined decision point in the lifecycle. AI handles interpretation and preparation. Humans provide authority. The system maintains operational consistency.</p>

<h2>What does AVA decide?</h2>
<p>AVA uses AI where renewal work contains uncertainty. She can help understand renewal intent, identify renewable assets, identify customers and contacts, classify renewal requests, understand business context, generate personalized communications, and recommend appropriate next actions.</p>
<p>Those are interpretation and preparation responsibilities. They help move the transaction forward. They do not give AVA unrestricted decision authority.</p>

<h2>What do humans decide?</h2>
<p>AVA intentionally does not own financial approval, vendor selection, contract negotiation, payment authorization, legal decisions, or executive approval. These remain human responsibilities or responsibilities for other specialized Workers.</p>
<p>This creates a clear operating model: AVA can coordinate the renewal, prepare the work, maintain workflow state, and bring the transaction to the appropriate human decision point. But AVA does not turn operational automation into autonomous authority.</p>

<h2>Approval does not mean autonomous payment</h2>
<p>Consider a software subscription approaching renewal. AVA detects the renewal work. The subscription is identified. The customer or organizational context is established. The responsible contact is known. The renewal moves to Human Review. A person approves the operational next step.</p>
<p>That does not mean AVA suddenly has authority to execute a payment. Payment authorization and automatic payment execution are separate responsibilities. They remain outside AVA's Version 1 scope. The approval workflow controls the renewal process. It does not erase financial controls.</p>

<h2>Approval does not mean contract negotiation</h2>
<p>Some renewals involve agreements. Terms may change. Pricing may change. Someone may want to renegotiate. AVA can keep the renewal transaction operationally accountable while that happens. But she does not negotiate the contract. Contract negotiation remains outside her responsibility.</p>
<p>That creates an important separation: AVA identifies that a renewal requires a decision before the deadline. The human decides which terms are acceptable. AVA continues the operational lifecycle once the decision has been made. AVA coordinates around the decision. She doesn't make the commercial decision herself.</p>

<h2>Approval does not mean legal approval</h2>
<p>Renewal notices and agreements can also contain legal questions. AVA can help interpret renewal intent and business context. She can identify that renewal work requires attention. She can bring the transaction to Human Review. But she does not provide legal approval. She does not decide whether contractual language is legally acceptable. She does not replace counsel or another authorized decision-maker. Legal authority stays human.</p>

<h2>Approval does not mean vendor selection</h2>
<p>A renewal may cause an organization to reconsider a vendor. That is a legitimate business decision. It is not AVA's decision. Vendor selection remains outside AVA's responsibility. AVA owns the Renewal Operations surrounding the decision. The organization owns the vendor decision.</p>

<h2>A software renewal approval workflow</h2>
<p>A SaaS subscription approaches renewal. The organization needs to determine what happens next. AVA's operational lifecycle can provide structure: Detect (the renewal enters the workflow), Understand (AVA interprets the renewal and its context), Identify (the relevant customer, subscription, and responsible contact are established), Prepare (required renewal communication can be drafted), Human Review (an authorized person reviews the proposed action), Fulfillment (the approved renewal continues toward its operational outcome), Record (the result is recorded), Schedule (the next renewal cycle is established), and Archive (supporting evidence is preserved). Complete: no operational work remains.</p>
<p>Approval is therefore one stage of the renewal. Not the entire renewal.</p>

<h2>A contract or maintenance agreement renewal approval workflow</h2>
<p>Recurring agreements can create a similar operational problem. A maintenance agreement approaches renewal. The organization may need someone to review the situation. There may be commercial or contractual decisions. AVA can organize the renewal transaction around those decisions without taking them over. She can maintain the operational lifecycle. Humans can provide contractual, financial, legal, or executive authority where necessary. This allows organizations to improve renewal operations without confusing process automation with decision automation.</p>

<h2>Customer-facing renewal communication requires review</h2>
<p>AVA can prepare personalized renewal communications. But Version 1 requires Human Review before customer-facing communication proceeds. This is a deliberate operating boundary. The Worker can use renewal context to help prepare the message. The person reviews what will be communicated. Only after approval does the workflow continue. This creates a useful balance: less repetitive preparation for employees, and continued human authority over external communication.</p>

<h2>What happens after approval matters</h2>
<p>Many approval processes focus almost entirely on getting the decision: pending, then approved. Then responsibility becomes fragmented again. Someone has to remember what happens next.</p>
<p>AVA's lifecycle doesn't end at approval. After Human Review comes Fulfillment (the renewal must reach the required operational outcome), Record Outcome (organizational records must reflect what happened), Schedule Next Renewal (future monitoring must be re-established), Archive (supporting evidence must be preserved), and Complete (no operational work should remain).</p>
<p>This is one of the most important distinctions in AVA's model: <strong>approval is a checkpoint. Completion is the outcome.</strong></p>

<h2>An approved renewal can still fail</h2>
<p>Imagine this: the renewal is detected, everyone agrees it should proceed, the manager approves it. Then the transaction stalls. The required follow-up isn't completed. The record isn't updated. The next cycle isn't scheduled. The approval technically happened. The renewal operation still failed.</p>
<p>AVA therefore does not define success as "approved." She defines success around the completed business obligation.</p>

<h2>One renewal remains one transaction through approval</h2>
<p>A renewal may involve multiple emails, reminders, documents, contacts, invoices, and approvals. AVA keeps that activity connected to one underlying renewal transaction. One renewal equals one transaction. That transaction remains the unit of accountability throughout the lifecycle. The approval does not become a disconnected task. It remains part of the renewal.</p>

<h2>Know what is waiting for human review</h2>
<p>A useful approval workflow should make the decision point operationally clear. The organization should be able to distinguish between work that is being understood, being prepared, waiting for human review, moving through fulfillment, being recorded, and being completed. The UNITELO platform maintains workflow state while AVA handles the interpretation required around renewal work. That separation helps keep the process predictable and auditable.</p>

<h2>Human-in-the-loop renewal automation</h2>
<p>"Human in the loop" should mean more than placing an approval button inside an automated workflow. The important question is: what responsibility belongs to the Worker, and what authority belongs to the human?</p>
<p>For AVA, the Worker owns Renewal Operations. The human retains consequential decision authority. That means organizations can automate repetitive operational responsibility without handing every business decision to AI. This is particularly important for renewals because they can touch financial commitments, customer relationships, vendor relationships, contracts, legal considerations, and executive decisions. AVA can carry the operational burden around those decisions while keeping the decisions themselves under human control.</p>

<h2>Renewal approval should be auditable</h2>
<p>Approval is part of the history of a renewal transaction. AVA's lifecycle continues beyond Human Review into records and archive. That creates continuity between what was reviewed, what happened afterward, what outcome was recorded, what evidence was preserved, and what future renewal was scheduled. The objective isn't generic enterprise governance. It is renewal-specific operational traceability.</p>

<h2>Renewal approval vs renewal authorization</h2>
<p>These terms can overlap, but they should not be allowed to blur AVA's authority. AVA can coordinate a renewal to the appropriate review or authorization point. The person or organizational policy determines whether the action is authorized. AVA then operates according to the resulting workflow state. The Worker does not grant herself authority because the workflow exists.</p>

<h2>Renewal approval vs renewal completion</h2>
<p>This is the distinction that matters most. Approval means a human decision required by the process has been made. Completion means the underlying renewal obligation has reached its successful outcome, organizational records reflect that outcome, evidence has been archived, the Renewal Register has been updated, the next cycle has been scheduled, and no operational work remains.</p>
<p>Approval can be necessary. But approval is not success. A completed renewal is success.</p>

<h2>Related Renewal Operations resources</h2>
<p>Human Review is one stage inside a larger responsibility. See the complete operational lifecycle in <a href="{$this->pageUrl('ava', 'renewal-management')}">Renewal Management Software</a>, see how detection starts the loop in <a href="{$this->pageUrl('ava', 'detect/renewal-tracking-software')}">Renewal Tracking &amp; Reminder Software</a>, or see how it closes after approval in <a href="{$this->pageUrl('ava', 'scheduling/renewal-calendar')}">Renewal Calendar &amp; Scheduling Software</a>.</p>

<h2>Give your team decisions, not renewal administration</h2>
<p>Your people should remain in control of consequential decisions. That doesn't mean they should also carry every repetitive operational step required to get a renewal to the decision point and then move it toward completion.</p>
<p>AVA can own that responsibility. She detects. She understands. She organizes. She prepares. She brings the renewal to Human Review. Your people decide. AVA continues the process.</p>
HTML;
    }

    private function page11Faqs(): array
    {
        return [
            ['What is a renewal approval workflow?', "A renewal approval workflow is the controlled process for bringing renewal work to an appropriate human review point before consequential action proceeds. With AVA, Human Review is one stage inside the broader Renewal Operations lifecycle."],
            ['Does AVA automatically approve renewals?', "No. AVA coordinates the renewal process, but consequential decision authority remains with people."],
            ['Is Human Review required with AVA?', "Yes. Human Review is required in AVA Version 1, and no customer-facing communication proceeds without approval."],
            ['Can AVA approve payments?', "No. Financial approval, payment authorization, financial authorization, and automatic payment execution are outside AVA's responsibility."],
            ['Can AVA negotiate renewal terms?', "No. Contract negotiation is explicitly outside AVA's responsibility."],
            ['Can AVA make legal decisions about a renewal?', "No. Legal decisions and legal approval remain outside AVA's authority."],
            ['Can AVA choose a vendor?', "No. Vendor selection is outside AVA's responsibility."],
            ['What can AVA do before Human Review?', "AVA can perform renewal-related work within her supported capabilities, including renewal detection and classification, customer identification, contact matching, draft generation, and renewal tracking. AI can also help understand renewal intent, identify assets, understand business context, generate personalized communications, and recommend appropriate next actions."],
            ['What happens after someone approves a renewal?', "AVA's lifecycle continues into fulfillment, recording the outcome, scheduling the next renewal, archiving supporting evidence, and completing the transaction."],
            ['Does approval mean the renewal is complete?', "No. Approval is a lifecycle stage. AVA considers the renewal complete only when the underlying business obligation has been successfully renewed, records reflect the outcome, supporting evidence is archived, the Renewal Register is updated, the next renewal cycle is scheduled, and no operational work remains."],
            ['Can AVA prepare customer renewal communications for approval?', "Yes. Draft generation and personalized communication preparation are supported capabilities. Customer-facing communication requires human approval before proceeding."],
            ['Why use AI if humans still approve the renewal?', "Because the repetitive operational work surrounding the decision still contains significant uncertainty and coordination. AVA uses AI to interpret renewal intent, identify assets, customers and contacts, understand context, prepare communications, and recommend next actions. Humans retain authority where authority is required."],
            ['Is AVA an autonomous renewal decision-maker?', "No. AVA is an AI Worker for Renewal Operations. Her role is to own and coordinate the operational process while humans retain control of consequential decisions."],
        ];
    }
}
