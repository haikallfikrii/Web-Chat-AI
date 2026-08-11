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
    'DB_NAME' => 'u451240370_chatlm_prod',
    'DB_USER' => 'u451240370_khalfikrii',
    'DB_PASS' => 'AellImehh10.',

    // Token dari BotFather (@ChatlmAsistantBot) — JANGAN taruh di dashboard Chat ID
    'TELEGRAM_BOT_TOKEN' => '123456789:AAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',

    // Wajib mode TEST di Stripe Dashboard
    'STRIPE_SECRET_KEY' => 'sk_test_...',
    'STRIPE_PUBLISHABLE_KEY' => 'pk_test_...',
    'STRIPE_WEBHOOK_SECRET' => 'whsec_...',
    'STRIPE_PRICE_STARTER_MONTHLY' => 'price_test_...',
    'STRIPE_PRICE_PRO_MONTHLY' => 'price_test_...',
    'STRIPE_PRICE_STARTER_YEARLY' => 'price_test_...',
    'STRIPE_PRICE_PRO_YEARLY' => 'price_test_...',

    // Harus alamat mailbox yang benar-benar ada, kalau tidak SMTP menolaknya.
    'MAIL_FROM_ADDRESS' => 'team@chatlm.tech',
    'MAIL_FROM_NAME' => 'ChatLM Staging',
    'MAIL_SUPPORT' => 'support@chatlm.tech',

    // SMTP Hostinger — kalau kosong, sistem memakai mail() PHP yang di shared
    // hosting sering gagal tanpa pemberitahuan.
    'MAIL_SMTP_HOST' => 'smtp.hostinger.com',
    'MAIL_SMTP_PORT' => '465',
    'MAIL_SMTP_USER' => 'team@chatlm.tech',
    'MAIL_SMTP_PASS' => 'password-mailbox-hostinger',
    'MAIL_SMTP_SECURE' => 'ssl',

    'TRIAL_DAYS' => '14',

    // Akses /admin.php (email login harus ada di daftar ini)
    'PLATFORM_ADMIN_EMAILS' => 'team@chatlm.tech',
    'PLATFORM_NOTIFY_EMAIL' => 'team@chatlm.tech',

    // Kosongkan jika tidak ingin widget di beranda staging
    'LANDING_WIDGET_API_KEY' => '',

    // Secret key untuk akses cron.php via URL (opsional)
    'CRON_SECRET' => 'change-this-to-random-string',
];
