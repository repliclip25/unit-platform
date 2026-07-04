# INFRA.md — UNIT Platform Infrastructure Bible

Every third-party service, API, and infrastructure dependency the UNIT platform relies on.
Update this file whenever a service is added, removed, or its pricing/status changes.

---

## How to read this file

| Column | Meaning |
|---|---|
| **Status** | `active` = live and in use · `test-mode` = wired but not live · `optional` = available but off by default · `planned` = not yet integrated |
| **Billing model** | How the service charges |
| **Est. cost @ 500 tx/mo** | Rough monthly cost at 500 AVA transactions per month |
| **Est. cost @ 5,000 tx/mo** | Rough monthly cost at 5,000 AVA transactions per month |
| **Config location** | Where credentials live in the codebase |

**"Transaction"** on UNIT = one email fully processed by AVA end-to-end (5 Claude API calls: read, classify, memory, draft, push).

---

## 1. AI / LLM

| Service | Provider | Status | Billing model | Est. @ 500 tx/mo | Est. @ 5,000 tx/mo | Config |
|---|---|---|---|---|---|---|
| Claude Haiku 4.5 | Anthropic | optional | $0.80/M input · $4.00/M output | ~$3/mo | ~$29/mo | `CLAUDE_API_KEY` · `services.claude` |
| Claude Sonnet 4.6 (**default**) | Anthropic | active | $3.00/M input · $15.00/M output | ~$26/mo | ~$260/mo | `CLAUDE_API_KEY` · `services.claude` |
| Claude Opus 4.7 | Anthropic | optional | $15.00/M input · $75.00/M output | ~$128/mo | ~$1,280/mo | `CLAUDE_API_KEY` · `services.claude` |
| OpenAI (GPT-4o etc.) | OpenAI | optional | Pay-per-token (tenant key) | $0 | $0 | `OPENAI_API_KEY` · `services.openai` |
| Google AI (Gemini) | Google | optional | Pay-per-token (tenant key) | $0 | $0 | `GOOGLE_AI_API_KEY` · `services.google_ai` |
| Kimi (Moonshot) | Moonshot AI | optional | Pay-per-token (tenant key) | $0 | $0 | `KIMI_API_KEY` · `services.kimi` |

**Token assumptions per AVA transaction (Sonnet default):**
- Classify email: ~800 input / 300 output
- Memory lookup: ~2,000 input / 500 output
- Draft reply: ~3,000 input / 1,500 output
- Total: **~5,800 input + 2,300 output tokens per transaction**
- Cost per transaction at Sonnet: **~$0.052**

---

## 2. Google / Gmail

| Service | Purpose | Status | Billing model | Est. monthly cost | Config |
|---|---|---|---|---|---|
| Gmail API | Read inboxes, send drafted replies, fast-track ingest | active | Free (quota: 1B units/day) | $0 | `GMAIL_CLIENT_ID` · `GMAIL_CLIENT_SECRET` · `services.gmail` |
| Google OAuth 2.0 (Gmail scope) | Tenant inbox authorization | active | Free | $0 | `GMAIL_CLIENT_ID` · `GMAIL_CLIENT_SECRET` · `GMAIL_REDIRECT_URI` |
| Google OAuth 2.0 (Sign-In) | Tenant account login / registration | active | Free | $0 | `GOOGLE_CLIENT_ID` · `GOOGLE_CLIENT_SECRET` · `services.google` |
| Google Cloud Pub/Sub | Gmail push notifications → AVA webhook | active | First 10 GB/mo free · $0.04/GB after | ~$0 (< 10 GB at low volume) | `GMAIL_PUBSUB_TOPIC` · `GMAIL_PUBSUB_SERVICE_ACCOUNT` |

**Pub/Sub threshold:** At ~2 KB per message, you'd need ~5M messages/month before leaving the free tier. Not a concern until very high volume.

**Watch renewal:** Gmail watch expires every 7 days. The `ava.gmail.watch.renew` cron (runs every 6 days) handles this automatically.

---

## 3. Billing & Payments

| Service | Purpose | Status | Billing model | Cost | Config |
|---|---|---|---|---|---|
| Stripe (test mode) | Subscription checkout, billing portal, webhooks | test-mode | 2.9% + $0.30 per successful charge | Revenue take-rate (not a flat cost) | `STRIPE_KEY` · `STRIPE_SECRET` · `STRIPE_WEBHOOK_SECRET` |

**To activate:** Replace `pk_test_...` / `sk_test_...` keys with live keys from Stripe dashboard. Update webhook endpoint in Stripe dashboard to point at `/stripe/webhook`.

**Margin math:** At $49/mo per deployment, Stripe takes ~$1.72 per subscriber. Factor into plan pricing.

---

## 4. Infrastructure & Hosting

| Service | Purpose | Status | Billing model | Est. monthly cost | Notes |
|---|---|---|---|---|---|
| Laravel Forge | Server provisioning, deploy pipeline, SSL, cron | active | $19/mo (Hobby) · $39/mo (Growth) | $19–39/mo | Growth plan needed for multiple servers |
| VPS — DigitalOcean / Hetzner / similar | App server: PHP, MySQL, Redis, Horizon | active | $24–48/mo depending on RAM/CPU | $24–48/mo | 2 GB RAM minimum; 4 GB recommended at scale |
| Redis | Horizon queue backend, session store, cache | active | Included on VPS (self-hosted) · $7/mo Redis Cloud | $0 (VPS) | `REDIS_HOST` · `QUEUE_CONNECTION=redis` |
| MySQL | Primary database (all platform data) | active | Included on VPS | $0 | `DB_CONNECTION=mysql` · `DB_HOST` |

**Fixed infra floor:** ~$43–87/mo regardless of transaction volume.

---

## 5. Email (Platform SMTP)

| Service | Purpose | Status | Billing model | Est. monthly cost | Config |
|---|---|---|---|---|---|
| Namecheap shared SMTP | Platform transactional emails (welcome, alerts, summaries) | active | Included with hosting plan | ~$0 incremental | `MAIL_HOST=premium105.web-hosting.com` · `MAIL_PORT=465` |

**Important:** Use `premium105.web-hosting.com` as SMTP host — NOT `mail.unit.report`. The SSL cert is issued to the server hostname, not the domain alias. Using `mail.unit.report` causes cert mismatch errors.

**Upgrade path:** If transactional email volume grows (> ~500/day), migrate to Postmark or Resend. Both are already wired in `config/services.php` — just set `MAIL_MAILER=postmark` or `resend` and provide the API key.

| Upgrade option | Billing | Sweet spot |
|---|---|---|
| Postmark | $15/mo for 10K emails | Deliverability-critical (receipts, billing) |
| Resend | $20/mo for 50K emails | High volume marketing + transactional |

---

## 6. Analytics & Tracking

| Service | Purpose | Status | Billing model | Est. monthly cost | Config |
|---|---|---|---|---|---|
| Google Tag Manager | Tag management container | planned | Free | $0 | `GTM_ID` (empty in .env) |
| Facebook Pixel | Paid ad attribution | planned | Free | $0 | `FACEBOOK_PIXEL_ID` (empty in .env) |

---

## 7. Domains

| Domain | Purpose | Registrar | Est. annual cost | Renewal |
|---|---|---|---|---|
| unit.app | Primary platform domain | — | ~$20/yr | — |
| unit.report | Email sending domain (hello@unit.report) | Namecheap | ~$15/yr | — |

**Monthly amortized:** ~$3/mo total.

---

## Cost summary by volume

| Volume | AI (Sonnet default) | Infra floor | Total est. |
|---|---|---|---|
| 100 tx/mo (early) | ~$5 | $46 | **~$51/mo** |
| 500 tx/mo | ~$26 | $46 | **~$72/mo** |
| 1,000 tx/mo | ~$52 | $46 | **~$98/mo** |
| 2,500 tx/mo | ~$130 | $46 | **~$176/mo** |
| 5,000 tx/mo | ~$260 | $46 | **~$306/mo** |
| 10,000 tx/mo | ~$520 | $70 | **~$590/mo** |

> Infra floor scales up at 10K tx/mo because you'd want a larger VPS and likely Forge Growth plan.
> Stripe take-rate not included — it comes out of revenue, not pocket.

---

## Connection checklist

Run through this when deploying to a new environment:

- [ ] `CLAUDE_API_KEY` — set and verified (Anthropic console)
- [ ] `GMAIL_CLIENT_ID` / `GMAIL_CLIENT_SECRET` — Google Cloud Console, Gmail API enabled
- [ ] `GMAIL_PUBSUB_TOPIC` / `GMAIL_PUBSUB_SERVICE_ACCOUNT` — Google Cloud Pub/Sub configured
- [ ] `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` — Google Sign-In OAuth client
- [ ] `STRIPE_KEY` / `STRIPE_SECRET` — **test** keys for staging, **live** keys for production
- [ ] `STRIPE_WEBHOOK_SECRET` — Stripe dashboard → Webhooks → endpoint secret
- [ ] `MAIL_HOST` / `MAIL_USERNAME` / `MAIL_PASSWORD` — Namecheap SMTP credentials
- [ ] `QUEUE_CONNECTION=redis` + `REDIS_HOST` — confirmed Horizon is running
- [ ] `APP_URL` — set to the actual domain (affects Gmail OAuth redirect URI)
- [ ] `GTM_ID` — Google Tag Manager (when ready)
- [ ] `FACEBOOK_PIXEL_ID` — Facebook Pixel (when ready)

---

## Adding a new service

When a new third-party service is integrated:

1. Add its credentials to `.env` and document them in `config/services.php`
2. Add a row to the relevant section above
3. Fill in: purpose, status, billing model, estimated cost at 500 and 5,000 tx/mo
4. Add it to the connection checklist
5. Commit this file with the same PR that wires the service

---

*Last updated: 2026-07-04*
