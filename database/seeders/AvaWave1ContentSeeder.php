<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Seeds worker_content_pages / worker_content_faqs from the approved AVA SEO
// Market Map, Wave 1. Run once per page as copy is approved:
//   php artisan db:seed --class=Database\\Seeders\\AvaWave1ContentSeeder
// Idempotent: upserts by url_path so re-running updates rather than duplicates.
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
            metaDescription: "AVA manages business renewals from detection through human review, fulfillment, records, audit evidence and the next renewal cycle, so important renewals don't quietly expire.",
            h1: "Renewal Management Software That Doesn't Stop at Reminders",
            ctaLabel: 'Hire AVA for Renewal Operations',
            ctaHeadline: 'Give Renewal Operations an owner.',
            ctaSubtext: "If you could hire one employee whose only responsibility was to make sure every important business renewal was completed on time, without relying on memory, while keeping your team in control of every important decision, that's the job AVA is designed to do.",
            ctaRoute: 'register',
            body: $this->page1Body(),
            faqs: $this->page1Faqs(),
        );

        $this->seedPage(
            worker: 'ava',
            tier: 'Tier 2',
            pageFamily: 'Lifecycle Solution',
            urlPath: 'detect/renewal-tracking-software',
            lifecycleStage: 'Detect',
            primaryQuery: 'renewal tracking software',
            secondaryQueries: ['renewal reminder software', 'expiration tracking'],
            seoTitle: 'Renewal Tracking & Reminder Software | AVA by UNITELO',
            metaDescription: "Track renewal dates, expiration risk and upcoming obligations with AVA, then move every renewal from detection and reminders through completion and the next renewal cycle.",
            h1: "Renewal Tracking & Reminder Software That Goes Beyond Reminders",
            ctaLabel: 'Put AVA in Charge of Renewal Tracking',
            ctaHeadline: 'Give every renewal an owner.',
            ctaSubtext: "You don't need another system that tells your team there's work to do and then walks away. Give the work to AVA. Let her detect it, organize it, and track it, while your people stay in control of the decisions and AVA stays responsible for moving the renewal toward completion.",
            ctaRoute: 'register',
            body: $this->page2Body(),
            faqs: $this->page2Faqs(),
        );

        $this->seedPage(
            worker: 'ava',
            tier: 'Tier 2',
            pageFamily: 'Asset Solution',
            urlPath: 'assets/domains',
            lifecycleStage: 'Detect',
            primaryQuery: 'domain expiration monitoring',
            secondaryQueries: ['domain renewal tracking', 'domain renewal tracker'],
            seoTitle: 'Domain Renewal & Expiration Tracking | AVA by UNITELO',
            metaDescription: "Track domain expiration and renewal work with AVA. Detect upcoming renewals, identify the right customer and contact, coordinate approval, record completion, and schedule the next cycle.",
            h1: "Domain Renewal & Expiration Tracking That Doesn't Stop at an Alert",
            ctaLabel: 'Have AVA Own Domain Renewals',
            ctaHeadline: 'Have AVA own Domain Renewal Operations.',
            ctaSubtext: "Your customers shouldn't depend on someone remembering a date. Your operations team shouldn't have to search through old inboxes to figure out who owns a domain. And your renewal process shouldn't end with another notification.",
            ctaRoute: 'register',
            body: $this->page3Body(),
            faqs: $this->page3Faqs(),
        );

        $this->seedPage(
            worker: 'ava',
            tier: 'Tier 2',
            pageFamily: 'Asset Solution',
            urlPath: 'assets/ssl-certificates',
            lifecycleStage: 'Detect',
            primaryQuery: 'SSL expiration monitoring',
            secondaryQueries: ['SSL certificate renewal tracking', 'SSL certificate expiration alerts'],
            seoTitle: 'SSL Certificate Renewal & Expiration Tracking | AVA by UNITELO',
            metaDescription: "Track SSL certificate renewal and expiration work with AVA. Detect renewal risk, coordinate human review, track fulfillment, record the outcome, and schedule the next cycle.",
            h1: "SSL Certificate Renewal & Expiration Tracking That Goes Beyond Alerts",
            ctaLabel: 'Have AVA Own SSL Renewals',
            ctaHeadline: 'Have AVA own SSL Renewal Operations.',
            ctaSubtext: "Expiration monitoring matters, but businesses ultimately don't need certificates to be monitored. They need certificates not to quietly expire. Give that operational responsibility an owner.",
            ctaRoute: 'register',
            body: $this->page4Body(),
            faqs: $this->page4Faqs(),
        );

        $this->seedPage(
            worker: 'ava',
            tier: 'Tier 2',
            pageFamily: 'Asset Solution',
            urlPath: 'assets/saas',
            lifecycleStage: 'Detect',
            primaryQuery: 'SaaS renewal management',
            secondaryQueries: ['software renewal tracking', 'SaaS subscription renewal tracker'],
            seoTitle: 'SaaS & Software Renewal Management | AVA by UNITELO',
            metaDescription: "Manage SaaS and software renewals with AVA. Track renewal work, identify owners, coordinate human review, record outcomes, and schedule the next renewal cycle.",
            h1: 'SaaS & Software Renewal Management That Keeps Every Renewal Accountable',
            ctaLabel: 'Have AVA Manage Software Renewals',
            ctaHeadline: 'Give software renewals an operational owner.',
            ctaSubtext: "Your team should make the decisions that affect your business, not spend their time chasing renewal work across inboxes, spreadsheets, calendars, vendor portals, and individual employees. AVA handles the operational responsibility. Your people remain in control.",
            ctaRoute: 'register',
            body: $this->page5Body(),
            faqs: $this->page5Faqs(),
        );

        $this->seedPage(
            worker: 'ava',
            tier: 'Tier 2',
            pageFamily: 'Industry/Customer',
            urlPath: 'industries/managed-service-providers',
            lifecycleStage: 'Identify Customer',
            primaryQuery: 'renewal management for MSPs',
            secondaryQueries: ['MSP renewal management software', 'managed service provider renewal tracking'],
            seoTitle: 'Renewal Management for MSPs | AVA by UNITELO',
            metaDescription: "AVA gives MSPs an AI Renewal Operations Worker to manage recurring client renewals across domains, SSL, hosting, SaaS, licenses, and maintenance agreements.",
            h1: "Renewal Management for Managed Service Providers That Doesn't Depend on Who Received the Email",
            ctaLabel: 'Hire AVA for MSP Renewal Operations',
            ctaHeadline: 'Give your MSP a dedicated Renewal Operations Worker.',
            ctaSubtext: "Your customers trust you with recurring technology obligations. Those obligations shouldn't depend on somebody remembering the right date, checking the right spreadsheet, or finding the right email. Give every renewal a transaction. Give every transaction an owner. Keep your people in control of the decisions.",
            ctaRoute: 'register',
            body: $this->page6Body(),
            faqs: $this->page6Faqs(),
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
                'cta_headline'             => $ctaHeadline,
                'cta_subtext'              => $ctaSubtext,
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

<h2>What AVA owns, and what she doesn't</h2>
<p>Giving an AI Worker responsibility should not mean giving it unlimited authority. AVA owns the renewal process. Your people retain control of important decisions.</p>
<p>AVA owns operational work such as: monitoring renewal activity and deadlines, detecting renewal requests, understanding customer relationships, identifying assets and contacts, preparing communications, coordinating human review, tracking progress, updating records, scheduling future renewals, and maintaining audit history.</p>
<p>Humans retain authority over: financial approval, vendor selection, contract negotiation, payment authorization, legal decisions, executive approval, accounting ownership, and other consequential business decisions.</p>
<p>AVA also does not autonomously execute payments or purchases. That boundary is intentional.</p>
<p><strong>AVA owns the process. Humans own the decisions.</strong></p>

<h2>Why use AI for renewal management?</h2>
<p>Renewal operations contain uncertainty. One vendor might send a clear renewal notice. Another may send an invoice. A customer might reference an asset by an internal name. Dates may appear in attachments, email threads, or different systems. The same business process can arrive in many different forms.</p>
<p>AVA uses AI where interpretation is necessary: to understand renewal intent, identify customers and assets, classify requests, understand context, generate personalized communications, and recommend appropriate next actions.</p>
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

<h2>Explore AVA's Renewal Operations</h2>
<p>Detection is where most renewals start. See how AVA turns a renewal alert into an accountable transaction in <a href="{$this->pageUrl('ava', 'detect/renewal-tracking-software')}">Renewal Tracking &amp; Reminder Software</a>, see how she manages one of the most commonly missed recurring obligations in <a href="{$this->pageUrl('ava', 'assets/domains')}">Domain Renewal &amp; Expiration Tracking</a>, see how the same lifecycle applies to recurring subscriptions in <a href="{$this->pageUrl('ava', 'assets/saas')}">SaaS &amp; Software Renewal Management</a>, or see how it scales across an entire customer base in <a href="{$this->pageUrl('ava', 'industries/managed-service-providers')}">Renewal Management for MSPs</a>.</p>

<h2>Stop managing renewals by memory.</h2>
<p>The goal isn't more automation for its own sake. It's operational consistency.</p>
<p>AVA exists so organizations don't have to depend on individual employees remembering hundreds of recurring obligations across inboxes, spreadsheets, calendars, and disconnected systems.</p>
<p>She doesn't replace the people who make business decisions. She removes one repetitive, time-sensitive, and operationally risky responsibility from their workload.</p>
<p>If you could hire one employee whose only responsibility was to make sure every important business renewal was completed on time, without relying on memory, while keeping your team in control of every important decision, that's the job AVA is designed to do.</p>
HTML;
    }

    private function page1Faqs(): array
    {
        return [
            ['What is renewal management software?', "Renewal management software helps organizations organize and manage recurring business obligations such as contracts, subscriptions, domains, certificates, licenses, insurance policies, and other renewable assets. AVA extends that concept beyond tracking dates and sending reminders by owning the operational lifecycle from detection through completion and future scheduling."],
            ['What is the difference between renewal tracking and renewal management?', "Renewal tracking focuses primarily on visibility: dates, deadlines, owners, calendars, and reminders. Renewal management includes the operational work required after detection: understanding the renewal, identifying the correct customer and asset, coordinating communication and approval, tracking fulfillment, recording the outcome, preserving evidence, and scheduling the next cycle."],
            ['What types of renewals can AVA manage?', "AVA Version 1 supports domains, SSL certificates, hosting services, SaaS subscriptions, insurance policies, licenses, and maintenance agreements."],
            ['Does AVA automatically approve renewals?', "No. Human review is required. AVA does not own financial approval, payment authorization, vendor selection, contract negotiation, legal decisions, or executive approval."],
            ['Can AVA automatically send customer communications?', "No customer-facing communication proceeds without human approval in Version 1. AVA can prepare personalized communications and coordinate the review process."],
            ['Does AVA make payments?', "No. Automatic payment execution and financial authorization are outside AVA's Version 1 responsibilities."],
            ['What happens after a renewal is completed?', "AVA updates the renewal records and Renewal Register, archives supporting evidence, schedules the next renewal cycle, and re-establishes future monitoring."],
            ['Is AVA a contract management system?', "AVA's responsibility is Renewal Operations. She may coordinate renewal work associated with agreements, but she does not own contract negotiation, legal approval, autonomous purchasing, or broader contract lifecycle management."],
            ['Is AVA an email automation tool?', "No. Email is one communication channel through which renewal work can enter the organization. AVA's responsibility is the renewal itself, not the email."],
            ['How does AVA use AI?', "AVA uses AI to interpret uncertain information, such as renewal intent, customers, assets, contacts, context, and communications. Deterministic systems on the UNITELO platform handle transaction creation, workflow state, approval policies, records, scheduling, notifications, and audit history."],
        ];
    }

    private function page2Body(): string
    {
        return <<<HTML
<p><strong>Know what's expiring. Know what needs attention. Make sure the renewal actually gets finished.</strong></p>
<p>A renewal reminder can tell you something is about to expire. It cannot make sure the renewal gets completed.</p>
<p>AVA is an AI Worker for Renewal Operations that continuously monitors renewal activity, detects upcoming renewal work, tracks deadlines, and moves each renewal into an accountable operational lifecycle.</p>
<p>Instead of leaving your team with another alert to act on, AVA stays responsible for the renewal through human review, fulfillment, records, evidence, and the next scheduled renewal cycle.</p>
<p>Because detecting a renewal is only the beginning.</p>

<h2>Why businesses miss renewals even when they have reminders</h2>
<p>Most organizations don't intentionally ignore important renewals. The problem is fragmentation.</p>
<p>Renewal information gets scattered across:</p>
<ul>
<li>Employee inboxes</li>
<li>Shared mailboxes</li>
<li>Spreadsheets</li>
<li>Calendars</li>
<li>Vendor portals</li>
<li>Accounting software</li>
<li>Individual employees</li>
</ul>
<p>A domain renewal notice might arrive in one inbox. An SSL certificate expiration date might be tracked somewhere else. A software subscription could be owned by another employee. A maintenance agreement may exist only in a spreadsheet. An insurance renewal may depend on someone remembering a calendar event.</p>
<p>Each system can contain part of the truth. But no single system necessarily owns what happens next.</p>
<p>That's how organizations can have reminders everywhere and still miss renewals.</p>

<h2>What is renewal tracking software?</h2>
<p>Renewal tracking software helps organizations monitor recurring business obligations and the dates associated with them. Depending on the system, that can include:</p>
<ul>
<li>Renewal dates</li>
<li>Expiration dates</li>
<li>Upcoming deadlines</li>
<li>Renewable assets</li>
<li>Customers</li>
<li>Responsible owners</li>
<li>Renewal status</li>
<li>Reminders</li>
<li>Notifications</li>
<li>Renewal history</li>
</ul>
<p>This visibility matters. You cannot manage a renewal you don't know exists. But visibility alone doesn't complete the work. The more important question is: what happens after the renewal is detected?</p>

<h2>A reminder creates awareness. It doesn't create ownership.</h2>
<p>Imagine an important software license expires in 30 days. Your renewal tracking system sends an alert. Someone receives it. Now what?</p>
<p>Someone still has to understand what is being renewed. Someone needs to determine which customer or business unit it belongs to. Someone has to identify the asset. Someone needs to find the responsible contact. Communication may need to be prepared. Approval may be required. The renewal has to be fulfilled. Records need to be updated. Evidence may need to be preserved. And the next renewal cycle needs to be scheduled.</p>
<p>The reminder solved one problem: awareness. It did not solve accountability.</p>
<p>AVA is designed to address both.</p>

<h2>Meet AVA: renewal tracking with an operational owner</h2>
<p>AVA is not a reminder bot. She is not an email assistant. She is not a calendar notification.</p>
<p>AVA is a Renewal Operations Worker on the UNITELO platform. Her job is to ensure that important business renewals don't get forgotten, delayed, or lost because of manual work, fragmented systems, or human oversight.</p>
<p>Renewal tracking is part of that job. It isn't the entire job.</p>
<p>AVA continuously monitors renewal activity, organizes renewal work, tracks deadlines, prepares communications, coordinates human review, maintains records, and keeps the renewal moving toward completion.</p>

<h2>How AVA tracks renewals</h2>

<h3>1. Detect renewal work</h3>
<p>AVA's renewal lifecycle starts with detection. Version 1 supports three sources: Gmail, where renewal notices and related communications can enter through email and AVA uses AI to interpret renewal intent rather than treating every message as identical; Asset Watch, where renewable assets can be monitored for upcoming renewal or expiration activity; and Manual Trigger, where your team can initiate a renewal transaction when renewal work is identified elsewhere.</p>
<p>Different entry points. One renewal lifecycle.</p>

<h3>2. Understand what the renewal is about</h3>
<p>Detecting a date isn't always enough. Renewal notices arrive in different formats. Vendors use different language. Customers communicate differently. Business rules change. AVA interprets the information to understand the renewal request and determine what requires attention.</p>
<p>This is where AI becomes useful. AVA can help interpret renewal intent, renewal context, the asset involved, the customer involved, responsible contacts, and appropriate next actions.</p>
<p>The goal isn't simply to extract a date. The goal is to understand the work represented by that date.</p>

<h3>3. Connect the renewal to the right customer and asset</h3>
<p>A renewal deadline without context can still create confusion. AVA identifies the customer relationship and the asset associated with the renewal. Version 1 supports domains, SSL certificates, hosting services, SaaS subscriptions, insurance policies, licenses, and maintenance agreements.</p>

<h3>4. Identify who is responsible</h3>
<p>Renewals frequently become stuck because everyone can see the deadline but nobody clearly owns the next action. AVA identifies the responsible contact associated with the renewal. That creates a clearer path from "this expires soon" to "this is the renewal transaction, this is what it concerns, and this is who needs to be involved."</p>

<h3>5. Track the renewal as one transaction</h3>
<p>A renewal may generate multiple emails, reminders, invoices, approvals, documents, and contacts. AVA doesn't treat each interaction as unrelated work. One renewal remains one transaction. That transaction becomes the unit of accountability. The emails belong to it. The reminders belong to it. The approvals belong to it. The records belong to it. And the transaction stays open until the renewal reaches its required outcome.</p>

<h3>6. Escalate from tracking into action</h3>
<p>This is where AVA differs from a basic renewal reminder system. Once renewal work has been detected and understood, AVA can continue through the operational lifecycle: Identify Customer, Identify Asset, Identify Contact, Prepare Communication, Human Review, Fulfillment, Record Outcome, Schedule Next Renewal, Archive, Complete.</p>
<p>The alert isn't the finish line. It's the beginning of the transaction.</p>

<h2>Human review stays in the loop</h2>
<p>Operational ownership does not mean unlimited authority. AVA coordinates renewal work while humans remain responsible for consequential business decisions.</p>
<p>Human review is required. No customer-facing communication proceeds without approval.</p>
<p>AVA does not own financial approval, vendor selection, contract negotiation, payment authorization, legal decisions, executive approval, CRM ownership, or accounting ownership. AVA also does not automatically execute payments or make autonomous purchases.</p>
<p><strong>AVA owns the process. Humans own the decisions.</strong></p>

<h2>Renewal tracking shouldn't stop when someone clicks "done"</h2>
<p>One of the biggest weaknesses of reminder-based processes is what happens after the current task is completed. Someone handles the renewal. The reminder disappears. Everyone moves on. Months later, the same problem returns.</p>
<p>AVA treats future scheduling as part of completing the current renewal. A renewal isn't complete until the obligation has been successfully renewed, records reflect the outcome, supporting evidence has been archived, the Renewal Register has been updated, the next renewal cycle has been scheduled, and no further operational work remains.</p>
<p>This creates a closed renewal loop: Detect, Complete, Monitor Again.</p>

<h2>Renewal alerts vs. renewal operations</h2>
<p>A renewal alert tells your organization something needs attention. AVA is designed to answer what needs attention, who and what does it involve, what happens next, and has the renewal actually been completed.</p>
<p>That's a much larger responsibility. A business with only a few recurring obligations may be able to manage them with calendars and reminders. But as the number of customers, assets, systems, vendors, and renewal dates grows, the operational burden grows too.</p>
<p>Eventually, the problem isn't remembering a date. It's managing the work created by the date.</p>

<h2>Stop building a bigger reminder stack</h2>
<p>Many organizations already have enough reminders. Calendar notifications. Spreadsheet colors. Email flags. Slack messages. Vendor notifications. Recurring tasks. Personal notes.</p>
<p>The missing layer is often not another alert. It's an owner.</p>
<p>AVA gives Renewal Operations a dedicated owner while keeping your people in control of the decisions that matter.</p>

<h2>Renewal tracking for organizations managing recurring obligations</h2>
<p>AVA is particularly relevant when renewal responsibility spans multiple customers, assets, and systems.</p>

<h3>Managed Service Providers</h3>
<p>Track recurring client obligations across software, domains, SSL certificates, hosting, licenses, and maintenance agreements.</p>

<h3>Digital Agencies</h3>
<p>Keep client domains, certificates, hosting services, subscriptions, and other recurring obligations from becoming scattered across employees.</p>

<h3>IT Service Companies</h3>
<p>Maintain continuous visibility and ownership over technology renewal work.</p>

<h3>Hosting Providers</h3>
<p>Monitor recurring customer and infrastructure renewal obligations.</p>

<h3>Professional Service Firms</h3>
<p>Create a repeatable operational process around recurring business and customer renewals.</p>

<h3>Compliance Teams</h3>
<p>Keep supported recurring obligations visible while maintaining records, evidence, and future scheduling.</p>

<h2>Go deeper on Renewal Operations</h2>
<p>Renewal tracking is the Detect stage of a larger responsibility. See the complete operational lifecycle in <a href="{$this->pageUrl('ava', 'renewal-management')}">Renewal Management Software</a>, see how detection works for one specific renewable asset in <a href="{$this->pageUrl('ava', 'assets/domains')}">Domain Renewal &amp; Expiration Tracking</a>, see how it applies to recurring subscriptions in <a href="{$this->pageUrl('ava', 'assets/saas')}">SaaS &amp; Software Renewal Management</a>, or see how it scales across an entire customer base in <a href="{$this->pageUrl('ava', 'industries/managed-service-providers')}">Renewal Management for MSPs</a>.</p>

<h2>From "expires soon" to "renewal complete"</h2>
<p>The purpose of renewal tracking isn't to create more notifications. It's to prevent expiration.</p>
<p>AVA's success isn't measured by how many alerts she generates. It isn't measured by how many emails she drafts. It isn't measured by how many reminders your team receives.</p>
<p>Success is measured by completed renewals. That means the renewal was detected. The requirements were understood. The correct customer, asset, and contact were identified. Human review happened where required. Fulfillment occurred. Records were updated. Evidence was archived. The next renewal was scheduled. And the transaction reached completion.</p>
<p>That's what renewal tracking should ultimately make possible.</p>
HTML;
    }

    private function page2Faqs(): array
    {
        return [
            ['What is renewal tracking software?', "Renewal tracking software helps businesses monitor renewal dates, expiration deadlines, renewable assets, owners, statuses, and upcoming renewal activity. AVA combines renewal tracking with an operational lifecycle that continues after detection."],
            ['What is renewal reminder software?', "Renewal reminder software alerts users when a renewal or expiration deadline is approaching. AVA uses monitoring and notifications as part of a broader Renewal Operations responsibility rather than treating the reminder itself as the outcome."],
            ['What can AVA track?', "AVA Version 1 supports renewal operations involving domains, SSL certificates, hosting services, SaaS subscriptions, insurance policies, licenses, and maintenance agreements."],
            ['Where can AVA detect renewal work?', "Version 1 supports Gmail, Asset Watch, and manual triggers."],
            ['Does AVA automatically renew or pay for subscriptions?', "No. Automatic payment execution, financial authorization, and autonomous purchasing are outside AVA's Version 1 scope."],
            ['Does AVA send renewal emails automatically?', "AVA can prepare personalized renewal communications, but no customer-facing communication proceeds without human approval in Version 1."],
            ['How is AVA different from a renewal calendar?', "A calendar primarily organizes dates and reminders. AVA uses dates and monitoring as inputs into an operational renewal transaction that continues through review, fulfillment, records, future scheduling, and completion."],
            ['How is AVA different from a spreadsheet renewal tracker?', "A spreadsheet can store renewal information, but someone still has to maintain it, interpret what needs attention, coordinate the work, follow up, record the result, and remember the next cycle. AVA is designed to own that operational responsibility."],
            ['What happens after a renewal is completed?', "AVA updates the renewal records and Renewal Register, archives supporting evidence, schedules the next renewal cycle, and re-establishes future monitoring."],
            ['Does AVA replace the people who currently manage renewals?', "AVA is designed to remove repetitive renewal coordination from their workload, not take away human decision authority. Your team retains control of financial, legal, vendor, payment, and executive decisions."],
        ];
    }

    private function page3Body(): string
    {
        return <<<HTML
<p><strong>Never let an important domain quietly expire.</strong></p>
<p>A domain can be one of the smallest recurring expenses in a business. It can also be one of the most disruptive things to forget.</p>
<p>Domain renewal notices arrive. Expiration dates approach. Reminders get sent. But when domain responsibility is spread across inboxes, registrar accounts, spreadsheets, calendars, customers, and individual employees, an important renewal can still be missed.</p>
<p>AVA is an AI Worker for Renewal Operations. She helps organizations detect domain renewal work, identify the customer and domain involved, find the responsible contact, coordinate the renewal through human review and fulfillment, record the outcome, and schedule the next renewal cycle.</p>
<p>The goal isn't another domain expiration alert. The goal is a completed domain renewal.</p>

<h2>Why domain renewals still get missed</h2>
<p>Domain renewal should be simple. A domain has an expiration date. The organization renews it before that date. Done.</p>
<p>In practice, the operational environment around the domain can make that simple task surprisingly fragile.</p>
<p>A renewal notice might arrive in an employee's inbox. The domain may be registered through a customer account. A digital agency may manage domains for dozens of clients. A Managed Service Provider may have renewal responsibility spread across different customer relationships.</p>
<p>The person receiving the renewal notice may not know who owns the domain. The person who knows the domain may not have authority to approve the renewal. Another employee may handle payment. Someone else may maintain the customer record.</p>
<p>The registrar can send a reminder. But the registrar doesn't own your internal renewal process.</p>
<p>That's the problem AVA is designed to address.</p>

<h2>What is domain renewal tracking?</h2>
<p>Domain renewal tracking is the process of monitoring domain renewal and expiration activity so important domains can be renewed before they expire. A domain renewal tracking process may include:</p>
<ul>
<li>Domain name</li>
<li>Customer or organization</li>
<li>Expiration date</li>
<li>Renewal date</li>
<li>Responsible contact</li>
<li>Renewal status</li>
<li>Renewal reminders</li>
<li>Supporting communications</li>
<li>Renewal history</li>
<li>Next renewal cycle</li>
</ul>
<p>At minimum, tracking gives you visibility. You know which domains exist. You know which dates are approaching. You know something needs attention.</p>
<p>But visibility is only the first layer. Once an important domain approaches renewal, somebody still needs to own what happens next.</p>

<h2>Domain expiration monitoring is not the same as domain renewal operations</h2>
<p>Expiration monitoring answers: when does this domain expire?</p>
<p>Renewal Operations answers: what needs to happen so this domain does not expire?</p>
<p>Those are different responsibilities. A monitoring system can identify the deadline. An alert can tell your team the deadline is approaching. But someone may still need to understand the renewal notice, identify the correct customer, confirm the domain involved, find the responsible contact, prepare necessary communication, obtain human approval, track the renewal through fulfillment, record what happened, preserve supporting evidence, and schedule the next renewal cycle.</p>
<p>AVA connects expiration awareness to operational ownership.</p>

<h2>Meet AVA: a Renewal Operations Worker for recurring domain obligations</h2>
<p>AVA is not a domain registrar. She does not replace the systems that register, host, or technically operate your domains. Her job is Renewal Operations. Domains are one of the renewable assets AVA supports.</p>
<p>When domain renewal work is detected, AVA can organize that work as a renewal transaction and keep it moving through the defined lifecycle.</p>
<p>That means your team doesn't have to treat a domain renewal notice as another disconnected email, reminder, or calendar event. It becomes accountable work.</p>

<h2>How AVA manages a domain renewal</h2>

<h3>1. Detect the renewal</h3>
<p>AVA's responsibility starts when renewal work is detected. In Version 1, renewal work can enter through Gmail, Asset Watch, and Manual Trigger. A domain renewal notice arriving by email can therefore become operational work. A monitored asset approaching renewal can become operational work. And your team can manually initiate a renewal when necessary.</p>
<p>The important change is what happens next. The renewal doesn't remain an isolated notification. It enters a lifecycle.</p>

<h3>2. Understand the renewal</h3>
<p>Domain renewal communications aren't always identical. Different vendors use different formats and terminology. The relevant information can appear in different places. AVA uses AI to interpret renewal intent and business context. The objective is to understand what this renewal is about, and what needs attention.</p>

<h3>3. Identify the customer</h3>
<p>This becomes especially important for organizations managing domains on behalf of other businesses. A Managed Service Provider may receive renewal activity for many customers. A digital agency may manage domains for dozens or hundreds of client websites. A hosting provider may have recurring obligations across customer accounts.</p>
<p>Knowing that a domain is approaching renewal isn't enough. The renewal needs the correct organizational context. AVA's lifecycle includes customer identification so the domain renewal can be connected to the appropriate customer relationship.</p>

<h3>4. Identify the domain</h3>
<p>AVA identifies the renewable asset associated with the transaction. In this case, that asset is the domain. Connecting the renewal to a specific asset gives the transaction operational context. Instead of "domain renewal email received," the organization can work from "this renewal transaction concerns this domain for this customer." That distinction matters when renewal volume grows.</p>

<h3>5. Identify the responsible contact</h3>
<p>Knowing the customer and domain still doesn't answer who needs to be involved. AVA identifies the responsible contact associated with the renewal. This helps prevent the common situation where everyone knows a domain is approaching expiration but nobody clearly owns the next action.</p>

<h3>6. Prepare the renewal communication</h3>
<p>Some domain renewals may require communication with a customer or another responsible party. AVA can prepare personalized communication based on the context of the renewal. But AVA does not confuse preparation with authority. Customer-facing communication requires human approval.</p>

<h3>7. Keep humans in control</h3>
<p>Human review is required. AVA can coordinate the renewal process, but consequential business decisions remain with people. AVA does not own financial approval, payment authorization, vendor selection, contract negotiation, legal decisions, or executive approval. AVA also does not automatically execute payments or autonomously purchase a domain renewal.</p>
<p><strong>AVA owns the process. Humans own the decisions.</strong></p>

<h3>8. Track the domain renewal through fulfillment</h3>
<p>A domain renewal shouldn't disappear from the operational workflow simply because someone approved it. The underlying obligation still needs to be fulfilled. AVA continues tracking the renewal.</p>
<p>That distinction is important. An approval is not the outcome. An email is not the outcome. A reminder is not the outcome. The domain being successfully renewed is the outcome.</p>

<h3>9. Record what happened</h3>
<p>Once the renewal has been fulfilled, AVA updates the renewal records. The Renewal Register reflects the outcome. This gives the organization continuity. The next time the domain enters a renewal cycle, the organization doesn't have to reconstruct what happened from old emails, employee memory, or disconnected spreadsheets.</p>

<h3>10. Archive the evidence</h3>
<p>Supporting evidence associated with the completed renewal can be archived as part of the transaction history. This creates a more complete operational record of the renewal. The organization can see that the renewal didn't simply disappear from a task list. It reached an outcome.</p>

<h3>11. Schedule the next domain renewal</h3>
<p>A renewed domain isn't permanently finished. The current obligation may be complete. Another renewal cycle now exists in the future. AVA schedules that next cycle and re-establishes monitoring.</p>
<p>This creates a closed loop: Detect, Understand, Coordinate, Renew, Record, Schedule, Monitor Again. The organization doesn't have to restart the process from memory next year.</p>

<h2>One domain renewal. One accountable transaction.</h2>
<p>A single domain renewal can involve a registrar notification, several email messages, multiple reminders, customer communication, an invoice, human approval, supporting documents, and different contacts.</p>
<p>AVA keeps those interactions connected to one renewal transaction. One renewal remains one transaction. That transaction stays accountable until the renewal reaches completion.</p>
<p>This is particularly valuable for organizations managing many domains across many customers.</p>

<h2>Domain renewal tracking for Managed Service Providers</h2>
<p>Managed Service Providers can accumulate recurring obligations across an entire customer portfolio. One client may have multiple domains, SSL certificates, hosting services, software subscriptions, licenses, and maintenance agreements.</p>
<p>Multiply that across dozens or hundreds of customers and renewal work becomes an operational system of its own.</p>
<p>AVA helps give each renewal a customer, an asset, a responsible contact, a lifecycle, and an outcome. Instead of asking employees to remember every recurring obligation, the renewal work has an owner.</p>

<h2>Domain renewal tracking for digital agencies</h2>
<p>Digital agencies often inherit responsibility for assets that aren't their core creative or marketing work. Domains are a good example.</p>
<p>The agency may build the website. Someone registers the domain. Another employee receives renewal notices. A client needs to approve the expense. Years later, the person who originally set everything up may no longer be involved. Yet the domain still needs to remain active.</p>
<p>AVA helps maintain operational continuity around that recurring obligation. The renewal belongs to the lifecycle, not to one employee's memory.</p>

<h2>Manage domains and SSL renewals as connected operational obligations</h2>
<p>Domains and SSL certificates are separate renewable assets, but they can exist within the same customer environment. A domain can remain registered while an SSL certificate expires. A certificate can be valid while a domain renewal is approaching.</p>
<p>Each obligation therefore needs its own transaction, deadline, status, and outcome. AVA can support renewal operations for both asset types while keeping each renewal accountable. See how she manages the certificate side of that environment in <a href="{$this->pageUrl('ava', 'assets/ssl-certificates')}">SSL Certificate Renewal &amp; Expiration Tracking</a>.</p>

<h2>Domain renewal spreadsheets work, until the work outgrows the spreadsheet</h2>
<p>A spreadsheet can be perfectly adequate for storing domain names, customers, expiration dates, registrar information, owners, and notes.</p>
<p>But the spreadsheet doesn't own the work represented by those fields. Someone has to keep it current. Someone has to check it. Someone has to notice approaching deadlines. Someone has to investigate changes. Someone has to communicate. Someone has to follow up. Someone has to record completion. Someone has to remember the next cycle.</p>
<p>AVA isn't designed merely to replace those rows with another database interface. She's designed to take responsibility for the operational renewal lifecycle represented by those rows.</p>

<h2>What AVA does not do with your domains</h2>
<p>AVA's scope matters as much as her capabilities. AVA does not become your registrar. AVA does not autonomously purchase domains. AVA does not independently authorize renewal payments. AVA does not select vendors on your behalf. AVA does not negotiate contracts. AVA does not make legal decisions. AVA does not replace the people responsible for consequential business decisions.</p>
<p>Her responsibility is narrower and clearer: own the operational renewal process.</p>

<h2>Related Renewal Operations resources</h2>
<p>Domain renewals are one part of a larger responsibility. See the complete operational lifecycle in <a href="{$this->pageUrl('ava', 'renewal-management')}">Renewal Management Software</a>, see how AVA detects renewal work across every supported asset in <a href="{$this->pageUrl('ava', 'detect/renewal-tracking-software')}">Renewal Tracking &amp; Reminder Software</a>, see how the same lifecycle applies to recurring subscriptions in <a href="{$this->pageUrl('ava', 'assets/saas')}">SaaS &amp; Software Renewal Management</a>, or see how it scales across a customer portfolio in <a href="{$this->pageUrl('ava', 'industries/managed-service-providers')}">Renewal Management for MSPs</a>.</p>

<h2>The real goal isn't tracking expiration dates</h2>
<p>The goal is keeping important domains active. Expiration monitoring is necessary because AVA needs to know when renewal work requires attention. Reminders are useful because people need visibility. Records matter because the organization needs continuity.</p>
<p>But none of those things individually define success. AVA considers the renewal complete only when the business obligation has been successfully renewed, records accurately reflect the outcome, supporting evidence has been archived, the Renewal Register has been updated, the next renewal cycle has been scheduled, and no further operational work remains.</p>
<p>A domain expiration alert is not success. A domain renewal reminder is not success. A completed domain renewal is success.</p>
HTML;
    }

    private function page3Faqs(): array
    {
        return [
            ['What is domain renewal tracking?', "Domain renewal tracking is the process of monitoring domain renewal and expiration information so domains can be renewed before they expire. AVA uses domain renewal tracking as the detection layer of a larger Renewal Operations lifecycle."],
            ['Does AVA monitor domain expiration dates?', "Domains are supported renewable assets in AVA Version 1, and Asset Watch is one of AVA's supported renewal sources. The exact technical monitoring mechanisms available through Asset Watch should be described according to the implemented product capabilities rather than assumed beyond the approved scope."],
            ['Can AVA automatically renew a domain?', "AVA's approved Version 1 scope excludes automatic payment execution, financial authorization, and autonomous purchasing. She coordinates Renewal Operations while humans retain consequential decision authority."],
            ['Can AVA manage domains for multiple customers?', "AVA is designed for organizations managing recurring customer obligations, including Managed Service Providers, IT service companies, digital agencies, and hosting providers. Her lifecycle explicitly includes identifying the customer associated with a renewal."],
            ['Can AVA prepare domain renewal emails?', "Yes. Draft generation and personalized communication preparation are supported capabilities. Customer-facing communication requires human approval before proceeding."],
            ['Does AVA replace my domain registrar?', "No. AVA's responsibility is Renewal Operations, not domain registration or registrar functionality."],
            ['What happens after the domain is renewed?', "AVA's definition of completion includes updating organizational records and the Renewal Register, archiving supporting evidence, scheduling the next renewal cycle, and ensuring no further operational work remains."],
            ['Can AVA track SSL certificates too?', "Yes. SSL certificates are another supported Version 1 renewal asset and should be managed as their own renewal transactions."],
            ['Is AVA just a domain expiration reminder?', "No. Detection and deadline monitoring are only the beginning of AVA's lifecycle. Her operational responsibility continues through human review, fulfillment, records, future scheduling, archive, and completion."],
            ['Who should use AVA for domain renewals?', "AVA is designed for organizations managing recurring operational obligations. Her approved customer scope includes Managed Service Providers, IT service companies, digital agencies, hosting providers, professional service firms, compliance teams, and organizations managing recurring customer renewals."],
        ];
    }

    private function page4Body(): string
    {
        return <<<HTML
<p><strong>Catch SSL renewal risk before expiration becomes a business problem.</strong></p>
<p>An SSL certificate can work perfectly today and become an operational problem when it expires. The expiration date may already be known. A notification may already have been sent. Someone may even have received a reminder.</p>
<p>But knowing a certificate is approaching expiration and making sure the renewal is successfully completed are two different responsibilities.</p>
<p>AVA is an AI Worker for Renewal Operations. She helps organizations detect SSL certificate renewal work, understand what requires attention, identify the customer and certificate involved, coordinate the appropriate people, track the renewal through fulfillment, record the outcome, archive supporting evidence, and schedule the next renewal cycle.</p>
<p>Don't just monitor SSL expiration. Own the renewal.</p>

<h2>Why SSL certificates still expire</h2>
<p>SSL certificate expiration is predictable. That doesn't make the operational process around renewal automatic.</p>
<p>Renewal information can become scattered across employee inboxes, shared mailboxes, spreadsheets, calendars, vendor systems, customer accounts, and individual employees.</p>
<p>One person may receive the expiration notice. Another may manage the website. Another may own the customer relationship. Someone else may need to approve an expense. The employee who originally configured the certificate may no longer be responsible for it.</p>
<p>The organization can know the expiration date and still lack clear operational ownership. That's the problem AVA is designed to solve.</p>

<h2>What is SSL certificate expiration tracking?</h2>
<p>SSL certificate expiration tracking helps organizations maintain visibility into certificates that are approaching expiration. That can involve information such as:</p>
<ul>
<li>Certificate or protected asset</li>
<li>Customer</li>
<li>Expiration date</li>
<li>Renewal status</li>
<li>Responsible contact</li>
<li>Upcoming deadline</li>
<li>Renewal reminders</li>
<li>Related communications</li>
<li>Renewal history</li>
<li>Next renewal cycle</li>
</ul>
<p>Expiration visibility matters. An organization cannot act on a renewal it doesn't know is approaching. But tracking the expiration date is only the first stage. The certificate still needs to make it safely through the renewal process.</p>

<h2>SSL expiration monitoring is detection. Renewal Operations is ownership.</h2>
<p>An SSL expiration monitor answers: which certificate is approaching expiration?</p>
<p>AVA's responsibility continues with: what needs to happen next, who needs to be involved, and has the renewal actually been completed?</p>
<p>That's the distinction between monitoring and operational ownership. Once SSL renewal work is detected, someone may still need to understand the renewal requirement, identify the correct customer, identify the certificate or associated asset, find the responsible contact, prepare necessary communication, obtain human approval, track fulfillment, record the result, archive supporting evidence, and schedule the next renewal cycle.</p>
<p>AVA is designed to keep those activities connected to the renewal.</p>

<h2>Meet AVA: a Renewal Operations Worker for SSL renewals</h2>
<p>AVA isn't simply an SSL expiration notification system. She is a Renewal Operations Worker on the UNITELO platform. SSL certificates are one of the renewable asset types she supports.</p>
<p>When SSL renewal work is detected, AVA can turn it into an accountable renewal transaction. The transaction remains active through the operational lifecycle rather than ending when an alert is delivered.</p>
<p>That changes the objective from "we warned someone" to "the renewal reached completion."</p>

<h2>How AVA manages an SSL certificate renewal</h2>

<h3>1. Detect</h3>
<p>AVA's responsibility begins when renewal work is detected. Version 1 supports Gmail, Asset Watch, and Manual Trigger. This allows renewal work to enter the lifecycle from supported monitored activity, incoming communications, or a person initiating the transaction. Detection establishes that something requires attention. It doesn't close the work.</p>

<h3>2. Understand</h3>
<p>Renewal notices don't always look the same. Vendors use different terminology. Customers communicate differently. Information can arrive in different formats. AVA uses AI to interpret renewal intent and business context. The objective is to determine what the renewal is about and what operational action is required.</p>

<h3>3. Identify the customer</h3>
<p>For organizations managing certificates across multiple customers, customer identification is essential. A Managed Service Provider may support many client environments. A digital agency may manage certificates associated with many websites. A hosting provider may have renewal obligations spread across customer accounts. AVA connects the renewal transaction to the appropriate customer relationship, turning a technical expiration event into accountable business work.</p>

<h3>4. Identify the asset</h3>
<p>AVA identifies the renewable asset associated with the transaction. For this workflow, the supported renewable asset is the SSL certificate. The organization can then move from a generic alert to a contextualized renewal: this certificate requires renewal for this customer.</p>

<h3>5. Identify the responsible contact</h3>
<p>A known expiration date doesn't guarantee someone is accountable for the next action. AVA identifies the responsible contact associated with the renewal. This helps prevent SSL renewal work from sitting between departments or employees because everyone assumes someone else is handling it.</p>

<h3>6. Prepare communication</h3>
<p>When communication is necessary, AVA can prepare personalized renewal communications based on the context available to her. This can support coordination around the renewal without turning AVA into an autonomous communication system. Human review remains part of the lifecycle.</p>

<h3>7. Require human review</h3>
<p>AVA owns operational coordination. Humans retain consequential decision authority. No customer-facing communication proceeds without human approval in AVA Version 1.</p>
<p>AVA also does not own financial approval, payment authorization, vendor selection, contract negotiation, legal decisions, or executive approval. And she does not automatically execute payments or make autonomous purchases.</p>
<p><strong>AVA owns the process. Humans own the decisions.</strong></p>

<h3>8. Track fulfillment</h3>
<p>An approved renewal is not necessarily a completed renewal. AVA continues tracking the transaction through fulfillment. This matters because the business outcome isn't "someone approved the SSL renewal." The business outcome is "the obligation was successfully renewed." AVA keeps the transaction accountable until the renewal reaches its required outcome.</p>

<h3>9. Record the outcome</h3>
<p>After fulfillment, AVA updates the relevant renewal records. The Renewal Register is updated as part of the completed lifecycle. This creates continuity between the current renewal and future renewal work. Instead of reconstructing the history later, the organization maintains an operational record of what happened.</p>

<h3>10. Archive supporting evidence</h3>
<p>AVA maintains audit history and archives supporting renewal evidence. That helps preserve the story of the transaction: what renewal was detected, what asset and customer were involved, what actions occurred, and what was the outcome. Renewal history becomes organizational information rather than employee memory.</p>

<h3>11. Schedule the next renewal</h3>
<p>Renewing an SSL certificate doesn't eliminate the recurring obligation. It creates another future renewal. AVA schedules the next renewal cycle so monitoring can be re-established.</p>
<p>The process becomes: Detect, Renew, Record, Schedule, Monitor Again. That's what turns an expiration event into a managed lifecycle.</p>

<h2>One SSL renewal. One accountable transaction.</h2>
<p>A certificate renewal can involve multiple emails, reminders, contacts, approvals, documents, and invoices. AVA keeps them associated with one renewal transaction. That transaction becomes the unit of accountability.</p>
<p>The work doesn't become "done" simply because an employee replied to an email or dismissed an alert. The transaction stays open until the renewal reaches completion.</p>

<h2>SSL renewal tracking for Managed Service Providers</h2>
<p>Managed Service Providers may be responsible for recurring technology obligations across many customers. SSL certificates can be one of those obligations. The challenge becomes larger as customer count grows: different certificates, different customers, different dates, different contacts, different systems, different renewal communications.</p>
<p>AVA's lifecycle gives the renewal structure: Customer, Asset, Contact, Action, Human Review, Fulfillment, Outcome. Instead of depending on individual technicians or account managers to remember every recurring obligation, each SSL renewal can become an accountable transaction.</p>

<h2>SSL renewal tracking for digital agencies</h2>
<p>Digital agencies frequently operate websites and digital infrastructure on behalf of customers. That can leave the agency responsible for recurring technical obligations long after the original project has launched.</p>
<p>An SSL certificate is easy to forget precisely because it may require little attention while everything is working correctly. Until it doesn't.</p>
<p>AVA helps agencies maintain operational continuity around those recurring obligations even when teams, clients, and responsibilities change.</p>

<h2>Manage domain and SSL renewals without treating them as the same obligation</h2>
<p>Domains and SSL certificates often exist in the same digital environment. But they aren't the same renewable asset. A domain can remain registered while its certificate expires. A certificate can remain valid while the associated domain approaches renewal.</p>
<p>Each therefore deserves its own renewal transaction. AVA can support both while maintaining separate accountability for each obligation. One asset renewal should not disappear inside another asset's status.</p>

<h2>SSL renewal spreadsheets still require someone to operate them</h2>
<p>A spreadsheet can store certificate information, customers, expiration dates, owners, renewal status, and notes. But the spreadsheet cannot take responsibility for what those fields mean.</p>
<p>Someone still needs to maintain the information, check upcoming dates, investigate the renewal, coordinate communication, follow up, record completion, and schedule the next cycle.</p>
<p>AVA's purpose isn't merely to provide another place to store SSL expiration dates. Her purpose is to own the operational renewal work those dates create.</p>

<h2>What AVA does not do with SSL certificates</h2>
<p>AVA's approved responsibility is Renewal Operations. That distinction matters. AVA should not be confused with certificate infrastructure or certificate lifecycle tooling outside her approved scope.</p>
<p>AVA does not gain authority to make financial, legal, purchasing, vendor, or executive decisions simply because she is responsible for coordinating the renewal. Her role is to make sure renewal work doesn't quietly disappear between detection and completion.</p>

<h2>What does a completed SSL renewal mean to AVA?</h2>
<p>AVA has a strict definition of done. The renewal isn't complete merely because an expiration alert was generated, someone received a reminder, an email was drafted, or an approval was recorded.</p>
<p>AVA considers the renewal complete only when the business obligation has been successfully renewed, organizational records reflect the outcome, supporting evidence has been archived, the Renewal Register has been updated, the next renewal cycle has been scheduled, and no further operational work remains.</p>
<p>This is the fundamental difference between alerting and ownership. An SSL expiration alert is not success. A completed SSL renewal is success.</p>

<h2>Related Renewal Operations resources</h2>
<p>SSL renewals are one part of a larger responsibility. See the complete operational lifecycle in <a href="{$this->pageUrl('ava', 'renewal-management')}">Renewal Management Software</a>, see how AVA detects renewal work across every supported asset in <a href="{$this->pageUrl('ava', 'detect/renewal-tracking-software')}">Renewal Tracking &amp; Reminder Software</a>, see how she manages a closely related recurring obligation in <a href="{$this->pageUrl('ava', 'assets/domains')}">Domain Renewal &amp; Expiration Tracking</a>, or see how the same lifecycle applies to recurring subscriptions in <a href="{$this->pageUrl('ava', 'assets/saas')}">SaaS &amp; Software Renewal Management</a>.</p>

<h2>Stop depending on someone noticing the SSL reminder</h2>
<p>Expiration monitoring matters. But businesses ultimately don't need certificates to be monitored. They need certificates not to quietly expire.</p>
<p>Give that operational responsibility an owner.</p>
HTML;
    }

    private function page4Faqs(): array
    {
        return [
            ['What is SSL certificate expiration tracking?', "SSL certificate expiration tracking is the process of maintaining visibility into certificates and their upcoming expiration or renewal activity so organizations can act before an important certificate expires. AVA uses that visibility as part of a broader Renewal Operations lifecycle."],
            ['Can AVA track SSL certificate renewals?', "Yes. SSL certificates are explicitly included among AVA's supported Version 1 renewal assets."],
            ['How does AVA detect SSL renewal work?', "AVA Version 1 supports Gmail, Asset Watch, and Manual Trigger as renewal sources. The approved specification does not define the underlying technical certificate discovery or monitoring mechanism beyond Asset Watch, so specific monitoring methods should depend on the implemented product."],
            ['Does AVA automatically renew SSL certificates?', "AVA's Version 1 scope excludes automatic payment execution, financial authorization, and autonomous purchasing. Her responsibility is operational coordination rather than autonomous purchasing authority."],
            ['Does AVA install SSL certificates?', "AVA's approved business contract defines her responsibility as Renewal Operations and does not establish certificate installation or technical deployment as an AVA capability. Those functions should not be assumed."],
            ['Does AVA replace SSL monitoring software?', "AVA's approved scope supports SSL certificates as renewable assets and Asset Watch as a renewal source. Whether AVA replaces a particular certificate monitoring product depends on the technical capabilities implemented in Asset Watch. AVA's defined responsibility is the renewal operation that follows detection."],
            ['Can AVA manage SSL renewals for multiple customers?', "AVA is designed for organizations with recurring customer renewals, and her lifecycle explicitly includes customer identification. That makes multi-customer renewal operations part of the intended business context."],
            ['Can AVA prepare renewal communications?', "Yes. Draft generation and personalized communication preparation are supported capabilities. Customer-facing communication requires human approval."],
            ['What happens after the certificate renewal is completed?', "AVA's definition of done includes updating records and the Renewal Register, archiving supporting evidence, scheduling the next renewal cycle, and ensuring no further operational work remains."],
            ['Can AVA manage domain renewals too?', "Yes. Domains are also explicitly supported Version 1 renewable assets and should be managed as separate renewal transactions."],
            ['How is AVA different from an SSL expiration reminder?', "An expiration reminder creates awareness. AVA's operational responsibility continues beyond detection through understanding, customer and asset identification, contact identification, communication, human review, fulfillment, records, future scheduling, archive, and completion."],
        ];
    }

    private function page5Body(): string
    {
        return <<<HTML
<p><strong>Know what's renewing. Know who needs to act. Make sure the renewal reaches completion.</strong></p>
<p>Software subscriptions are easy to start. They're harder to keep operationally organized. One application renews annually. Another renews monthly. A license expires on a fixed date. A renewal notice goes to an employee who originally created the account. An invoice reaches Finance. Operations knows who uses the software. Management needs to approve what happens next.</p>
<p>And somewhere in the middle of all those systems and people, someone has to make sure the renewal actually gets handled.</p>
<p>AVA is an AI Worker for Renewal Operations. She helps organizations detect SaaS and software renewal work, understand the obligation, identify the relevant customer, asset, and contact, coordinate human review, track fulfillment, record the outcome, archive evidence, and schedule the next renewal cycle.</p>
<p>AVA doesn't make your purchasing decisions. She makes sure the renewal operation doesn't get lost between them.</p>

<h2>Why SaaS renewals become an operational problem</h2>
<p>A modern organization can depend on dozens or hundreds of recurring software products. Some are organization-wide. Some belong to individual departments. Some support specific customers. Some may have been purchased by employees who are no longer responsible for them.</p>
<p>Renewal information can become scattered across employee inboxes, shared mailboxes, spreadsheets, calendars, vendor portals, accounting software, and individual employees.</p>
<p>One system contains the renewal date. Another contains the invoice. Someone knows why the software exists. Someone else knows who uses it. Another person has financial authority. The renewal itself sits between them.</p>
<p>The problem isn't simply subscription visibility. It's fragmented renewal responsibility.</p>

<h2>What is SaaS renewal management?</h2>
<p>SaaS renewal management is the operational process of keeping recurring software and subscription renewals visible, organized, and moving toward an outcome before their deadlines. That can involve:</p>
<ul>
<li>Software or subscription</li>
<li>Customer or organization</li>
<li>Renewal date</li>
<li>Expiration date</li>
<li>Renewal requirements</li>
<li>Responsible contact</li>
<li>Renewal status</li>
<li>Communications</li>
<li>Human approvals</li>
<li>Supporting records</li>
<li>Renewal history</li>
<li>Next renewal cycle</li>
</ul>
<p>Tracking those elements creates visibility. Managing the renewal requires something more: ownership of the work they create.</p>

<h2>A tracker tells you what's coming. Renewal Operations owns the work.</h2>
<p>A software renewal tracker tells you what's coming. That's important. You need to know which subscriptions are approaching renewal, which licenses are expiring, which renewal notices have arrived, who is associated with them, and which deadlines require attention.</p>
<p>But knowing that a SaaS subscription renews next month doesn't make the operational work disappear. Someone still needs to determine what the notice means, identify the software involved, determine the customer relationship, and find the responsible contact. Communication may be necessary. Human approval may be required. Fulfillment needs to be tracked. The outcome needs to be recorded. And if the obligation continues, the next renewal cycle needs to be scheduled.</p>
<p>Tracking identifies the work. Renewal Operations owns the work.</p>

<h2>Meet AVA: your SaaS Renewal Operations Worker</h2>
<p>AVA is not simply a subscription reminder. She's a Renewal Operations Worker on the UNITELO platform. SaaS subscriptions and licenses are among the recurring obligations she is designed to manage.</p>
<p>When software renewal work is detected, AVA organizes it into a renewal transaction. That transaction becomes the unit of accountability. Instead of leaving the renewal fragmented across emails, reminders, invoices, contacts, and approvals, the operational work remains connected until the transaction reaches completion.</p>

<h2>How AVA manages a SaaS or software renewal</h2>

<h3>1. Detect the renewal</h3>
<p>AVA's responsibility starts when renewal work is detected. Version 1 supports three renewal sources: Gmail, Asset Watch, and Manual Trigger. A software renewal notice arriving in Gmail can create renewal work. A supported monitored asset can surface renewal activity. And an employee can manually initiate a renewal transaction when necessary. The result is the same: the renewal enters an accountable lifecycle.</p>

<h3>2. Understand the renewal</h3>
<p>Software vendors don't all communicate the same way. Renewal information can appear in notices, invoices, email threads, or other supported inputs, and the language and context can vary. AVA uses AI where interpretation is necessary, including understanding renewal intent, identifying renewal assets, identifying customers and contacts, classifying renewal requests, understanding business context, preparing personalized communications, and recommending appropriate next actions.</p>

<h3>3. Identify the customer</h3>
<p>Not every software renewal belongs only to the organization itself. Managed Service Providers, IT service companies, agencies, hosting providers, and other service businesses can manage recurring obligations associated with customers. AVA's lifecycle therefore includes customer identification, connecting the renewal to the appropriate customer relationship before further action is taken. This matters when the same organization manages software obligations across many clients.</p>

<h3>4. Identify the software or subscription</h3>
<p>AVA identifies the renewable asset associated with the transaction. For this workflow, that may be a supported SaaS subscription or license. Instead of "we received another renewal notice," the organization can work from "this renewal transaction concerns this software obligation for this customer or organization."</p>

<h3>5. Identify the responsible contact</h3>
<p>Software ownership can become surprisingly unclear over time. The employee who purchased the product may not be the person who uses it. The user may not control the budget. Finance may receive the invoice but not understand the operational requirement. An account manager may know the customer but not the software. AVA identifies the responsible contact associated with the renewal so the work has a clear path forward.</p>

<h3>6. Prepare renewal communication</h3>
<p>When communication is required, AVA can prepare personalized renewal communications using the available business context. That communication could support the operational process surrounding the renewal. But AVA does not independently decide the commercial outcome. Human review remains required.</p>

<h3>7. Coordinate human review</h3>
<p>AVA owns the process. Humans own the decisions. That distinction is especially important for SaaS and software renewals. AVA does not decide whether your company should keep a vendor, whether the price is acceptable, whether a different vendor should be selected, whether a contract should be negotiated, whether payment should be authorized, whether a purchase should be made, or whether legal terms should be accepted. Those decisions remain with the appropriate people. AVA's job is to keep the renewal operation organized around those decisions.</p>

<h3>8. Track fulfillment</h3>
<p>A renewal doesn't become complete simply because a manager approves it. There may still be operational work required. AVA tracks the renewal through fulfillment, and the transaction remains open until the business obligation has actually reached the required outcome. Approval is a decision. Fulfillment is an outcome. AVA coordinates the process around both while leaving decision authority with people.</p>

<h3>9. Record the outcome</h3>
<p>Once the renewal reaches its outcome, AVA updates the renewal records. The Renewal Register is updated. The organization now has a record of what happened rather than another completed task whose context disappears over time. That history becomes useful when the next renewal cycle arrives.</p>

<h3>10. Archive supporting evidence</h3>
<p>AVA maintains audit history and archives supporting renewal evidence. This preserves continuity around the transaction. The organization can maintain a record of the renewal instead of relying on old inboxes, personal notes, or employee memory to reconstruct what happened.</p>

<h3>11. Schedule the next renewal</h3>
<p>A completed SaaS renewal frequently creates another future renewal obligation. AVA schedules the next renewal cycle, and future monitoring is re-established. The current transaction can then reach completion. The process becomes: Detect, Understand, Decide, Fulfill, Record, Schedule, Monitor Again. That is how recurring software becomes a managed operational lifecycle rather than a recurring surprise.</p>

<h2>One software renewal. One accountable transaction.</h2>
<p>Software renewals can create a surprising amount of activity. A single renewal may involve multiple emails, invoices, reminders, approvals, documents, and contacts. AVA keeps that activity connected to one renewal transaction. One renewal remains one transaction, and the transaction is the unit of accountability. This means the organization doesn't have to mistake communication activity for operational completion.</p>

<h2>SaaS renewal management without taking away human control</h2>
<p>AI workers should not receive unlimited authority simply because a workflow can be automated. AVA's boundaries are deliberate.</p>
<p>AVA can detect renewal work, interpret renewal intent, identify customers, identify assets, match contacts, prepare communications, coordinate human review, track renewal progress, update renewal records, maintain audit history, and schedule future renewals.</p>
<p>But AVA does not own financial approval, vendor selection, contract negotiation, payment authorization, legal decisions, or executive approval. Version 1 also excludes automatic payment execution and autonomous purchasing. This allows organizations to automate operational coordination without automating consequential business authority.</p>

<h2>SaaS renewal management is not the same as SaaS procurement</h2>
<p>These categories can overlap, but they are not the same responsibility. Procurement may involve vendor evaluation, vendor selection, purchasing, negotiation, commercial terms, and financial authorization. Those responsibilities are outside AVA's approved scope.</p>
<p>AVA is focused on the renewal operation. Her question isn't "which vendor should the company buy from?" Her question is "there is a renewal obligation. What needs to happen to move it to an approved and recorded outcome before the deadline?" That boundary keeps AVA's responsibility clear.</p>

<h2>SaaS renewal management is not the same as software spend optimization</h2>
<p>Organizations may want to reduce software spending, identify unused applications, consolidate vendors, or optimize license counts. Those can be valuable business activities, but they aren't automatically AVA responsibilities.</p>
<p>AVA exists to reduce renewal risk and improve Renewal Operations. If an activity doesn't support that responsibility, it shouldn't be added to AVA simply because it relates to software. This keeps the Worker focused.</p>

<h2>SaaS renewal management for Managed Service Providers</h2>
<p>Managed Service Providers can face an additional layer of complexity. The software renewal may not belong directly to the MSP. It may belong to a customer, and different clients can have different software products, renewal dates, contacts, requirements, approvals, and supporting documents.</p>
<p>AVA's customer identification stage helps connect each renewal to the appropriate client relationship. The asset is identified. The responsible contact is identified. The transaction remains accountable until completion. See how this applies across an entire customer portfolio in <a href="{$this->pageUrl('ava', 'industries/managed-service-providers')}">Renewal Management for MSPs</a>.</p>

<h2>Software renewals shouldn't depend on the employee who bought the software</h2>
<p>One of the risks of recurring software is organizational memory. Someone signs up for a product. Their email becomes the account contact. They understand why the software exists and know when it renews. Then responsibilities change: the employee moves teams, or leaves. The subscription remains.</p>
<p>A resilient renewal process should belong to the organization rather than to the memory of the employee who created the account. AVA creates continuity by keeping renewal work inside a defined operational lifecycle.</p>

<h2>Replace the SaaS renewal spreadsheet, or make it less important</h2>
<p>Spreadsheets can be useful. They can store software names, renewal dates, customers, owners, statuses, and notes. The limitation isn't necessarily the spreadsheet itself. It's the operational work required around it.</p>
<p>Someone must keep the spreadsheet current, check approaching deadlines, interpret incoming notices, coordinate approvals, follow up, update the outcome, and schedule the next cycle. AVA's value isn't simply moving those rows into a new interface. It's giving the work represented by those rows an operational owner.</p>

<h2>What AVA does not do with your software renewals</h2>
<p>AVA does not autonomously decide whether your organization should renew a SaaS subscription. She does not choose replacement vendors. She does not negotiate your contract. She does not authorize payment. She does not autonomously purchase software. She does not provide legal approval. She does not own your accounting system.</p>
<p>Those boundaries remain intact. AVA owns the operational lifecycle around the renewal. Your organization retains authority over the decisions inside that lifecycle.</p>

<h2>A renewal reminder isn't the outcome</h2>
<p>Imagine AVA detects that an important software obligation is approaching renewal. The renewal gets classified. The correct customer and software asset are identified. The responsible person is found. Communication is prepared. Human review occurs. The organization makes its decision. The renewal is fulfilled. The outcome is recorded. Evidence is archived. The next cycle is scheduled.</p>
<p>Now the renewal is complete. That's fundamentally different from "we sent someone a reminder 30 days before expiration." AVA measures success at the end of the operational lifecycle, not at the beginning.</p>

<h2>What does "done" mean for a SaaS renewal?</h2>
<p>AVA considers the renewal complete only when the business obligation has been successfully renewed, organizational records accurately reflect the outcome, supporting evidence has been archived, the Renewal Register has been updated, the next renewal cycle has been scheduled, and no further operational work remains.</p>
<p>A generated email isn't success. A sent email isn't success. A reminder isn't success. A completed renewal is success.</p>

<h2>Related Renewal Operations resources</h2>
<p>SaaS renewals are one part of a larger responsibility. See the complete operational lifecycle in <a href="{$this->pageUrl('ava', 'renewal-management')}">Renewal Management Software</a>, see how AVA detects renewal work across every supported asset in <a href="{$this->pageUrl('ava', 'detect/renewal-tracking-software')}">Renewal Tracking &amp; Reminder Software</a>, or see how she manages a closely related recurring obligation in <a href="{$this->pageUrl('ava', 'assets/domains')}">Domain Renewal &amp; Expiration Tracking</a>.</p>

<h2>Software renewals deserve more than a reminder</h2>
<p>Your team should make the decisions that affect your business. They shouldn't have to spend their time chasing recurring renewal work across inboxes, spreadsheets, calendars, vendor portals, and individual employees.</p>
<p>AVA handles the operational responsibility. Your people remain in control.</p>
HTML;
    }

    private function page5Faqs(): array
    {
        return [
            ['What is SaaS renewal management?', "SaaS renewal management is the operational process of monitoring, organizing, and coordinating recurring software subscription renewals before their deadlines. AVA extends the process from detection through human review, fulfillment, records, evidence, and future scheduling."],
            ['Can AVA track SaaS subscription renewals?', "Yes. SaaS subscriptions are explicitly included among AVA's supported Version 1 renewable assets."],
            ['Can AVA manage software license renewals?', "Yes. Licenses are also explicitly supported Version 1 renewal assets."],
            ['Where can AVA detect software renewal work?', "AVA Version 1 supports Gmail, Asset Watch, and Manual Trigger as renewal sources."],
            ['Does AVA decide whether we should renew a SaaS product?', "No. AVA owns Renewal Operations, not the organization's commercial decision-making authority. The appropriate people remain responsible for consequential renewal decisions."],
            ['Does AVA negotiate SaaS contracts?', "No. Contract negotiation is explicitly outside AVA's approved responsibility."],
            ['Does AVA automatically pay software renewals?', "No. Automatic payment execution, payment authorization, financial authorization, and autonomous purchasing are outside AVA's Version 1 scope."],
            ['Is AVA a SaaS spend management platform?', "No. AVA's approved business responsibility is Renewal Operations. The contract does not establish general SaaS spend optimization, vendor consolidation, usage optimization, or procurement management as AVA responsibilities."],
            ['Can AVA prepare software renewal communications?', "Yes. Draft generation and personalized communication preparation are supported capabilities. Customer-facing communication requires human approval."],
            ['Can AVA manage software renewals for multiple customers?', "AVA is designed for organizations managing recurring customer obligations, and customer identification is a defined stage of her renewal lifecycle."],
            ['What happens after a software renewal is completed?', "AVA updates renewal records and the Renewal Register, archives supporting evidence, schedules the next renewal cycle, and ensures no further operational work remains before the transaction reaches completion."],
            ['How is AVA different from SaaS renewal reminder software?', "Reminder software primarily creates awareness around an upcoming renewal. AVA uses detection as the beginning of a larger operational lifecycle that continues through understanding, customer and asset identification, human review, fulfillment, records, future scheduling, archive, and completion."],
        ];
    }

    private function page6Body(): string
    {
        return <<<HTML
<p><strong>Give every client renewal an operational owner.</strong></p>
<p>Managed Service Providers don't manage one technology environment. They manage many. Every customer can bring another set of recurring obligations: domains, SSL certificates, hosting services, software subscriptions, licenses, and maintenance agreements. Each can have its own renewal date, customer contact, vendor communication, approval process, and operational history.</p>
<p>As the customer portfolio grows, so does the renewal workload.</p>
<p>AVA is an AI Worker for Renewal Operations built to help organizations manage recurring operational obligations across customers. She detects renewal work, identifies the customer and asset involved, finds the responsible contact, prepares communications, coordinates human review, tracks fulfillment, records outcomes, archives evidence, and schedules the next renewal cycle.</p>
<p>Your MSP manages the customer relationship. AVA owns the renewal operation.</p>

<h2>The MSP renewal problem isn't one deadline. It's hundreds of recurring obligations.</h2>
<p>Imagine an MSP with 50 customers. One customer has several domains. Another has SSL certificates. Another has software subscriptions. Another depends on licenses. Another has a maintenance agreement. Some renewal notices arrive by email. Some obligations are already being monitored. Some are known only by the employees responsible for the account. Now multiply those obligations across the entire customer base.</p>
<p>The challenge quickly stops being "when does this expire?" It becomes "how do we make sure every recurring customer obligation gets handled by the right people before anything important expires?" That's a Renewal Operations problem.</p>

<h2>Why MSP renewals become fragmented</h2>
<p>Renewal information can live across technician inboxes, shared mailboxes, account managers, spreadsheets, calendars, vendor portals, accounting software, customer records, and individual employees.</p>
<p>Different people see different parts of the renewal. An account manager understands the customer. A technician understands the asset. Finance may receive an invoice. Management may provide approval. A vendor sends the renewal notice. Someone still has to connect everything.</p>
<p>Without clear operational ownership, responsibility becomes fragmented, and fragmented responsibility creates renewal risk.</p>

<h2>What is MSP renewal management?</h2>
<p>MSP renewal management is the operational process of tracking and coordinating recurring obligations across managed customers so important renewals reach completion before expiration. That may involve identifying the customer, the renewable asset, the renewal deadline, the responsible contact, the renewal requirements, required communication, required human approval, current renewal status, supporting records, the completed outcome, and the next renewal cycle.</p>
<p>For an MSP, the customer dimension makes this especially important. A renewal isn't simply "SSL certificate expires soon." It is "this SSL certificate for this customer requires renewal, and this is the transaction responsible for making sure the work reaches completion."</p>

<h2>Meet AVA: a Renewal Operations Worker for MSPs</h2>
<p>AVA is not another inbox rule. She isn't a reminder bot. She isn't a spreadsheet replacement disguised as AI.</p>
<p>AVA is a Renewal Operations Worker on the UNITELO platform. Her responsibility is to make sure important recurring business obligations don't quietly expire because of manual work, fragmented systems, or human oversight. Managed Service Providers are one of the organization types AVA is designed to support, because recurring customer obligations deserve continuous operational ownership.</p>

<h2>One customer can have many renewable assets</h2>
<p>AVA Version 1 supports renewal operations involving:</p>
<ul>
<li><strong>Domains:</strong> track recurring domain obligations associated with customers before expiration creates operational disruption.</li>
<li><strong>SSL Certificates:</strong> keep SSL renewal work visible and moving toward completion before certificates quietly expire.</li>
<li><strong>Hosting Services:</strong> coordinate recurring hosting renewal obligations associated with customer environments.</li>
<li><strong>SaaS Subscriptions:</strong> track recurring software subscription renewal work without giving AVA purchasing or financial decision authority.</li>
<li><strong>Licenses:</strong> maintain operational ownership over supported license renewal obligations.</li>
<li><strong>Maintenance Agreements:</strong> track recurring maintenance agreement renewals through their operational lifecycle.</li>
<li><strong>Insurance Policies:</strong> included among AVA's supported renewal assets where they form part of the organization's recurring operational obligations.</li>
</ul>
<p>Different asset types. One Renewal Operations model.</p>

<h2>One renewal becomes one accountable transaction</h2>
<p>This is particularly important for MSPs. A single customer renewal may involve multiple emails, invoices, reminders, documents, approvals, and contacts. AVA does not treat those as six unrelated pieces of work. One renewal remains one transaction, and that transaction becomes the unit of accountability.</p>
<p>This makes it easier to distinguish between activity and completion. Five emails don't mean the renewal is finished. Three reminders don't mean the renewal is finished. An approval doesn't necessarily mean the renewal is finished. The transaction remains accountable until the renewal reaches completion.</p>

<h2>How AVA manages an MSP renewal</h2>

<h3>1. Detect</h3>
<p>AVA's responsibility starts when renewal work is detected. Version 1 supports Gmail, Asset Watch, and Manual Trigger, giving renewal work multiple supported entry points without changing the underlying responsibility. Once detected, the renewal enters the lifecycle.</p>

<h3>2. Understand</h3>
<p>Customer renewals don't arrive in a standardized format. Different vendors communicate differently, different customers have different contexts, and different assets have different renewal requirements. AVA uses AI where interpretation is necessary to understand renewal intent, classify the request, identify relevant context, and recommend appropriate next actions.</p>

<h3>3. Identify the customer</h3>
<p>For an MSP, this is one of the most important stages. AVA determines which customer relationship the renewal belongs to, turning disconnected renewal information into customer-specific operational work. Instead of "we received a renewal notice," the MSP can work from "this renewal belongs to Customer A."</p>

<h3>4. Identify the asset</h3>
<p>Next, AVA identifies what is being renewed: a domain, an SSL certificate, a hosting service, a SaaS subscription, a license, or a maintenance agreement. Connecting the customer and asset gives the renewal its operational identity: Customer A, Domain A, Renewal Transaction, rather than "email #14,382, somebody should probably look at this."</p>

<h3>5. Identify the responsible contact</h3>
<p>AVA identifies the responsible contact associated with the renewal. Depending on the transaction, different people may need to participate. The important point is that responsibility doesn't remain ambiguous: the renewal has a customer, an asset, the appropriate contact context, and a transaction responsible for moving the work forward.</p>

<h3>6. Prepare communication</h3>
<p>When the renewal requires communication, AVA can prepare personalized communications using the context she has identified. This can reduce repetitive coordination work without removing human control. No customer-facing communication proceeds without approval in Version 1.</p>

<h3>7. Coordinate human review</h3>
<p>Managed Service Providers still control their business decisions. AVA doesn't decide whether a vendor should be retained, whether a customer should approve an expense, whether a price is acceptable, whether a contract should be negotiated, whether payment should be authorized, whether a purchase should occur, or whether a legal decision should be made. Those responsibilities remain with people. AVA coordinates the operational process surrounding them.</p>
<p><strong>AVA owns the process. Humans own the decisions.</strong></p>

<h3>8. Track fulfillment</h3>
<p>A customer approves the renewal. Is the transaction finished? Not necessarily. The underlying obligation still has to reach its outcome. AVA tracks renewal progress through fulfillment so the MSP doesn't confuse an approval, email, or reminder with successful completion.</p>

<h3>9. Record the outcome</h3>
<p>Once the renewal is fulfilled, AVA updates the renewal records and Renewal Register. The transaction now contains organizational history, so when the same obligation returns in the future, the MSP doesn't need to reconstruct the previous cycle from employee memory and old inboxes.</p>

<h3>10. Schedule the next renewal</h3>
<p>Recurring obligations don't disappear after fulfillment. A renewed domain creates another future domain renewal. A renewed software subscription creates another renewal cycle. A renewed maintenance agreement can create another future obligation. AVA schedules the next renewal cycle so monitoring is re-established, and the current renewal can then move toward completion.</p>

<h3>11. Archive and complete</h3>
<p>Supporting evidence is archived. Audit history is maintained. Records reflect the outcome. The next renewal has been scheduled. No further operational work remains. Now the transaction is complete. That's AVA's definition of success.</p>

<h2>Manage renewals by customer, not by whoever received the email</h2>
<p>This is one of the most important operational changes AVA can create for an MSP. Without structured ownership, renewal work can become organized around communication channels: Sarah received this email, Finance received this invoice, James has a calendar reminder, the customer mentioned this to their account manager. That's fragile.</p>
<p>AVA's lifecycle reorganizes the work around the business obligation: which customer, which asset, which renewal, which contact, what is the status, what needs to happen next, has it reached completion. The renewal belongs to the organization, not to the employee who happened to receive the message.</p>

<h2>Renewal management for growing MSPs</h2>
<p>Manual renewal tracking can work when an MSP has a small number of customers and obligations. The difficulty increases as the business grows. More customers create more assets. More assets create more renewal dates. More dates create more notices. More notices create more coordination. More coordination creates more opportunities for something to be forgotten.</p>
<p>Adding customers shouldn't require employees to carry an ever-growing number of renewal obligations in their heads. AVA creates an operational owner for that recurring work.</p>

<h2>Domain renewal management for MSPs</h2>
<p>Domains are particularly well suited to the MSP renewal model. A customer may have one domain. Another may have ten. Different domains may have different renewal cycles and operational contexts. AVA can support the renewal operation around those domains while connecting each transaction to the correct customer and contact. The objective isn't simply to know the expiration date. It's to make sure the domain renewal reaches completion. See <a href="{$this->pageUrl('ava', 'assets/domains')}">Domain Renewal &amp; Expiration Tracking</a>.</p>

<h2>SSL renewal management for MSPs</h2>
<p>SSL certificates create another recurring technical obligation. The certificate can be working correctly today while an expiration deadline approaches in the future. AVA supports SSL certificates as renewable assets and keeps the renewal transaction connected to the appropriate customer context, so the expiration event becomes operational work rather than just another alert. See <a href="{$this->pageUrl('ava', 'assets/ssl-certificates')}">SSL Certificate Renewal &amp; Expiration Tracking</a>.</p>

<h2>SaaS and software renewal management for MSPs</h2>
<p>MSPs may also manage recurring software and license obligations associated with customers. AVA can coordinate the renewal lifecycle around those supported assets while keeping consequential purchasing and financial decisions with humans. That boundary matters: AVA manages Renewal Operations. She doesn't become the MSP's autonomous procurement department. See <a href="{$this->pageUrl('ava', 'assets/saas')}">SaaS &amp; Software Renewal Management</a>.</p>

<h2>Replace fragmented renewal responsibility, not your team</h2>
<p>AVA isn't designed to replace technicians. She doesn't replace account managers. She doesn't replace Finance. She doesn't replace executives. And she doesn't remove human approval.</p>
<p>Her job is narrower: remove the repetitive, time-sensitive responsibility of making sure renewal work doesn't disappear between all those people. Your technicians can remain technicians. Your account managers can manage customers. Your leaders can make business decisions. AVA keeps Renewal Operations moving.</p>

<h2>What AVA does not own for an MSP</h2>
<p>AVA intentionally does not own financial approval, vendor selection, contract negotiation, payment authorization, legal decisions, executive approval, CRM ownership, or accounting ownership.</p>
<p>Version 1 also excludes automatic payment execution, autonomous purchasing, cross-worker collaboration, ERP orchestration, and multi-department workflow orchestration.</p>
<p>These boundaries matter. AVA should be hired for a clear business responsibility rather than slowly expanding into every activity surrounding the customer. That responsibility is Renewal Operations.</p>

<h2>Stop measuring renewal management by reminders sent</h2>
<p>For an MSP, success isn't "we sent the customer three reminders." It isn't "the expiration date was in our spreadsheet." It isn't "the account manager received the email."</p>
<p>AVA measures success by the business outcome. A renewal reaches completion when the obligation has been successfully renewed, organizational records accurately reflect the outcome, supporting evidence has been archived, the Renewal Register has been updated, the next renewal cycle has been scheduled, and no further operational work remains.</p>
<p>A generated email isn't success. A sent email isn't success. A completed renewal is success.</p>

<h2>Related Renewal Operations resources</h2>
<p>MSP renewal management builds on the same lifecycle used across every AVA page. See the complete operational lifecycle in <a href="{$this->pageUrl('ava', 'renewal-management')}">Renewal Management Software</a>, or see how AVA detects renewal work across every supported asset in <a href="{$this->pageUrl('ava', 'detect/renewal-tracking-software')}">Renewal Tracking &amp; Reminder Software</a>.</p>

<h2>Recurring customer obligations deserve a dedicated owner</h2>
<p>Your customers trust you with recurring technology obligations. Those obligations shouldn't depend on somebody remembering the right date, checking the right spreadsheet, or finding the right email.</p>
<p>Give every renewal a transaction. Give every transaction an owner. Keep your people in control of the decisions.</p>
HTML;
    }

    private function page6Faqs(): array
    {
        return [
            ['What is MSP renewal management?', "MSP renewal management is the operational process of tracking and coordinating recurring obligations across managed customers so important renewals can reach completion before expiration."],
            ['What types of renewals can AVA manage for an MSP?', "AVA Version 1 supports domains, SSL certificates, hosting services, SaaS subscriptions, insurance policies, licenses, and maintenance agreements."],
            ['Can AVA manage renewals across multiple customers?', "AVA is specifically designed for organizations managing recurring customer renewals, and Identify Customer is a defined stage of her renewal lifecycle."],
            ['Can AVA track customer domains?', "Domains are explicitly supported Version 1 renewable assets. AVA's responsibility is the Renewal Operations surrounding the domain rather than acting as a domain registrar."],
            ['Can AVA manage SSL certificate renewals?', "Yes. SSL certificates are explicitly supported Version 1 renewable assets."],
            ['Can AVA manage customer software renewals?', "SaaS subscriptions and licenses are supported assets. AVA can coordinate the operational renewal lifecycle while humans retain purchasing, financial, vendor, and contractual decision authority."],
            ['Can AVA manage hosting renewals?', "Yes. Hosting services are explicitly included among AVA's supported Version 1 renewal assets."],
            ['Does AVA automatically charge customers or pay vendors?', "No. Automatic payment execution, financial authorization, payment authorization, and autonomous purchasing are outside AVA's Version 1 scope."],
            ['Does AVA send renewal communications directly to MSP customers?', "AVA can prepare personalized communications, but human review is required and no customer-facing communication proceeds without approval in Version 1."],
            ['Does AVA replace our PSA or CRM?', "AVA's approved responsibility does not include CRM ownership, and her business contract does not establish PSA replacement as a capability. AVA should be positioned as a Renewal Operations Worker rather than as a general replacement for an MSP's operational software stack."],
            ['Does AVA negotiate vendor or customer contracts?', "No. Contract negotiation is explicitly outside AVA's responsibility."],
            ['How does AVA know which customer a renewal belongs to?', "Customer identification is one of AVA's defined lifecycle stages and supported capabilities. The precise technical matching methods should be described according to the implemented product rather than assumed beyond AVA's approved specification."],
            ['What happens after a customer renewal is completed?', "AVA's definition of done requires records to reflect the outcome, supporting evidence to be archived, the Renewal Register to be updated, the next renewal cycle to be scheduled, and no further operational work to remain."],
            ['Why would an MSP use AVA instead of renewal reminders?', "Renewal reminders create awareness. AVA is designed to provide continuous operational ownership from detection through customer and asset identification, human review, fulfillment, records, future scheduling, archive, and completion."],
        ];
    }
}
