<?php
/**
 * Template config.local.php untuk STAGING
 * Domain: https://staging.chatlm.tech
 * Branch Git: staging
 *
 * Di Hostinger (folder staging):
 *   cp config.local.staging.example.php config.local.php
 *   lalu edit DB_* dengan kredensial database STAGING dari phpMyAdmin.
 */
declare(strict_types=1);

return [
    'APP_ENV' => 'staging',
    'APP_SECRET' => 'staging-secret-min-32-chars-unique',
    'APP_SITE_URL' => 'https://staging.chatlm.tech',
    'APP_NAME' => 'ChatLM (Staging)',

    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '3306',
    'DB_NAME' => 'u123456789_chatlm_staging',
    'DB_USER' => 'u123456789_staging_user',
    'DB_PASS' => 'PASSWORD_STAGING_DI_SINI',

    'TELEGRAM_BOT_TOKEN' => '',

    // Wajib mode TEST di Stripe Dashboard
    'STRIPE_SECRET_KEY' => 'sk_test_...',
    'STRIPE_PUBLISHABLE_KEY' => 'pk_test_...',
    'STRIPE_WEBHOOK_SECRET' => 'whsec_...',
    'STRIPE_PRICE_STARTER_MONTHLY' => 'price_test_...',
    'STRIPE_PRICE_PRO_MONTHLY' => 'price_test_...',
    'STRIPE_PRICE_STARTER_YEARLY' => 'price_test_...',
    'STRIPE_PRICE_PRO_YEARLY' => 'price_test_...',

    'MAIL_FROM_ADDRESS' => 'staging@chatlm.tech',
    'MAIL_FROM_NAME' => 'ChatLM Staging',
    'MAIL_SUPPORT' => 'support@chatlm.tech',

    'TRIAL_DAYS' => '14',

    // Widget demo di landing page (index.php) — API key dari dashboard staging
    'LANDING_WIDGET_API_KEY' => '0156790afc7edc03c198d93a358243c7750464c345d6596d7fe8806410cda026',
];
