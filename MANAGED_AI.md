# ChatLM Managed AI — Architecture & Competitor Positioning

## Two product tracks

| Track | Who brings the AI? | ChatLM charges | Best for |
|-------|-------------------|----------------|----------|
| **BYOK** | Customer (OpenAI, Gemini, DeepSeek, OpenRouter) | Platform fee only — **$19–$89/mo** | Developers, agencies with existing API accounts |
| **Managed AI** | ChatLM (system key via OpenRouter) | Platform + included message quota — **$29–$129/mo** | Founders who want plug-and-play |

Annual billing: **pay 10 months, get 12** (~17% off) — toggle **Bill annually** on `/pricing.php`.

---

## Competitor snapshot (2026)

| Platform | Entry paid | Real AI cost | Whitelabel | BYOK | Hidden fees |
|----------|-----------|--------------|------------|------|-------------|
| **Chatbase** | ~$40/mo | Credits / overages | Enterprise add-on | No | $40+ per 1k extra credits |
| **Tidio** | $29/mo + Lyro ~$39+ | AI is separate add-on | Expensive tiers | No | True cost often **$68–150+/mo** |
| **Intercom Fin** | $29/seat + **$0.99/resolution** | Per resolution | Suite pricing | No | Scales badly with volume |
| **ChatBot.com** | ~$50/mo | Limited chats | Enterprise | No | Per-chat caps |
| **Crisp** | €25–45/mo | AI on **€295/mo** tier | Workspace pricing | No | AI gated to high tier |
| **ChatLM BYOK** | **$19/mo** | You pay provider directly | From Pro | **Yes** | **None** |
| **ChatLM Managed** | **$29/mo** | **3k msgs included** | From Growth | N/A (fixed quota) | Predictable |

### What competitors often lack (ChatLM advantages)

1. **True BYOK** — No token markup; use your own OpenRouter/OpenAI bill.
2. **Managed + BYOK in one product** — Same widget, clear upgrade path.
3. **Transparent USD pricing** — No per-resolution surprise bills.
4. **6-language** marketing, docs, and pricing.
5. **Telegram alerts** on all tiers.
6. **Single embed script** — No heavy suite lock-in.
7. **OpenRouter backend** — One integration, many models for Managed tier.

---

## Step-by-step: Managed AI integration

### 1. Database (run once)

```bash
mysql ... < schema_migration_v6_managed_ai.sql
```

Adds to `clients`: `plan_type`, `api_key_source`, `message_quota_*`, `remove_branding`, `whitelist_domains`, etc.

### 2. Server secrets (`config.local.php`)

```php
'SYSTEM_OPENROUTER_API_KEY' => 'sk-or-v1-...',
'SYSTEM_AI_PROVIDER' => 'openrouter',
'SYSTEM_AI_DEFAULT_MODEL' => 'openai/gpt-4o-mini',
// Optional fallbacks (comma-separated OpenRouter slugs):
'SYSTEM_AI_FALLBACK_MODELS' => 'deepseek/deepseek-chat,google/gemini-2.0-flash',
```

Never commit real keys. File: `config/ai_config.php` reads these via `config_env()`.

### 3. Stripe products

Create **12 prices** (or start with monthly only):

- `STRIPE_PRICE_BYOK_STARTER_MONTHLY`, `_YEARLY`
- `STRIPE_PRICE_BYOK_PRO_*`, `STRIPE_PRICE_BYOK_AGENCY_*`
- `STRIPE_PRICE_MANAGED_STARTER_*`, `MANAGED_PRO_*`, `MANAGED_AGENCY_*`

Legacy env vars still map: `STRIPE_PRICE_STARTER_MONTHLY` → BYOK Starter.

### 4. Request flow (`api/chat.php`)

1. Validate widget `X-Api-Key` → load client + settings.
2. **Domain check** — `Referer` / `Origin` must match `allowed_origins`.
3. **Quota** — if `plan_type` is `managed_*` and `message_quota_used >= message_quota_limit` → 402 JSON error.
4. **Credentials**
   - `api_key_source = system` → use `config/ai_config.php` master key + default model.
   - `api_key_source = user` → decrypt `widget_settings.ai_api_key` (BYOK).
5. Call `ai_chat_complete()` (existing multi-provider layer).
6. On success for Managed → `message_quota_used + 1`.
7. Return `{ reply, session_id }`.

### 5. Widget settings (`api/get-settings.php` / `api/widget-settings.php`)

- `show_watermark` from `remove_branding` + plan.
- Managed plans expose `message_quota_limit`, `message_quota_used`, `billing_track`.

### 6. Recommended provider: **OpenRouter**

| Why | Detail |
|-----|--------|
| One API | OpenAI-compatible `/chat/completions` |
| Model choice | `openai/gpt-4o-mini`, `deepseek/deepseek-chat`, `google/gemini-2.0-flash` |
| Cost | ~$0.15–$0.40 / 1M input tokens for mini models → **~$0.002–0.005 per chat reply** |
| Failover | Swap model slug without code deploy |

**Alternatives:** Direct OpenAI (`gpt-4o-mini`), DeepSeek API (cheap), Google Gemini API.

### 7. Model routing strategy

| Tier | Default model | Notes |
|------|---------------|-------|
| Managed Starter | `openai/gpt-4o-mini` | Best cost/quality |
| Managed Growth+ | Customer can set `ai_model` in dashboard | Still billed to your master key |
| BYOK | Customer model field | Their cost |

### 8. Margin example (Managed Starter $29/mo, 3k msgs)

- COGS: 3,000 × $0.003 ≈ **$9**
- Gross margin ≈ **$20 (69%)**

---

## Files touched

- `schema_migration_v6_managed_ai.sql`
- `config/ai_config.php`
- `includes/managed_ai.php`
- `includes/plans.php`, `includes/billing.php`
- `api/chat.php`, `api/get-settings.php`, `api/widget-settings.php`
- `pricing.php`, `css/pricing.css`

---

## Testing checklist

1. Run migration on staging.
2. Set `SYSTEM_OPENROUTER_API_KEY` in `config.local.php`.
3. Subscribe test user to `managed_starter_monthly` (Stripe test mode).
4. Embed widget — chat without user API key.
5. Exhaust quota in DB (`message_quota_used = message_quota_limit`) → expect 402.
6. BYOK plan — requires dashboard API key; quota should not apply.
