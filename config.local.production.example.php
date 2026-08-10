<?php
/**
 * Template config.local.php untuk PRODUCTION
 * Domain: https://chatlm.tech
 * Branch Git: production
 *
 * Di Hostinger (folder production):
 *   cp config.local.production.example.php config.local.php
 *   lalu edit DB_* dengan kredensial database PRODUCTION dari phpMyAdmin.
 */
declare(strict_types=1);

return [
    'APP_ENV' => 'production',
    'APP_SECRET' => 'production-secret-min-32-chars-unique-DIFFERENT-FROM-STAGING',
    'APP_SITE_URL' => 'https://chatlm.tech',
    'APP_NAME' => 'ChatLM',

    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '3306',
    'DB_NAME' => 'u451240370_chatlm_prod',
    'DB_USER' => 'u451240370_khalfikrii',
    'DB_PASS' => 'AellImehh10.',

    // Token dari BotFather (@ChatlmAsistantBot) — JANGAN taruh di dashboard Chat ID
    'TELEGRAM_BOT_TOKEN' => '123456789:AAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',

    // Mode LIVE di Stripe Dashboard
    'STRIPE_SECRET_KEY' => 'sk_live_...',
    'STRIPE_PUBLISHABLE_KEY' => 'pk_live_...',
    'STRIPE_WEBHOOK_SECRET' => 'whsec_...',
    'STRIPE_PRICE_STARTER_MONTHLY' => 'price_live_...',
    'STRIPE_PRICE_PRO_MONTHLY' => 'price_live_...',
    'STRIPE_PRICE_STARTER_YEARLY' => 'price_live_...',
    'STRIPE_PRICE_PRO_YEARLY' => 'price_live_...',

    'STRIPE_PRICE_BYOK_STARTER_MONTHLY' => 'price_live_...',
    'STRIPE_PRICE_BYOK_STARTER_YEARLY' => 'price_live_...',
    'STRIPE_PRICE_BYOK_PRO_MONTHLY' => 'price_live_...',
    'STRIPE_PRICE_BYOK_PRO_YEARLY' => 'price_live_...',
    'STRIPE_PRICE_BYOK_AGENCY_MONTHLY' => 'price_live_...',
    'STRIPE_PRICE_BYOK_AGENCY_YEARLY' => 'price_live_...',
    'STRIPE_PRICE_MANAGED_STARTER_MONTHLY' => 'price_live_...',
    'STRIPE_PRICE_MANAGED_STARTER_YEARLY' => 'price_live_...',
    'STRIPE_PRICE_MANAGED_PRO_MONTHLY' => 'price_live_...',
    'STRIPE_PRICE_MANAGED_PRO_YEARLY' => 'price_live_...',
    'STRIPE_PRICE_MANAGED_AGENCY_MONTHLY' => 'price_live_...',
    'STRIPE_PRICE_MANAGED_AGENCY_YEARLY' => 'price_live_...',

    'SYSTEM_OPENROUTER_API_KEY' => 'sk-or-v1-...',
    'SYSTEM_AI_PROVIDER' => 'openrouter',
    'SYSTEM_AI_DEFAULT_MODEL' => 'openai/gpt-4o-mini',
    'SYSTEM_AI_FALLBACK_MODELS' => 'deepseek/deepseek-chat,google/gemini-2.0-flash',

    'MAIL_FROM_ADDRESS' => 'billing@chatlm.tech',
    'MAIL_FROM_NAME' => 'ChatLM',
    'MAIL_SUPPORT' => 'support@chatlm.tech',

    'TRIAL_DAYS' => '14',

    'PLATFORM_ADMIN_EMAILS' => 'team@chatlm.tech',
    'PLATFORM_NOTIFY_EMAIL' => 'team@chatlm.tech',

    // Widget di beranda chatlm.tech (index.php) — demo untuk situs sendiri
    'LANDING_WIDGET_API_KEY' => '0156790afc7edc03c198d93a358243c7750464c345d6596d7fe8806410cda026',
];
