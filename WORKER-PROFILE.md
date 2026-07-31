# Worker Public Profile Page Specification

Reference implementation: `resources/views/workers/public/ava.blade.php` +
`app/Http/Controllers/WorkerPublicController.php`. AVA's page is the proof
of concept for every section and pattern below; when a worker (DOX, MOX,
NUX, or any future one) gets a real public profile page, it should follow
this spec rather than reinvent structure per worker.

This is a living document. AVA's page is currently the only page built to
this spec, sample size of one. Building the second page (DOX) is the real
test of what's actually reusable versus AVA-specific; update this file
when that happens.

---

## Philosophy

Three rules override everything else in this document:

1. **Never fabricate.** No invented stats, testimonials, integrations,
   certifications, or capabilities. If a number isn't real (queried from
   the DB), it doesn't go on the page. If a feature isn't built, the page
   says "coming soon" instead of implying it exists. A fabricated SOC2/GDPR
   badge was removed from AVA's page specifically because it was a
   legal-risk claim with zero backing documentation, don't repeat that.
2. **Single source of truth, everywhere possible.** Any data that drives
   both visible copy and structured data (JSON-LD) must come from the
   same PHP array. The pipeline steps, the FAQ, the edge statements all
   work this way, so copy and schema cannot silently drift apart.
3. **Workers are beings, not features.** Every worker gets a first-person
   voice (a "Meet [Worker]" section), a personality-driven hero video, and
   (eventually) her own explainer videos. The page sells a coworker, not
   a checklist of capabilities.

---

## Required Data Shape

Every worker's entry in `WorkerPublicController::$workers` needs:

```php
'slug' => [
    'name'       => 'AVA',                    // display name
    'slug'       => 'ava',
    'role'       => 'AI Renewal Agent',        // public marketing role (see Naming below)
    'category'   => 'Renewal Automation',
    'meta_desc'  => '...',                     // under 160 chars, ends on a real trust phrase

    'hero_video' => [
        'url'          => 'videos/AVA.MP4',
        'thumbnail'    => 'images/ava-skyline.png',
        'duration_iso' => 'PT22S',             // real, from ffprobe, not guessed
        'upload_date'  => '2026-07-11',
        'transcript'   => '...',                // real, transcribed from the actual audio
    ],

    'pipeline' => [                             // single source of truth for the animated
        ['who' => 'AVA'|'YOU', 'title' => '...', 'desc' => '...'],
        // ...grouped from the real backend pipeline into ~10 visitor-followable steps
    ],

    'faq' => [
        ['q' => '...', 'a' => '...'],           // 'a' may contain safe inline HTML (e.g. <a> to /pricing)
    ],
],
```

Additionally, `WorkerPublicController::show()` queries and passes down
(worker-agnostic, driven by `worker_slug` columns, not hardcoded per
worker):

- `$resources`: published `blog_posts` where `worker_slug = $slug`
- `$reviews`: approved `worker_reviews` where `worker_slug = $slug`
- `$deploymentCount`, `$totalTx`: real usage stats from `worker_deployments`/`transactions`

---

## Naming Convention (deliberate, not inconsistent)

UNIT intentionally uses **both** "AI Agent" and "AI Worker" language
across a worker's own page, for both search terms:

| Surface | Term used |
|---|---|
| `<title>` / Service schema `name` | "AI Agent" (e.g. "AVA: AI Agent for Renewals") |
| Hero eyebrow | "AI Renewal Worker" |
| Meet-section signature, internal dashboard `employee()['title']` | "AI Renewal Agent" / "AI Renewal Coordinator" (internal only, never public) |
| llms.txt | Must match the *public* language ("AI Renewal Agent"), never the internal dashboard title |

Do not blanket-replace one term with the other. Do keep llms.txt
consistent with the public page, that was a real bug (llms.txt said
"Coordinator" while every public surface said "Agent"/"Worker").

---

## Page Sections, In Order

Each row: what it's for, what data drives it, and the non-negotiable
constraint that came out of building AVA's page.

| # | Section | Data source | Constraint |
|---|---|---|---|
| 1 | Nav | shared `<x-public-nav>` | Reuse the component, don't rebuild per worker |
| 2 | Hero | `$worker['hero_video']`, `$avaHasDesk`-equivalent, real DB stats | Text (`.hero-copy`) must hide during video playback and the whole `.hero-text` column must be bottom-anchored (`justify-content:flex-end`), never vertically centered, or the CTA buttons float over the footage when copy collapses. Captions (`<track kind="captions">`) plus a **visible** `<details>` transcript, a track file alone isn't crawlable. |
| 3 | The Problem | hardcoded per-worker (the pain this worker solves) | Ends with a real "related reading" link to a real blog post if one exists |
| 4 | Meet [Worker] | first-person copy, worker's own photo | This is the character-voice section; nothing else on the page should be first-person |
| 5 | Memory | tenant-owned-data breakdown | This is a platform-wide differentiator (every worker's memory is tenant-exclusive), not AVA-specific copy, keep the framing generic to "what you teach her/him" |
| 6 | Pipeline demo | `$worker['pipeline']`, tagged `who` per step | Same array drives the HowTo schema. Never hardcode timestamps, use the honest "[Worker] / You decide" tag instead |
| 7 | Edge Statements | `$edgeStatements` array (img, eye, h3, p, proof, video) | Every statement needs a `proof` (real screenshot/artifact) behind "See Proof" and a `video` slot (nullable) behind "Watch [Worker] Explain". VideoObject schema for a statement is emitted only `@if(!empty($edge['video']))`, adding a real video later requires editing that one array field, nothing else |
| 8 | What [Worker] Actually Touches + Live Performance | real integrations list + real DB stats | List only integrations that actually exist. Icon-left/label-right rows, no outer card (see Visual Rules) |
| 9 | Who Hires [Worker] + Reviews | confirmed real ICP + `$reviews` | Personas must match the confirmed GTM persona (check with the user/employer field), not invented industries. Reviews: real cards if `$reviews->count() > 0`, else the honest skeleton + solicitation overlay, never fabricated quotes |
| 10 | Integrations | real connections only | If there's only one real integration, say so plainly ("One real connection...") rather than padding with a logo wall |
| 11 | Security | real, verifiable claims only | Never claim a certification (SOC2, GDPR, etc.) without an actual audit/cert on file |
| 12 | Resources | `$resources` (DB query, `worker_slug` scoped) | Query-driven, not hardcoded links, so a renamed/unpublished post can't leave a dead link on the page |
| 13 | FAQ | `$worker['faq']` | Same array drives FAQPage schema. Answers may link out (e.g. pricing question → `/pricing`) using `{!! !!}` since it's first-party controlled HTML |
| 14 | Final CTA | real trial terms | No fabricated urgency/stats in the closing push |
| 15 | Footer | shared `<x-public-footer>` | Carries Organization + WebSite schema for the whole page for free |

---

## Required JSON-LD (7 blocks, in `<head>`)

1. **Service**: name, serviceType, description, image (OG-cropped, see
   below), url, provider. Conditionally gets `aggregateRating` + `review`
   appended, **only** when `$reviews->count() > 0`. Never emit
   AggregateRating with zero backing reviews (Google's own guidance).
2. **FAQPage**: built via `array_map` over `$worker['faq']`, cannot
   drift from the visible accordion.
3. **BreadcrumbList**: Home -> AI Workers -> [Worker].
4. **HowTo**: built via `array_map` over `$worker['pipeline']`, cannot
   drift from the visible demo.
5. **VideoObject** (hero): name, description (= transcript), thumbnailUrl,
   contentUrl, uploadDate, duration. All sourced from `$worker['hero_video']`.
6. **VideoObject** (edge statements, conditional, zero or more): one per
   `$edgeStatements` entry where `video` is set. Nothing renders until a
   real video exists.
7. **Organization** + **WebSite**: free from `<x-public-footer>`.

Verify with:
```bash
php artisan tinker --execute='
$resp = app()->handle(\Illuminate\Http\Request::create("/ai-workers/{slug}", "GET"));
preg_match_all("/<script type=\"application\/ld\+json\">(.*?)<\/script>/s", $resp->getContent(), $m);
foreach ($m[1] as $j) { $d = json_decode($j, true); echo ($d["@type"] ?? "PARSE ERROR") . PHP_EOL; }
'
```

---

## SEO Checklist

- [ ] `<title>` under 60 chars, format `"{Name}: AI Agent for {Category} | UNIT"`
- [ ] Meta description under 160 chars, ends on a real trust phrase
- [ ] Canonical + OG + Twitter tags via `@include('partials.seo-meta', [...])`
- [ ] og:image is a **dedicated** 1200x630 crop (`{slug}-og.png`), not the
  raw hero poster reused as-is, most hero photos are portrait/near-square
  and crop badly at 1.91:1 if you don't make a real crop
- [ ] Every `<img>` has a descriptive, per-image alt (not a generic
  `alt="{Name}"` repeated everywhere), edge-card images can derive theirs
  from the card's own `eye` field so they can't drift
- [ ] Exactly one `<h1>`, clean H2/H3 nesting, no skipped levels
- [ ] Page is in `sitemap.xml` (`SitemapController::PUBLIC_WORKER_SLUGS`)
- [ ] Worker is in `llms.txt` (`SitemapController::WORKER_DETAILS`), role
  and description match the public page, not the internal dashboard title
- [ ] At least 3 internal links: a Resources section (DB-driven) plus
  2-3 contextual "related reading" links at natural topical matches
  within the page body

---

## Visual Rules

- **Reduce card usage.** Default to icon-left/label-right rows for
  routine icon+text content (integrations, personas, feature lists).
  Reach for an actual bordered/background card only when the content is
  a genuinely distinct module (a review, an article preview, a pricing
  tier), not for every repeated list item.
- **Brand colors:** black/white base, gold `#F5C518` used only as an
  underline accent (`.hl::after`), never as a fill/stroke/text color.
- **No em-dashes anywhere**, in copy, comments, or migration content.
  Replace with the punctuation the sentence actually calls for (period,
  comma, colon).
- **Modal/lightbox pattern** (for "See Proof" / "Watch [Worker] Explain"):
  one shared `#edgeModal` per page, `data-edge-proof="{i}"` /
  `data-edge-watch="{i}"` on trigger buttons, JS reads a `@json()`-dumped
  array built from `$edgeStatements`, no per-card duplicate modals.

---

## Reviews (Admin-Managed)

- Table: `worker_reviews` (`worker_slug`, `author_name`, `author_company`,
  `rating`, `quote`, `status`, `approved_at`).
- Admin: `/admin/reviews` (`AdminReviewController`), add/approve/reject/delete.
  Reviews currently arrive by email (the page's "Share Your Experience"
  mailto link) and get manually transcribed in; there is no public
  submission form.
- Public page: real cards replace the skeleton the moment
  `$reviews->count() > 0`; the skeleton + solicitation overlay is the
  correct state at zero, not a bug to "fix" by inventing quotes.

---

## Building a New Worker's Page: Checklist

1. Confirm the worker has a real, working backend pipeline first, the
   page's pipeline demo and HowTo schema must reflect actual stages, not
   planned ones.
2. Add the worker's entry to `WorkerPublicController::$workers` with all
   required fields (see Required Data Shape above).
3. Get/produce: a personality hero video (with real transcript), a
   first-person "Meet [Worker]" photo, and at least one photo per edge
   statement.
4. Write 2-4 edge statements: real differentiators, each with a genuine
   screenshot/artifact for "See Proof" (generate it the way AVA's were:
   trigger the real backend feature against a test transaction/tenant,
   screenshot the real result, redact any account-identifying info).
5. Confirm the worker's real ICP/persona with the user, don't guess
   industries.
6. Add the worker to `SitemapController::PUBLIC_WORKER_SLUGS` and
   `WORKER_DETAILS` (sitemap + llms.txt).
7. Write at least 2-3 real blog posts tagged `worker_slug = {slug}` before
   launch, so the Resources section isn't empty on day one.
8. Run the SEO Checklist above in full before calling the page done.
9. Add `<x-self-learn />` before `</html>` (AVA's page includes it right
   before the closing tag; every new page gets this).

---

## Known Gaps (as of AVA's page, 2026-07-31)

These are honest, current limitations, not things to silently "fix" by
fabricating content:

- Every "Watch [Worker] Explain" video slot is empty across the whole
  platform. UNIT Studio's video pipeline doesn't exist yet.
- Reviews are empty for every worker until real ones come in via email
  and get manually approved.
- This entire spec is validated against exactly one worker page (AVA).
  Building DOX's page next is the real test of what generalizes.
