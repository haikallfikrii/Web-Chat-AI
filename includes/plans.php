<?php
declare(strict_types=1);

/**
 * Katalog paket ChatLM — jalur BYOK vs Managed AI.
 * Stripe Price ID via config_env (STRIPE_PRICE_*).
 */
function app_site_url(): string
{
    return app_base_url();
}

/**
 * @return array<string, array<string, mixed>>
 */
function billing_plan_definitions(): array
{
    $annualFactor = 10; // 10 bulan harga = ~17% off vs 12 bulan

    $defs = [
        'free' => [
            'code'                  => 'free',
            'plan_type'             => 'free',
            'track'                 => 'byok',
            'api_key_source'        => 'user',
            'name'                  => 'Free',
            'tagline'               => 'Try BYOK with watermark',
            'price_usd'             => 0,
            'price_display'         => '$0',
            'interval'              => null,
            'stripe_price_id'       => null,
            'show_watermark'        => true,
            'remove_branding'       => false,
            'subscription_status'   => 'trial',
            'message_quota_limit'   => 0,
            'max_websites'          => 1,
            'highlight'             => false,
            'cta'                   => 'Start Free',
            'features'              => [
                '1 website · BYOK (your API key)',
                'Watermark "Powered by ChatLM"',
                '14-day full trial',
                'Telegram notifications',
            ],
        ],
        // ── BYOK ─────────────────────────────────────────────
        'byok_starter_monthly' => [
            'code' => 'byok_starter_monthly', 'plan_type' => 'byok_starter', 'track' => 'byok',
            'api_key_source' => 'user', 'name' => 'Starter',
            'price_usd' => 19, 'price_display' => '$19', 'interval' => 'month',
            'stripe_price_id' => config_env('STRIPE_PRICE_BYOK_STARTER_MONTHLY', config_env('STRIPE_PRICE_STARTER_MONTHLY', '')),
            'show_watermark' => false, 'remove_branding' => true, 'subscription_status' => 'active',
            'message_quota_limit' => 0, 'max_websites' => 1, 'highlight' => false,
            'cta' => 'Start Starter',
            'features' => ['1 website', 'No watermark', 'Your OpenAI / Gemini / DeepSeek keys', 'Zero token markup', 'Telegram alerts'],
        ],
        'byok_starter_yearly' => [
            'code' => 'byok_starter_yearly', 'plan_type' => 'byok_starter', 'track' => 'byok',
            'api_key_source' => 'user', 'name' => 'Starter',
            'price_usd' => 19 * $annualFactor,
            'price_display' => '$16',
            'price_monthly_equiv' => 16,
            'price_total_annual' => 19 * $annualFactor,
            'interval' => 'year', 'billing_note' => 'billed annually',
            'stripe_price_id' => config_env('STRIPE_PRICE_BYOK_STARTER_YEARLY', config_env('STRIPE_PRICE_STARTER_YEARLY', '')),
            'show_watermark' => false, 'remove_branding' => true, 'subscription_status' => 'active',
            'message_quota_limit' => 0, 'max_websites' => 1, 'highlight' => true,
            'cta' => 'Start Starter',
            'features' => ['1 website', 'No watermark', 'Your OpenAI / Gemini / DeepSeek keys', 'Zero token markup', '2 months free vs monthly'],
        ],
        'byok_pro_monthly' => [
            'code' => 'byok_pro_monthly', 'plan_type' => 'byok_pro', 'track' => 'byok',
            'api_key_source' => 'user', 'name' => 'Pro',
            'price_usd' => 39, 'price_display' => '$39', 'interval' => 'month',
            'stripe_price_id' => config_env('STRIPE_PRICE_BYOK_PRO_MONTHLY', config_env('STRIPE_PRICE_PRO_MONTHLY', '')),
            'show_watermark' => false, 'remove_branding' => true, 'subscription_status' => 'active',
            'message_quota_limit' => 0, 'max_websites' => 5, 'highlight' => true,
            'cta' => 'Start Pro',
            'features' => ['5 websites', 'Whitelabel widget', 'Custom CSS & branding', 'Chat history & AI memory', 'Email support'],
        ],
        'byok_pro_yearly' => [
            'code' => 'byok_pro_yearly', 'plan_type' => 'byok_pro', 'track' => 'byok',
            'api_key_source' => 'user', 'name' => 'Pro',
            'price_usd' => 39 * $annualFactor,
            'price_display' => '$33',
            'price_monthly_equiv' => 33,
            'price_total_annual' => 39 * $annualFactor,
            'interval' => 'year', 'billing_note' => 'billed annually',
            'stripe_price_id' => config_env('STRIPE_PRICE_BYOK_PRO_YEARLY', config_env('STRIPE_PRICE_PRO_YEARLY', '')),
            'show_watermark' => false, 'remove_branding' => true, 'subscription_status' => 'active',
            'message_quota_limit' => 0, 'max_websites' => 5, 'highlight' => true,
            'cta' => 'Start Pro',
            'features' => ['5 websites', 'Whitelabel widget', 'Custom CSS & branding', 'Chat history & AI memory', '2 months free vs monthly'],
        ],
        'byok_agency_monthly' => [
            'code' => 'byok_agency_monthly', 'plan_type' => 'byok_agency', 'track' => 'byok',
            'api_key_source' => 'user', 'name' => 'Agency',
            'price_usd' => 89, 'price_display' => '$89', 'interval' => 'month',
            'stripe_price_id' => config_env('STRIPE_PRICE_BYOK_AGENCY_MONTHLY', ''),
            'show_watermark' => false, 'remove_branding' => true, 'subscription_status' => 'active',
            'message_quota_limit' => 0, 'max_websites' => 25, 'highlight' => false,
            'cta' => 'Start Agency',
            'features' => ['25 websites', 'Multi-client workflows', 'Priority support', 'Advanced allowed origins', 'Zero token markup'],
        ],
        'byok_agency_yearly' => [
            'code' => 'byok_agency_yearly', 'plan_type' => 'byok_agency', 'track' => 'byok',
            'api_key_source' => 'user', 'name' => 'Agency',
            'price_usd' => 89 * $annualFactor,
            'price_display' => '$74',
            'price_monthly_equiv' => 74,
            'price_total_annual' => 89 * $annualFactor,
            'interval' => 'year', 'billing_note' => 'billed annually',
            'stripe_price_id' => config_env('STRIPE_PRICE_BYOK_AGENCY_YEARLY', ''),
            'show_watermark' => false, 'remove_branding' => true, 'subscription_status' => 'active',
            'message_quota_limit' => 0, 'max_websites' => 25, 'highlight' => false,
            'cta' => 'Start Agency',
            'features' => ['25 websites', 'Multi-client workflows', 'Priority support', 'Advanced allowed origins', '2 months free vs monthly'],
        ],
        // ── Managed AI ───────────────────────────────────────
        'managed_starter_monthly' => [
            'code' => 'managed_starter_monthly', 'plan_type' => 'managed_starter', 'track' => 'managed',
            'api_key_source' => 'system', 'name' => 'Starter',
            'price_usd' => 29, 'price_display' => '$29', 'interval' => 'month',
            'stripe_price_id' => config_env('STRIPE_PRICE_MANAGED_STARTER_MONTHLY', ''),
            'show_watermark' => true, 'remove_branding' => false, 'subscription_status' => 'active',
            'message_quota_limit' => 3000, 'max_websites' => 1, 'highlight' => false,
            'cta' => 'Start Starter',
            'features' => ['3,000 AI messages / month', '1 website · plug & play', 'GPT-4o-mini class models', 'No API key required', 'Telegram alerts'],
        ],
        'managed_starter_yearly' => [
            'code' => 'managed_starter_yearly', 'plan_type' => 'managed_starter', 'track' => 'managed',
            'api_key_source' => 'system', 'name' => 'Starter',
            'price_usd' => 29 * $annualFactor,
            'price_display' => '$24',
            'price_monthly_equiv' => 24,
            'price_total_annual' => 29 * $annualFactor,
            'interval' => 'year', 'billing_note' => 'billed annually',
            'stripe_price_id' => config_env('STRIPE_PRICE_MANAGED_STARTER_YEARLY', ''),
            'show_watermark' => true, 'remove_branding' => false, 'subscription_status' => 'active',
            'message_quota_limit' => 3000, 'max_websites' => 1, 'highlight' => true,
            'cta' => 'Start Starter',
            'features' => ['3,000 AI messages / month', '1 website · plug & play', 'GPT-4o-mini class models', 'No API key required', '2 months free vs monthly'],
        ],
        'managed_pro_monthly' => [
            'code' => 'managed_pro_monthly', 'plan_type' => 'managed_pro', 'track' => 'managed',
            'api_key_source' => 'system', 'name' => 'Growth',
            'price_usd' => 59, 'price_display' => '$59', 'interval' => 'month',
            'stripe_price_id' => config_env('STRIPE_PRICE_MANAGED_PRO_MONTHLY', ''),
            'show_watermark' => false, 'remove_branding' => true, 'subscription_status' => 'active',
            'message_quota_limit' => 4000, 'max_websites' => 3, 'highlight' => true,
            'cta' => 'Start Growth',
            'features' => ['4,000 AI messages / month', '3 websites', 'Whitelabel (no watermark)', 'Model routing via OpenRouter', 'Lead-friendly chat memory'],
        ],
        'managed_pro_yearly' => [
            'code' => 'managed_pro_yearly', 'plan_type' => 'managed_pro', 'track' => 'managed',
            'api_key_source' => 'system', 'name' => 'Growth',
            'price_usd' => 59 * $annualFactor,
            'price_display' => '$49',
            'price_monthly_equiv' => 49,
            'price_total_annual' => 59 * $annualFactor,
            'interval' => 'year', 'billing_note' => 'billed annually',
            'stripe_price_id' => config_env('STRIPE_PRICE_MANAGED_PRO_YEARLY', ''),
            'show_watermark' => false, 'remove_branding' => true, 'subscription_status' => 'active',
            'message_quota_limit' => 4000, 'max_websites' => 3, 'highlight' => true,
            'cta' => 'Start Growth',
            'features' => ['4,000 AI messages / month', '3 websites', 'Whitelabel (no watermark)', 'Model routing via OpenRouter', '2 months free vs monthly'],
        ],
        'managed_agency_monthly' => [
            'code' => 'managed_agency_monthly', 'plan_type' => 'managed_agency', 'track' => 'managed',
            'api_key_source' => 'system', 'name' => 'Business',
            'price_usd' => 129, 'price_display' => '$129', 'interval' => 'month',
            'stripe_price_id' => config_env('STRIPE_PRICE_MANAGED_AGENCY_MONTHLY', ''),
            'show_watermark' => false, 'remove_branding' => true, 'subscription_status' => 'active',
            'message_quota_limit' => 12000, 'max_websites' => 10, 'highlight' => false,
            'cta' => 'Start Business',
            'features' => ['12,000 AI messages / month', '10 websites', 'Whitelabel', 'Priority support', 'Best for agencies & multi-brand'],
        ],
        'managed_agency_yearly' => [
            'code' => 'managed_agency_yearly', 'plan_type' => 'managed_agency', 'track' => 'managed',
            'api_key_source' => 'system', 'name' => 'Business',
            'price_usd' => 129 * $annualFactor,
            'price_display' => '$108',
            'price_monthly_equiv' => 108,
            'price_total_annual' => 129 * $annualFactor,
            'interval' => 'year', 'billing_note' => 'billed annually',
            'stripe_price_id' => config_env('STRIPE_PRICE_MANAGED_AGENCY_YEARLY', ''),
            'show_watermark' => false, 'remove_branding' => true, 'subscription_status' => 'active',
            'message_quota_limit' => 12000, 'max_websites' => 10, 'highlight' => false,
            'cta' => 'Start Business',
            'features' => ['12,000 AI messages / month', '10 websites', 'Whitelabel', 'Priority support', '2 months free vs monthly'],
        ],
    ];

    // Legacy Stripe plan codes (backend only — NOT shown in pricing UI)
    $legacy = [
        'starter_monthly' => 'byok_starter_monthly',
        'starter_yearly'  => 'byok_starter_yearly',
        'pro_monthly'     => 'byok_pro_monthly',
        'pro_yearly'      => 'byok_pro_yearly',
    ];
    foreach ($legacy as $old => $new) {
        if (isset($defs[$new])) {
            $copy = $defs[$new];
            $copy['code'] = $old;
            $copy['legacy_alias'] = $new;
            $copy['hidden'] = true;
            $defs[$old] = $copy;
        }
    }

    return $defs;
}

function billing_plans(): array
{
    return billing_plan_definitions();
}

/** @return list<string> */
function billing_plan_codes_for_track(string $track, string $interval = 'month'): array
{
    $out = [];
    foreach (billing_plan_definitions() as $code => $plan) {
        if (($plan['track'] ?? '') !== $track || $code === 'free') {
            continue;
        }
        $iv = $plan['interval'] ?? null;
        if ($interval === 'month' && $iv === 'month') {
            $out[] = $code;
        } elseif ($interval === 'year' && $iv === 'year') {
            $out[] = $code;
        }
    }

    return $out;
}

function billing_plan(string $code): ?array
{
    $plans = billing_plan_definitions();

    return $plans[$code] ?? null;
}

/** Paket dengan tagline, fitur, dan CTA sesuai bahasa. */
function billing_plans_for_lang(string $lang): array
{
    require_once __DIR__ . '/i18n_plans.php';

    $plans = billing_plans();
    $copy  = plan_strings($lang);

    foreach ($plans as $code => &$plan) {
        $type = (string) ($plan['plan_type'] ?? '');
        if (isset($copy[$type])) {
            $plan['tagline']  = $copy[$type]['tagline'];
            $plan['features'] = $copy[$type]['features'];
            $plan['cta']      = $copy[$type]['cta'];
        } elseif (isset($copy[$code])) {
            $plan['tagline']  = $copy[$code]['tagline'];
            $plan['features'] = $copy[$code]['features'];
            $plan['cta']      = $copy[$code]['cta'];
        }
    }
    unset($plan);

    return $plans;
}

function billing_plan_is_paid(string $code): bool
{
    $plan = billing_plan($code);

    return $plan !== null && ($plan['price_usd'] ?? 0) > 0;
}

function billing_interval_label(?string $interval, string $lang = 'en'): string
{
    require_once __DIR__ . '/i18n_plans.php';
    $labels = plan_interval_labels($lang);

    return match ($interval) {
        'month' => $labels['month'],
        'year'  => $labels['year'],
        default => '',
    };
}

function billing_annual_savings_percent(): int
{
    return 17;
}
