<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Seeds worker_content_pages / worker_content_faqs from the approved AVA SEO
// Market Map, Wave 1. Run once per page as copy is approved:
//   php artisan db:seed --class=Database\\Seeders\\AvaWave1ContentSeeder
// Idempotent — upserts by url_path so re-running updates rather than duplicates.
class AvaWave1ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPage(
            worker: 'ava',
            tier: 'Tier 1',
            pageFamily: 'Category Pillar',
            urlPath: 'renewal-management',
            lifecycleStage: 'Complete',
            primaryQuery: 'renewal management software',
            secondaryQueries: ['business renewal management', 'human in the loop renewal automation'],
            seoTitle: 'Renewal Management Software That Owns the Process | AVA by UNITELO',
            metaDescription: "AVA manages business renewals from detection through human review, fulfillment, records, audit evidence and the next renewal cycle—so important renewals don't quietly expire.",
            h1: "Renewal Management Software That Doesn't Stop at Reminders",
            ctaLabel: 'Hire AVA for Renewal Operations',
            ctaRoute: 'register',
            body: $this->page1Body(),
            faqs: $this->page1Faqs(),
        );
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
        string $ctaRoute,
        string $body,
        array $faqs,
    ): void {
        $pageId = DB::table('worker_content_pages')->updateOrInsert(
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
                'cta_label'                => $ctaLabel,
                'cta_route'                => $ctaRoute,
                'publishing_wave'          => 'Wave 1',
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

    private function page1Body(): string
    {
        return <<<HTML
<p><strong>Never let an important business renewal quietly expire.</strong></p>
<p>Renewal management shouldn't depend on someone remembering to check a spreadsheet, search an inbox, notice a calendar alert, or follow up at exactly the right time.</p>
<p>AVA is an AI Worker for Renewal Operations.</p>
<p>She continuously monitors renewal activity, organizes renewal work, prepares communications, coordinates human review, tracks fulfillment, maintains renewal records, archives evidence, and schedules the next renewal cycle.</p>
<p>Her job isn't to remind your team that work needs to be done. Her job is to make sure the renewal reaches completion.</p>
<p><strong>AVA owns the process. Your people own the decisions.</strong></p>

<h2>Your business depends on things that expire.</h2>
<p>Domains need to be renewed. SSL certificates need to stay active. Software subscriptions need attention. Licenses expire. Insurance policies require renewal. Hosting services continue on recurring cycles. Maintenance agreements need to remain current.</p>
<p>Most of these obligations are routine. Missing one may not be.</p>
<p>A forgotten domain can interrupt a business. An expired certificate can affect customer trust. A missed license or policy renewal can create operational, financial, or compliance problems.</p>
<p>Yet renewal information is often scattered across:</p>
<ul>
<li>Employee inboxes</li>
<li>Shared mailboxes</li>
<li>Spreadsheets</li>
<li>Calendars</li>
<li>Vendor portals</li>
<li>Accounting systems</li>
<li>Individual employees</li>
</ul>
<p>The problem isn't simply that businesses need more reminders. The problem is that no one owns the entire renewal lifecycle.</p>

<h2>Renewal tracking is not renewal management.</h2>
<p>Traditional renewal tracking answers an important question: <strong>What's coming up?</strong></p>
<p>You record an expiration date. You assign an owner. You create a reminder. You receive an alert. That is useful. But the renewal still has to happen.</p>
<p>Someone has to understand what the notice means. Someone has to identify the correct customer. Someone has to determine which asset is being renewed. Someone has to find the responsible contact. Someone may need to prepare a communication. Someone has to get the appropriate human approval. Someone has to track the work until fulfillment. Someone has to record what happened. Someone has to preserve the evidence. And when everything is finished, someone has to make sure the next renewal cycle is scheduled.</p>
<p>AVA is designed to own that operational responsibility.</p>

<h2>Meet AVA: your Renewal Operations Worker</h2>
<p>AVA is not an AI email assistant. She is not a Gmail automation. She is not a writing tool.</p>
<p>AVA is a Renewal Operations employee on the UNITELO platform. Email is simply one place renewal work can enter the organization.</p>
<p>AVA's responsibility begins when renewal work is detected. It ends only after the renewal has been completed, recorded, archived, and scheduled for its next lifecycle.</p>
<p>Instead of giving your team another system they must remember to operate, AVA is given a business responsibility: <strong>Ensure every important renewal reaches successful completion before expiration.</strong></p>

<h2>How AVA manages a renewal</h2>
<p>Every renewal becomes an accountable transaction. A single renewal might involve several emails, reminders, contacts, documents, invoices, and approvals. AVA still treats it as one renewal. One transaction. One responsibility. One outcome.</p>

<h3>1. Detect</h3>
<p>AVA identifies renewal work entering the organization or approaching a monitored deadline. Version 1 supports renewal detection through Gmail, Asset Watch, and manual triggers. The objective is simple: find renewal work before it becomes a problem.</p>

<h3>2. Understand</h3>
<p>Not every renewal arrives in the same format. Vendors use different terminology. Customers communicate differently. Notices contain different dates, requirements, assets, and instructions. AVA interprets the renewal and determines what the work is about.</p>

<h3>3. Identify the customer</h3>
<p>When organizations manage multiple customers, identifying the correct account matters. AVA connects the renewal to the appropriate customer relationship so the work doesn't become another disconnected email or calendar entry.</p>

<h3>4. Identify the asset</h3>
<p>AVA determines what is actually being renewed. Version 1 supports domains, SSL certificates, hosting services, SaaS subscriptions, insurance policies, licenses, and maintenance agreements. This gives the renewal operational context.</p>

<h3>5. Identify the responsible contact</h3>
<p>A renewal without ownership can sit untouched even when everyone knows the deadline. AVA identifies the appropriate contact associated with the renewal so responsibility is clear.</p>

<h3>6. Prepare communication</h3>
<p>When communication is required, AVA prepares personalized renewal communications using the context of the renewal. But preparation isn't authorization.</p>

<h3>7. Human review</h3>
<p>Human review is required. AVA does not send customer-facing communication without approval. Your organization remains in control of consequential business decisions while AVA coordinates the operational work around them.</p>

<h3>8. Fulfillment</h3>
<p>Approval isn't the finish line. AVA continues tracking the renewal through fulfillment. The purpose is not to mark a reminder as complete. The purpose is to make sure the underlying business obligation is actually renewed.</p>

<h3>9. Record the outcome</h3>
<p>Once fulfillment occurs, AVA records what happened and updates the renewal information. This creates continuity instead of forcing the organization to reconstruct the history the next time the renewal appears.</p>

<h3>10. Schedule the next renewal</h3>
<p>A completed renewal creates another future obligation. AVA schedules the next renewal cycle so monitoring is re-established. The process doesn't return to human memory.</p>

<h3>11. Archive</h3>
<p>Supporting evidence and renewal history are preserved so the organization maintains an auditable record of the transaction.</p>

<h3>12. Complete</h3>
<p>Only now does AVA consider the renewal complete. Not when an email was drafted. Not when a reminder was sent. Not when someone clicked an approval button. A completed renewal is success.</p>

<h2>What does "done" actually mean?</h2>
<p>AVA has a strict definition of completion. A renewal isn't done until:</p>
<ul>
<li>The business obligation has been successfully renewed.</li>
<li>Organizational records accurately reflect the outcome.</li>
<li>Supporting evidence has been archived.</li>
<li>The Renewal Register has been updated.</li>
<li>The next renewal cycle has been scheduled.</li>
<li>No further operational work remains.</li>
</ul>
<p>This creates a fundamentally different accountability model. Most systems help people remember renewal work. AVA is responsible for helping the organization move that work through an operational lifecycle.</p>

<h2>What AVA owns—and what she doesn't</h2>
<p>Giving an AI Worker responsibility should not mean giving it unlimited authority. AVA owns the renewal process. Your people retain control of important decisions.</p>
<p>AVA owns operational work such as: monitoring renewal activity and deadlines, detecting renewal requests, understanding customer relationships, identifying assets and contacts, preparing communications, coordinating human review, tracking progress, updating records, scheduling future renewals, and maintaining audit history.</p>
<p>Humans retain authority over: financial approval, vendor selection, contract negotiation, payment authorization, legal decisions, executive approval, accounting ownership, and other consequential business decisions.</p>
<p>AVA also does not autonomously execute payments or purchases. That boundary is intentional.</p>
<p><strong>AVA owns the process. Humans own the decisions.</strong></p>

<h2>Why use AI for renewal management?</h2>
<p>Renewal operations contain uncertainty. One vendor might send a clear renewal notice. Another may send an invoice. A customer might reference an asset by an internal name. Dates may appear in attachments, email threads, or different systems. The same business process can arrive in many different forms.</p>
<p>AVA uses AI where interpretation is necessary—to understand renewal intent, identify customers and assets, classify requests, understand context, generate personalized communications, and recommend appropriate next actions.</p>
<p>But predictable operational work should remain predictable. The UNITELO platform handles deterministic execution such as creating transactions, recording renewal history, updating the Renewal Register, scheduling future renewals, enforcing approval policies, maintaining audit records, and tracking workflow state.</p>
<p>AI handles uncertainty. The platform handles execution. That separation allows renewal operations to remain controlled, consistent, and auditable.</p>

<h2>Renewal management software vs. spreadsheets and reminders</h2>
<p>A spreadsheet can tell you that a renewal exists. A calendar can tell you that a date is approaching. A reminder can tell someone to do something.</p>
<p>AVA is designed to answer a different question: <strong>Who is responsible for making sure the renewal actually gets finished?</strong></p>
<p>That's why AVA isn't intended to replace a spreadsheet with a prettier spreadsheet. She replaces fragmented operational responsibility with continuous ownership. Your existing systems can remain part of the environment. AVA's job is to keep renewal work moving.</p>

<h2>Built for organizations with recurring operational obligations</h2>
<p>AVA is designed for organizations where renewals happen repeatedly and across many customers, assets, or systems. That includes:</p>

<h3>Managed Service Providers</h3>
<p>Coordinate recurring client obligations across domains, SSL certificates, software, licenses, hosting, and maintenance agreements.</p>

<h3>IT Service Companies</h3>
<p>Keep technology-related renewal work visible and moving without relying on individual employees to remember every deadline.</p>

<h3>Digital Agencies</h3>
<p>Manage recurring client domains, hosting, certificates, subscriptions, and other operational obligations across multiple accounts.</p>

<h3>Hosting Providers</h3>
<p>Track customer and infrastructure renewal obligations that cannot be allowed to quietly expire.</p>

<h3>Professional Service Firms</h3>
<p>Create consistent operational ownership for recurring customer and business obligations.</p>

<h3>Compliance Teams</h3>
<p>Maintain visibility, records, evidence, and future scheduling around supported recurring obligations.</p>

<h3>Organizations Managing Customer Renewals</h3>
<p>Give every renewal a defined lifecycle, accountable transaction, and recorded outcome.</p>

<h2>One renewal. One accountable transaction.</h2>
<p>Renewal operations become difficult when responsibility is fragmented. Sales sees one email. Finance receives an invoice. Operations knows about the asset. Management handles approval. Someone updates a spreadsheet. Someone else remembers the next date.</p>
<p>AVA changes the unit of accountability. One renewal = one transaction. All the emails, reminders, contacts, documents, approvals, records, and actions associated with that renewal belong to the same operational responsibility. The renewal remains open until the work is actually complete.</p>

<h2>Stop managing renewals by memory.</h2>
<p>The goal isn't more automation for its own sake. It's operational consistency.</p>
<p>AVA exists so organizations don't have to depend on individual employees remembering hundreds of recurring obligations across inboxes, spreadsheets, calendars, and disconnected systems.</p>
<p>She doesn't replace the people who make business decisions. She removes one repetitive, time-sensitive, and operationally risky responsibility from their workload.</p>
<p>If you could hire one employee whose only responsibility was to make sure every important business renewal was completed on time—without relying on memory and while keeping your team in control of every important decision—that's the job AVA is designed to do.</p>
HTML;
    }

    private function page1Faqs(): array
    {
        return [
            ['What is renewal management software?', "Renewal management software helps organizations organize and manage recurring business obligations such as contracts, subscriptions, domains, certificates, licenses, insurance policies, and other renewable assets. AVA extends that concept beyond tracking dates and sending reminders by owning the operational lifecycle from detection through completion and future scheduling."],
            ['What is the difference between renewal tracking and renewal management?', "Renewal tracking focuses primarily on visibility: dates, deadlines, owners, calendars, and reminders. Renewal management includes the operational work required after detection—understanding the renewal, identifying the correct customer and asset, coordinating communication and approval, tracking fulfillment, recording the outcome, preserving evidence, and scheduling the next cycle."],
            ['What types of renewals can AVA manage?', "AVA Version 1 supports domains, SSL certificates, hosting services, SaaS subscriptions, insurance policies, licenses, and maintenance agreements."],
            ['Does AVA automatically approve renewals?', "No. Human review is required. AVA does not own financial approval, payment authorization, vendor selection, contract negotiation, legal decisions, or executive approval."],
            ['Can AVA automatically send customer communications?', "No customer-facing communication proceeds without human approval in Version 1. AVA can prepare personalized communications and coordinate the review process."],
            ['Does AVA make payments?', "No. Automatic payment execution and financial authorization are outside AVA's Version 1 responsibilities."],
            ['What happens after a renewal is completed?', "AVA updates the renewal records and Renewal Register, archives supporting evidence, schedules the next renewal cycle, and re-establishes future monitoring."],
            ['Is AVA a contract management system?', "AVA's responsibility is Renewal Operations. She may coordinate renewal work associated with agreements, but she does not own contract negotiation, legal approval, autonomous purchasing, or broader contract lifecycle management."],
            ['Is AVA an email automation tool?', "No. Email is one communication channel through which renewal work can enter the organization. AVA's responsibility is the renewal itself, not the email."],
            ['How does AVA use AI?', "AVA uses AI to interpret uncertain information—such as renewal intent, customers, assets, contacts, context, and communications. Deterministic systems on the UNITELO platform handle transaction creation, workflow state, approval policies, records, scheduling, notifications, and audit history."],
        ];
    }
}
