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

    'TELEGRAM_BOT_TOKEN' => '',

    // Mode LIVE di Stripe Dashboard
    'STRIPE_SECRET_KEY' => 'sk_live_...',
    'STRIPE_PUBLISHABLE_KEY' => 'pk_live_...',
    'STRIPE_WEBHOOK_SECRET' => 'whsec_...',
    'STRIPE_PRICE_STARTER_MONTHLY' => 'price_live_...',
    'STRIPE_PRICE_PRO_MONTHLY' => 'price_live_...',
    'STRIPE_PRICE_STARTER_YEARLY' => 'price_live_...',
    'STRIPE_PRICE_PRO_YEARLY' => 'price_live_...',

    'MAIL_FROM_ADDRESS' => 'billing@chatlm.tech',
    'MAIL_FROM_NAME' => 'ChatLM',
    'MAIL_SUPPORT' => 'support@chatlm.tech',

    'TRIAL_DAYS' => '14',
];
