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

        $this->seedPage(
            worker: 'ava',
            tier: 'Tier 2',
            pageFamily: 'Lifecycle Solution',
            urlPath: 'ownership/renewal-ownership-responsible-contacts',
            lifecycleStage: 'Identify Contact',
            primaryQuery: 'renewal ownership tracking',
            secondaryQueries: ['responsible contact tracking software', 'renewal accountability tracking'],
            seoTitle: 'Renewal Ownership & Responsible Contact Tracking | AVA by UNITELO',
            metaDescription: "AVA connects every renewal to the right customer, asset, and responsible contact, keeping accountability in place even as people and roles change.",
            h1: "Renewal Ownership & Responsible Contact Tracking That Survives Employee Turnover",
            ctaLabel: 'Give Every Renewal an Owner',
            ctaHeadline: 'Give renewal responsibility a permanent home.',
            ctaSubtext: "Important recurring obligations shouldn't depend on somebody remembering who handled them last year, or disappear between a shared inbox, spreadsheet, calendar, and customer record. AVA keeps each renewal connected to its business context and moves the transaction through the lifecycle while your people retain control of consequential decisions.",
            ctaRoute: 'register',
            body: $this->page12Body(),
            faqs: $this->page12Faqs(),
            heroImage: 'images/ava-life.webp',
            heroImageAlt: 'AVA working at her desk, managing renewal ownership',
            faqImage: 'images/ava-stand.webp',
            faqImageAlt: "AVA, UNITELO's AI Renewal Operations Worker",
        );

        $this->seedPage(
            worker: 'ava',
            tier: 'Tier 2',
            pageFamily: 'Lifecycle Solution',
            urlPath: 'understand/renewal-notice-dates-terms',
            lifecycleStage: 'Understand',
            primaryQuery: 'renewal notice period',
            secondaryQueries: ['renewal date vs expiration date', 'contract renewal notice period'],
            seoTitle: 'Renewal Notice, Dates & Terms Tracking | AVA by UNITELO',
            metaDescription: "AVA turns renewal notices, dates, and terms into structured, accountable work, without acting as legal counsel or interpreting contractual rights.",
            h1: 'Renewal Notice, Dates & Terms Tracking That Turns Dates Into Work',
            ctaLabel: 'Have AVA Understand Renewal Work',
            ctaHeadline: "Don't let the expiration date be the first time the renewal gets attention.",
            ctaSubtext: "Renewal work begins before completion. The notice needs to be detected, the context understood, the customer and asset identified, the responsible contact established, and the work moved toward review and fulfillment. AVA gives that process an operational owner.",
            ctaRoute: 'register',
            body: $this->page13Body(),
            faqs: $this->page13Faqs(),
            heroImage: 'images/ava-desk.webp',
            heroImageAlt: 'AVA focused at her laptop, understanding renewal notices',
            faqImage: 'images/ava-skyline.webp',
            faqImageAlt: 'AVA in the UNITELO boardroom',
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
<p>See the two stages right before this one: <a href="{$this->pageUrl('ava', 'understand/renewal-notice-dates-terms')}">Renewal Notice, Dates &amp; Terms Tracking</a> and <a href="{$this->pageUrl('ava', 'ownership/renewal-ownership-responsible-contacts')}">Renewal Ownership &amp; Responsible Contact Tracking</a>.</p>

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

    private function page12Body(): string
    {
        return <<<HTML
<p><strong>Give Every Renewal a Responsible Owner</strong></p>
<p>A renewal has been detected. The deadline is known. The asset has been identified. The customer is known. But there is still one critical question: who needs to act?</p>
<p>Renewal work becomes dangerous when responsibility is unclear. It sits in a shared inbox. It gets forwarded between employees. Someone assumes someone else is handling it. The employee who originally managed the customer has left. The vendor contact has changed. The customer contact has changed. Or the renewal exists in a spreadsheet with a date, but no clear person connected to the next action.</p>
<p>AVA is an AI Worker for Renewal Operations designed to keep renewal work connected to the right customer, the right asset, and the responsible contact as the transaction moves toward completion.</p>
<p>Every renewal should have context. Every renewal should have accountability.</p>

<h2>Why renewals become ownerless</h2>
<p>Renewals rarely begin as clean, structured tasks. They can arrive through inbox. They can originate from an asset being monitored. They can be triggered manually. And the information required to complete the renewal may be spread across different systems and different people.</p>
<p>One employee knows the customer. Another manages the asset. Someone else receives vendor emails. A manager provides approval. Another person handles the fulfillment step. When there is no clear operational owner connecting these pieces, renewal responsibility becomes fragmented. That fragmentation creates a dangerous assumption: someone must be handling it. Sometimes nobody is.</p>

<h2>What does renewal ownership mean?</h2>
<p>Renewal ownership means establishing accountability for the work required to move a recurring obligation through its lifecycle. It answers questions such as who is connected to this renewal, who should receive or review communication, who needs to provide information, who needs to make a decision, and who needs to take the next operational action.</p>
<p>But ownership does not mean every person connected to the renewal has the same authority. A responsible contact may be relevant to the transaction without having financial, legal, or executive decision-making authority. That distinction matters. AVA helps establish operational responsibility. She does not invent organizational authority.</p>

<h2>Customer, Asset, Contact, Transaction</h2>
<p>AVA's renewal model becomes more useful when four pieces of context are connected.</p>
<h3>Customer</h3>
<p>Who does this renewal belong to? For organizations managing renewals across multiple customers, this prevents an incoming renewal from becoming an isolated notice with no business context.</p>
<h3>Asset</h3>
<p>What exactly is renewing? A domain? SSL certificate? Hosting service? SaaS subscription? Insurance policy? License? Maintenance agreement? The renewal must be connected to the actual recurring obligation.</p>
<h3>Contact</h3>
<p>Who is relevant to the action or communication? This could involve the appropriate customer, vendor, or internal contact depending on the renewal and the next required step.</p>
<h3>Transaction</h3>
<p>What renewal is the organization accountable for completing? AVA treats one renewal as one transaction. That transaction becomes the unit of accountability even when the work surrounding it involves multiple people, emails, documents, reminders, approvals, or other activities. The model is: Customer, Asset, Contact, Renewal Transaction. This turns a scattered renewal notice into structured operational work.</p>

<h2>Identifying the contact is a lifecycle stage</h2>
<p>AVA's renewal lifecycle is: Detect, Understand, Identify Customer, Identify Asset, Identify Contact, Prepare Communication, Human Review, Fulfillment, Record Outcome, Schedule Next Renewal, Archive, Complete.</p>
<p>Identifying the contact happens before communication is prepared. That sequence is important. AVA should not simply generate a renewal email because a deadline exists. The Worker first needs the business context surrounding the renewal: who is the customer, what is the asset, who is the appropriate contact. Only then should communication preparation move forward.</p>

<h2>A renewal date without ownership is still a risk</h2>
<p>Imagine a spreadsheet containing "Domain renewal, October 14." The date is useful. But it leaves important questions unanswered: which domain, which customer, who is responsible for the relationship, who should be contacted, who needs to approve the next action, has anything already happened. A date creates visibility. It does not automatically create accountability. AVA's job is broader than storing renewal dates. She helps move the obligation into an accountable transaction.</p>

<h2>A shared inbox is not a renewal owner</h2>
<p>Shared inboxes can be useful sources of renewal activity. But receiving a message does not mean someone owns the underlying business obligation. A renewal notice can arrive, someone can read it, someone can flag it, someone can forward it, and the renewal can still fail. The inbox contains the communication. AVA's lifecycle represents the work. That difference is fundamental. AVA can monitor supported sources such as Gmail for renewal activity, but the objective is not simply to manage messages. The objective is to identify the renewal and move it toward completion.</p>

<h2>A calendar reminder is not a renewal owner</h2>
<p>A calendar can tell someone "renewal due in 30 days." That is useful. But the calendar does not necessarily establish the correct customer, the exact renewable asset, the responsible contact, the required communication, the human review state, the fulfillment outcome, the renewal record, the evidence, or the next renewal cycle. A reminder can tell someone that work exists. AVA is designed to own the operational lifecycle around that work.</p>

<h2>Contact identification makes communication more useful</h2>
<p>AVA supports preparing personalized renewal communications. But personalization requires context. A useful renewal communication should not be created in isolation from the transaction. AVA can use the context surrounding the renewal to help prepare the communication: who the customer is, what is renewing, who the relevant contact is, what the renewal is about, and what action may be required. This is why Identify Contact comes before Prepare Communication. The communication should emerge from the renewal context, not the other way around.</p>

<h2>The responsible contact is not automatically the approver</h2>
<p>This distinction is essential. The person associated with a renewal may be the appropriate contact for communication or operational coordination. That does not automatically give that person authority to approve payments, contract terms, vendor selection, legal decisions, financial commitments, or executive decisions. AVA separates contact identification from decision authority. She can determine who is relevant to the renewal process. The organization's policies determine who has authority to make consequential decisions.</p>

<h2>Ownership is different from authority</h2>
<p>Think of renewal operations as having two different questions.</p>
<h3>Who owns the work?</h3>
<p>This is an operational accountability question. Someone, or in AVA's case a Worker, must ensure the renewal continues moving through its lifecycle.</p>
<h3>Who has authority to decide?</h3>
<p>This is a governance question. A human may need to approve a communication, authorize a financial commitment, make a legal decision, select a vendor, or negotiate a contract. AVA can own the operational process without owning those decisions. That gives organizations a powerful model: the Worker owns the responsibility, humans retain the authority.</p>

<h2>AVA can own the renewal even when multiple people are involved</h2>
<p>Real renewals rarely involve one person from beginning to end. A single renewal might involve a customer contact, an internal account owner, a technical employee, a manager, a finance employee, a vendor contact, and an executive. The presence of multiple people should not cause the underlying renewal to fragment into unrelated tasks. AVA keeps the renewal transaction as the central unit of accountability. People can enter the process when their participation is required. The transaction remains one renewal.</p>

<h2>One renewal equals one unit of accountability</h2>
<p>A renewal might generate three reminder emails, two internal conversations, one invoice, several documents, a manager's approval, a customer response, and a fulfillment action. Those are activities surrounding the renewal. They are not seven different renewals. AVA treats the underlying obligation as one transaction. That gives the organization a clearer question to ask: is this renewal complete? Instead of did someone send the email, did someone update the spreadsheet, did someone approve the request, did someone reply. Individual activities matter. But the business outcome matters more.</p>

<h2>Renewal ownership survives employee handoffs</h2>
<p>One of the weaknesses of person-dependent renewal management is that organizational memory can leave with the person. An employee manages a customer. They know the domain, the hosting service, which person at the customer normally approves renewals, and which vendor emails matter. Then they change roles or leave the organization. The recurring obligations remain. The institutional context may not. AVA's operating model is designed to reduce that dependence on individual memory. Renewal responsibility belongs to the lifecycle, not to the memory of whichever employee happened to manage the last cycle.</p>

<h2>Client renewals need customer-level accountability</h2>
<p>The problem becomes even more significant when an organization manages renewals for customers. A Managed Service Provider may have recurring obligations across many client environments. A digital agency may continue managing domains, SSL certificates, hosting services, software, licenses, or maintenance-related obligations long after the original project ended. A hosting provider may manage recurring services across many accounts. Now the organization isn't simply asking what renewals do we have. It needs to ask which customer does each renewal belong to, what asset is renewing for that customer, who is the appropriate contact, and what is the current state of the transaction. AVA's customer, asset, and contact stages give that work a consistent structure.</p>

<h2>Renewal ownership for Managed Service Providers</h2>
<p>Managed Service Providers can face a particularly dense ownership problem. There may be many customers. Each customer may have multiple renewable assets. Each asset may have different deadlines. Each customer may have different contacts. Different people may need to participate at different stages. Trying to preserve all of that through inboxes and employee memory creates unnecessary operational risk. AVA provides a consistent renewal transaction model across the portfolio: Customer, Asset, Contact, Renewal Transaction. The specific people involved can change. The operational responsibility remains. See <a href="{$this->pageUrl('ava', 'industries/managed-service-providers')}">Renewal Management for MSPs</a>.</p>

<h2>Renewal ownership for digital agencies</h2>
<p>Digital agencies often encounter the same problem from a different direction. A project ends. The recurring infrastructure does not. The agency may still be connected to domains, SSL certificates, hosting, software subscriptions, licenses, and maintenance agreements. Months or years later, a renewal arrives. The original project manager may be gone. The client contact may have changed. The person who purchased the service may no longer remember it. AVA helps move those recurring obligations away from project memory and into a repeatable renewal lifecycle. The project can end without the renewal responsibility disappearing. See <a href="{$this->pageUrl('ava', 'industries/digital-agencies')}">Renewal Management for Digital Agencies</a>.</p>

<h2>What happens after AVA identifies the responsible contact?</h2>
<p>Contact identification is not the outcome. It prepares the transaction for the next stage. Once the appropriate context is established, AVA can move toward Prepare Communication. If customer-facing communication is required, AVA can prepare a personalized draft using the renewal context. Then the transaction reaches Human Review. No customer-facing communication proceeds without required human approval. After review, AVA continues tracking the transaction through fulfillment, records the outcome, schedules the next renewal, archives supporting evidence, and completes the transaction. The contact is therefore one piece of a larger operating system.</p>

<h2>Renewal ownership should not end when someone responds</h2>
<p>A common operational mistake is treating human response as completion. Someone replies to the email. Someone says they are handling it. Someone approves the request. Someone acknowledges the reminder. Those are useful state changes. They do not necessarily mean the renewal has been completed. AVA's accountability model remains centered on the business obligation. The transaction stays open until the renewal has reached its required operational outcome and the completion requirements have been satisfied.</p>

<h2>What AVA owns</h2>
<p>AVA owns the operational renewal lifecycle. That includes responsibilities such as monitoring renewal activity, detecting renewal requests, monitoring deadlines, understanding renewal context, identifying customers, identifying renewable assets, matching contacts, preparing communications, coordinating Human Review, tracking fulfillment, updating renewal records, scheduling future renewal activity, and maintaining audit history. The objective is continuous operational ownership.</p>

<h2>What AVA does not own</h2>
<p>AVA's ownership has deliberate boundaries. AVA does not own financial approval, vendor selection, contract negotiation, payment authorization, legal decisions, executive approval, CRM ownership, or accounting ownership. AVA can coordinate renewal work that intersects with those responsibilities. She does not take those responsibilities over. This is the difference between an AI Worker with a defined business responsibility and an AI system that simply tries to automate everything it encounters.</p>

<h2>Renewal ownership without replacing your CRM</h2>
<p>Customer information may already exist in systems your organization uses. AVA's responsibility is not to become the organization's generic CRM. Her job is renewal-specific. She needs enough customer and relationship context to perform Renewal Operations. The CRM remains the CRM. AVA remains the Renewal Operations Worker.</p>

<h2>Renewal ownership without replacing your people</h2>
<p>AVA is not designed to remove humans from renewal decisions. She removes the need for important recurring obligations to depend entirely on human memory and repetitive coordination. People still provide authority. People still make consequential decisions. People can still manage customer and vendor relationships. AVA provides continuous operational ownership around the renewal itself.</p>

<h2>From ownerless renewal to accountable transaction</h2>
<p>Before AVA, a renewal might look like this: an email arrived, someone forwarded it, a reminder was created, a spreadsheet was updated, someone said they would handle it, then everyone moved on.</p>
<p>With AVA, the operating question becomes different: what renewal transaction does this represent? Which customer? Which asset? Which contact? What needs to happen? What is its current lifecycle state? What decision is required? Has fulfillment occurred? Was the outcome recorded? Is the next cycle scheduled? Is the evidence archived? Is the transaction actually complete?</p>
<p>That is renewal ownership.</p>

<h2>Related Renewal Operations resources</h2>
<p>Ownership builds on the same lifecycle used across every AVA page. See the complete operational lifecycle in <a href="{$this->pageUrl('ava', 'renewal-management')}">Renewal Management Software</a>, see the stage right before this one in <a href="{$this->pageUrl('ava', 'understand/renewal-notice-dates-terms')}">Renewal Notice, Dates &amp; Terms Tracking</a>, see how ownership scales across a customer portfolio in <a href="{$this->pageUrl('ava', 'customers/client-renewal-management')}">Client &amp; Customer Renewal Management</a>, or see the decision point that follows in <a href="{$this->pageUrl('ava', 'approval/renewal-approval-workflow')}">Renewal Approval Workflow</a>.</p>

<h2>Give renewal responsibility a permanent home</h2>
<p>Important recurring obligations should not depend on somebody remembering who handled them last year. They should not become ownerless because an employee changes roles. They should not disappear between a shared inbox, spreadsheet, calendar, and customer record.</p>
<p>AVA gives Renewal Operations a defined owner. She keeps each renewal connected to its business context and moves the transaction through the lifecycle while your people retain control of consequential decisions.</p>
<p>Customer. Asset. Contact. Transaction. Completion.</p>
HTML;
    }

    private function page12Faqs(): array
    {
        return [
            ['What is renewal ownership?', "Renewal ownership is the operational responsibility for ensuring a renewal continues moving through its required lifecycle rather than depending on disconnected reminders, inboxes, or individual memory."],
            ['What is a renewal owner?', "A renewal owner is the person or Worker responsible for maintaining accountability around the renewal process. With AVA, the Worker owns Renewal Operations while humans retain authority over consequential decisions."],
            ['How does AVA identify the responsible contact?', "Contact matching and responsible-contact identification are supported AVA capabilities. AVA uses renewal context to help associate the transaction with the relevant customer, asset, and contact required for the next stage of work."],
            ['Is the renewal contact always the person who approves it?', "No. A contact may be operationally relevant without having financial, legal, contractual, or executive authority. AVA separates contact identification from decision authority."],
            ['Can AVA manage renewals across multiple customers?', "AVA is designed for organizations with recurring customer renewals, including Managed Service Providers, IT service companies, digital agencies, hosting providers, professional service firms, and other organizations managing recurring obligations."],
            ['What types of renewable assets can AVA manage?', "AVA Version 1 supports renewal work involving domains, SSL certificates, hosting services, SaaS subscriptions, insurance policies, licenses, and maintenance agreements."],
            ['Does AVA replace our CRM?', "No. CRM ownership is outside AVA's responsibility. AVA uses customer and relationship context specifically to perform Renewal Operations."],
            ['Does AVA replace account managers or Customer Success teams?', "No. AVA owns Renewal Operations. Customer relationship management, commercial retention strategy, sales responsibilities, and broader Customer Success responsibilities are separate functions."],
            ['Can AVA prepare communication once the contact is identified?', "Yes. Preparing personalized renewal communications is part of AVA's lifecycle. Customer-facing communication requires Human Review before proceeding."],
            ['Can AVA approve a renewal?', "AVA coordinates Human Review but does not take over consequential authority that belongs to people. Financial approval, payment authorization, vendor selection, contract negotiation, legal decisions, and executive approval remain outside AVA's responsibility."],
            ['What happens if the employee who handled the previous renewal leaves?', "AVA's model is designed to make renewal operations less dependent on individual memory. The renewal is maintained as a transaction connected to customer, asset, contact, records, evidence, and future scheduling rather than existing only in one employee's knowledge."],
            ['When does AVA consider a renewal complete?', "A renewal is complete when the obligation has been successfully renewed, records are accurate, supporting evidence is archived, the Renewal Register is updated, the next renewal cycle is scheduled, and no operational work remains."],
        ];
    }

    private function page13Body(): string
    {
        return <<<HTML
<p><strong>Understand Renewal Notices Before the Deadline Becomes the Problem</strong></p>
<p>A renewal notice arrives. It contains a date. Maybe several dates. There may be an expiration date, a renewal date, a notice period, instructions, customer information, asset information, and terms surrounding what happens next.</p>
<p>The message exists. But has the organization actually understood the renewal work it represents?</p>
<p>AVA is an AI Worker for Renewal Operations designed to turn renewal activity into structured, accountable work. She helps understand renewal intent and business context so the organization can identify what is renewing, connect it to the right customer and asset, determine the next operational step, and move the transaction through its lifecycle.</p>
<p>When contractual, financial, legal, or other consequential judgment is required, people remain in control.</p>
<p>Don't just receive the renewal notice. Turn it into accountable renewal work.</p>

<h2>Renewal information is often unstructured</h2>
<p>Renewals do not always arrive as perfectly structured records. One vendor may send "your subscription renews next month." Another may say "your service is approaching expiration." Another message may request action. Another may reference an agreement. Another may contain customer, asset, contact, and renewal information across several paragraphs.</p>
<p>The language varies. The context varies. The format varies. And the meaning of the communication may depend on information surrounding it. That creates a problem for purely deterministic workflows. Before the organization can act consistently, someone, or something, has to understand what the renewal activity represents.</p>

<h2>Receiving a notice is not the same as understanding it</h2>
<p>An inbox can receive a renewal message. A rule can detect a keyword. A calendar can contain a date. A spreadsheet can store an expiration. Those mechanisms create visibility. But renewal operations require context. The organization still needs to answer: is this actually renewal activity, what is renewing, which customer is involved, which asset or obligation is involved, who is the relevant contact, what action appears to be required, and what should happen next. AVA's Understand stage exists to help transform incoming renewal information into operational context.</p>

<h2>Where Understand sits in AVA's lifecycle</h2>
<p>AVA's renewal lifecycle begins: Detect, Understand, Identify Customer, Identify Asset, Identify Contact, Prepare Communication, Human Review, Fulfillment, Record Outcome, Schedule Next Renewal, Archive, Complete.</p>
<p>Detection answers "something may require renewal attention." Understanding begins answering "what does this renewal activity mean operationally?" Only after that context exists should the transaction continue into customer, asset, contact, communication, review, and fulfillment stages.</p>

<h2>Why AVA uses AI for renewal understanding</h2>
<p>Some parts of renewal operations are deterministic: creating a transaction, recording history, updating the Renewal Register, scheduling future work, maintaining workflow state, and enforcing approval policies. Those actions benefit from predictable systems.</p>
<p>But understanding renewal activity can involve uncertainty. Language varies. Customers vary. Assets vary. Contacts vary. Requests vary. Context varies. AVA uses AI where interpretation is required, including identifying assets, identifying customers, identifying contacts, classifying renewal requests, understanding business context, generating personalized communications, and recommending appropriate next actions. The operating principle is: AI handles uncertainty. The UNITELO platform handles deterministic execution.</p>

<h2>Renewal date, expiration date, and notice period are not the same thing</h2>
<p>These concepts can all appear around renewal work, but they answer different operational questions.</p>
<h3>Renewal date</h3>
<p>A renewal date generally identifies when a recurring obligation is expected to renew or enter its next cycle.</p>
<h3>Expiration date</h3>
<p>An expiration date identifies when an existing service, asset, agreement, license, policy, certificate, or other time-bound obligation reaches the end of its current period.</p>
<h3>Notice period</h3>
<p>A notice period can define a window in which action or communication may need to occur before a renewal or expiration event. The exact meaning and consequences can depend on the underlying agreement or business context. For Renewal Operations, the important point is: the expiration date may not be the only date that matters. Operational attention may be required before the final expiration date arrives.</p>

<h2>A renewal deadline should become work, not just a date</h2>
<p>Suppose a renewal is approaching on September 30. Knowing September 30 is useful. But the date alone does not tell the organization what is renewing, which customer is affected, who needs to act, whether communication is required, whether human review is required, whether fulfillment has started, whether the outcome has been recorded, or whether the next cycle has been scheduled. AVA's responsibility is to help move the renewal from date awareness into an accountable lifecycle. The deadline becomes part of a transaction.</p>

<h2>Turn renewal notices into transactions</h2>
<p>One of AVA's core operating principles is: one renewal equals one transaction. The notice is not the transaction. The email is not the transaction. The calendar event is not the transaction. Those are sources or activities surrounding the underlying renewal. Once renewal work has been detected and understood, AVA can create or maintain the renewal as a unit of accountability. That transaction can then accumulate the operational context required to complete the work: customer, asset, contact, communication, review, fulfillment, outcome, evidence, next cycle. Instead of managing disconnected pieces of renewal information, the organization manages the renewal itself.</p>

<h2>Understand the customer behind the renewal</h2>
<p>A renewal notice may contain information that helps establish the customer relationship. AVA's lifecycle explicitly includes Identify Customer after the renewal has been understood. This matters particularly for organizations managing recurring obligations across multiple customers. A notice saying "renewal approaching" is less operationally useful than understanding "this renewal belongs to this customer." That customer context becomes part of the transaction.</p>

<h2>Understand the asset behind the renewal</h2>
<p>AVA also needs to establish what is actually renewing. Version 1 supports renewal work involving domains, SSL certificates, hosting services, SaaS subscriptions, insurance policies, licenses, and maintenance agreements. The same word, "renewal," can therefore represent very different operational work. A domain renewal is not identical to an insurance policy renewal. An SSL certificate is not a maintenance agreement. A SaaS subscription is not a license in every operational context. Understanding what the renewal concerns helps AVA move the transaction into the correct context.</p>

<h2>Understand who is connected to the renewal</h2>
<p>After customer and asset identification comes Identify Contact. The appropriate contact matters because renewal work may require communication, information, review, or another human action. AVA supports contact matching as part of her Version 1 capabilities. This creates a structured relationship: Customer, Asset, Contact, Renewal Transaction. The notice is no longer simply an email containing a deadline. It has become business work with context.</p>

<h2>Renewal terms require careful boundaries</h2>
<p>Renewal communications can contain information about terms. That does not mean AVA should be positioned as autonomous legal counsel. There is an important difference between understanding renewal context and making a legal determination about contractual language. AVA's approved responsibility supports understanding renewal intent and business context. Legal decisions remain outside her responsibility. Contract negotiation remains outside her responsibility. Legal approval remains outside Version 1 scope. That boundary should remain clear wherever renewal terms are involved.</p>

<h2>AVA is not a legal interpretation engine</h2>
<p>AVA should not be hired to answer "is this clause legally enforceable?", "should we accept these contractual terms?", "what legal rights does this notice create?", "should we terminate this agreement?", or "what legal position should we take?" Those questions require authority or expertise outside AVA's Renewal Operations responsibility. AVA's job is narrower and operational: there is renewal activity, understand its business context, organize the work, and move the transaction to the appropriate next stage. If a consequential legal decision is required, the appropriate person makes it.</p>

<h2>Understanding does not mean negotiating</h2>
<p>A renewal notice may reveal that terms or pricing have changed. AVA can help recognize the renewal context. But she does not negotiate the resulting agreement. Vendor selection and contract negotiation remain human responsibilities. AVA can keep the renewal operationally accountable while those decisions happen. The transaction does not need to disappear simply because a human negotiation is occurring. AVA owns the process around the decision. The human owns the decision.</p>

<h2>From renewal notice to responsible contact</h2>
<p>Consider a renewal notice arriving through a supported source. AVA's operating flow can be understood like this: 1. Detect, renewal activity is identified. 2. Understand, AVA interprets the renewal intent and available business context. 3. Identify Customer, the renewal is connected to the relevant customer. 4. Identify Asset, the renewable asset or obligation is established. 5. Identify Contact, the appropriate contact context is established. The organization now has something substantially more useful than "we received a renewal email." It has an accountable renewal transaction.</p>

<h2>From understanding to communication</h2>
<p>Once AVA understands the renewal context and identifies the relevant customer, asset, and contact, the lifecycle can move into Prepare Communication. AVA can use that context to prepare personalized communication. This is important: a renewal message should not simply be generated from a generic prompt. It should reflect the actual transaction, who is involved, what is renewing, what is the relevant context, and what appears to need attention. AVA uses the renewal context to prepare the work. Then the transaction moves to Human Review.</p>

<h2>Human Review protects consequential decisions</h2>
<p>No customer-facing communication proceeds without required human approval in AVA Version 1. That means AI interpretation does not automatically become external action. AVA can understand, organize, and prepare. Then a person reviews. This creates a deliberate boundary between AI interpretation and human authority. The organization gets the operational benefits of AI without treating every AI interpretation as an autonomous business decision.</p>

<h2>Renewal notice tracking for contracts and agreements</h2>
<p>Agreement-related renewals illustrate why this distinction matters. An agreement may contain dates or renewal-related information. Operationally, the organization needs to know that renewal work requires attention. But contractual language can also create questions that exceed AVA's responsibility. AVA can own Renewal Operations around supported recurring obligations. She does not become a general Contract Lifecycle Management system, legal advisor, or contract negotiator. For supported agreement-oriented assets such as maintenance agreements, AVA can manage the renewal operation while humans retain contractual and legal authority.</p>

<h2>Renewal notice tracking for SaaS and software</h2>
<p>Software and SaaS renewals can also generate renewal notices containing dates, account information, subscription context, pricing information, or requested actions. AVA can help turn that renewal activity into an accountable transaction. But she does not automatically make purchasing decisions, vendor-selection decisions, financial approvals, payment authorizations, or contract-negotiation decisions. Those remain outside her responsibility. AVA keeps the renewal process moving around them.</p>

<h2>Renewal notice tracking for client assets</h2>
<p>For Managed Service Providers, digital agencies, hosting providers, and other service organizations, a renewal notice may concern an asset managed on behalf of a customer. Now understanding the notice requires more than recognizing the date. The organization needs to know which customer, which asset, which contact, and which renewal transaction. This is why AVA's lifecycle explicitly separates Understand from Identify Customer from Identify Asset from Identify Contact. Each stage adds operational context.</p>

<h2>Stop treating every renewal email as an isolated task</h2>
<p>Without a defined renewal lifecycle, incoming notices can create a collection of unrelated actions: flag this email, create a calendar reminder, add a spreadsheet row, forward this to someone, create a task, follow up later. Each action may be reasonable. But the underlying renewal can still lack a single unit of accountability. AVA changes the model. The renewal becomes the transaction. Everything else becomes activity surrounding that transaction.</p>

<h2>Renewal understanding should lead to action</h2>
<p>Understanding a renewal is useful only if the organization can do something with that understanding. AVA's lifecycle therefore does not stop at interpretation. After Understand comes customer identification, asset identification, contact identification, communication preparation, Human Review, fulfillment, Record Outcome, future scheduling, Archive, and Complete. This is what separates renewal intelligence from Renewal Operations. Understanding should move work forward.</p>

<h2>Dates should survive beyond the current renewal</h2>
<p>There is another reason renewal dates matter. A successful renewal can create another future renewal. AVA's responsibility therefore includes Schedule Next Renewal. Once the current renewal has been fulfilled and the outcome recorded, future monitoring should be re-established where the obligation continues. The current date becomes part of organizational history. The future date becomes part of the next lifecycle. Renewal scheduling is therefore not administrative cleanup. It is part of completing the current renewal correctly.</p>

<h2>What AVA understands</h2>
<p>Within her approved Renewal Operations responsibility, AVA uses AI to help understand renewal intent, renewable assets, customers, contacts, request classification, business context, communication context, and potential next actions. This is where AI adds value: interpreting variable information that would otherwise require repetitive human review.</p>

<h2>What AVA does not decide</h2>
<p>Understanding information does not grant authority over it. AVA does not own financial approval, vendor selection, contract negotiation, payment authorization, legal decisions, or executive approval. AVA also does not own autonomous purchasing or legal approval. When renewal information reaches one of those boundaries, the appropriate person remains responsible for the decision.</p>

<h2>From unstructured renewal information to controlled execution</h2>
<p>The operating model is simple. AI handles uncertainty: AVA interprets renewal activity and context. The platform handles execution: transactions, workflow state, records, scheduling, notifications, approval policies, and audit history remain deterministic and traceable. Humans retain authority: consequential decisions remain with people. Together, these layers allow renewal operations to become more consistent without requiring every incoming notice to be manually reconstructed from the beginning.</p>

<h2>Related Renewal Operations resources</h2>
<p>Understanding is the stage that turns a notice into work. See the complete operational lifecycle in <a href="{$this->pageUrl('ava', 'renewal-management')}">Renewal Management Software</a>, see how the next stages connect customer, asset, and contact in <a href="{$this->pageUrl('ava', 'ownership/renewal-ownership-responsible-contacts')}">Renewal Ownership &amp; Responsible Contact Tracking</a>, or see the human decision point that follows in <a href="{$this->pageUrl('ava', 'approval/renewal-approval-workflow')}">Renewal Approval Workflow</a>.</p>

<h2>Don't let the expiration date be the first time the renewal gets attention</h2>
<p>Renewal work begins before completion. The notice needs to be detected. The context needs to be understood. The customer needs to be identified. The asset needs to be identified. The responsible contact needs to be established. The work needs to move toward review and fulfillment.</p>
<p>AVA gives that process an operational owner.</p>
HTML;
    }

    private function page13Faqs(): array
    {
        return [
            ['What is a renewal notice period?', "A renewal notice period generally refers to a period associated with communicating or taking action before a renewal or expiration event. The exact meaning and consequences can depend on the relevant agreement and context. AVA should not be treated as legal counsel for interpreting contractual rights or obligations."],
            ['What is the difference between a renewal date and an expiration date?', "A renewal date generally relates to when a recurring obligation enters its next renewal cycle, while an expiration date identifies the end of a current period. The exact meaning depends on the asset or agreement involved."],
            ['Can AVA track renewal deadlines?', "Deadline monitoring is part of AVA's approved Renewal Operations responsibility. AVA can move detected renewal activity into an accountable operational lifecycle rather than treating the deadline as an isolated reminder."],
            ['Can AVA understand renewal notices?', "AVA uses AI to understand renewal intent, identify assets, customers and contacts, classify renewal requests, understand business context, generate personalized communications, and recommend appropriate next actions."],
            ['Does AVA extract legal terms from contracts?', "AVA's approved Version 1 Business Contract does not establish general automated legal clause extraction as a supported capability. AVA should therefore not be represented as a legal clause extraction system unless that capability is separately implemented and approved."],
            ["Can AVA interpret a contract's notice period?", "AVA can understand renewal intent and business context within her Renewal Operations responsibility. However, legal decisions and legal approval remain outside her scope. Where determining the meaning or consequence of contractual language requires legal judgment, that judgment belongs to an authorized human."],
            ['Does AVA negotiate renewal terms?', "No. Contract negotiation is outside AVA's responsibility."],
            ['What happens after AVA understands a renewal notice?', "The lifecycle continues through customer identification, asset identification, contact identification, communication preparation, Human Review, fulfillment, recording the outcome, scheduling the next renewal, archiving evidence, and completion."],
            ['Can AVA automatically send a renewal communication after interpreting a notice?', "No customer-facing communication proceeds without required Human Review in Version 1. AVA can prepare personalized communication, but a person reviews it before it proceeds."],
            ['What renewable assets does AVA support?', "AVA Version 1 supports domains, SSL certificates, hosting services, SaaS subscriptions, insurance policies, licenses, and maintenance agreements."],
            ['Is AVA Contract Lifecycle Management software?', "No. AVA is an AI Worker for Renewal Operations. Her approved scope does not establish general contract authoring, negotiation, legal review, clause management, or full Contract Lifecycle Management functionality."],
            ['Why use AI for renewal notices?', "Renewal activity can vary in language, format, customer context, asset type, contact information, and required next action. AI helps interpret that uncertainty, while the UNITELO platform handles deterministic workflow execution, records, scheduling, approval policies, and audit history."],
        ];
    }
}
