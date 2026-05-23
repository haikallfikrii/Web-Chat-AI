<?php
declare(strict_types=1);

/**
 * Definisi paket ChatLM.
 * Stripe Price ID diisi lewat env (STRIPE_PRICE_*).
 */
function app_site_url(): string
{
    return app_base_url();
}

function billing_plans(): array
{
    return [
        'free' => [
            'code'              => 'free',
            'name'              => 'Free',
            'tagline'           => 'Coba widget dengan watermark',
            'price_usd'         => 0,
            'price_display'     => '$0',
            'interval'          => null,
            'stripe_price_id'   => null,
            'show_watermark'    => true,
            'subscription_status' => 'trial',
            'features'          => [
                'Widget chat AI di 1 website',
                'Watermark "Powered by ChatLM"',
                'Trial 14 hari penuh',
                'Notifikasi Telegram',
                'Multi-provider AI (BYOK)',
            ],
            'highlight'         => false,
            'cta'               => 'Pakai Gratis',
        ],
        'starter_monthly' => [
            'code'              => 'starter_monthly',
            'name'              => 'Starter',
            'tagline'           => 'Untuk bisnis kecil & solo founder',
            'price_usd'         => 19,
            'price_display'     => '$19',
            'interval'          => 'month',
            'stripe_price_id'   => STRIPE_PRICE_STARTER_MONTHLY,
            'show_watermark'    => false,
            'subscription_status' => 'active',
            'features'          => [
                'Tanpa watermark',
                '1 website / widget',
                'Riwayat chat & memori AI',
                'Notifikasi Telegram',
                'Dukungan email standar',
            ],
            'highlight'         => false,
            'cta'               => 'Mulai Starter',
        ],
        'pro_monthly' => [
            'code'              => 'pro_monthly',
            'name'              => 'Pro',
            'tagline'           => 'Tim kecil & agensi',
            'price_usd'         => 49,
            'price_display'     => '$49',
            'interval'          => 'month',
            'stripe_price_id'   => STRIPE_PRICE_PRO_MONTHLY,
            'show_watermark'    => false,
            'subscription_status' => 'active',
            'features'          => [
                'Semua fitur Starter',
                'Prioritas dukungan',
                'Branding penuh (tanpa watermark)',
                'Cocok untuk multi-brand',
                'Ideal pasar global (USD)',
            ],
            'highlight'         => true,
            'cta'               => 'Upgrade ke Pro',
        ],
        'starter_yearly' => [
            'code'              => 'starter_yearly',
            'name'              => 'Starter',
            'tagline'           => 'Bayar tahunan — hemat ~2 bulan',
            'price_usd'         => 190,
            'price_display'     => '$190',
            'interval'          => 'year',
            'stripe_price_id'   => STRIPE_PRICE_STARTER_YEARLY,
            'show_watermark'    => false,
            'subscription_status' => 'active',
            'features'          => [
                'Semua fitur Starter bulanan',
                'Tagihan tahunan sekali',
                'Tanpa watermark',
                'Hemat dibanding bulanan',
            ],
            'highlight'         => false,
            'cta'               => 'Starter Tahunan',
        ],
        'pro_yearly' => [
            'code'              => 'pro_yearly',
            'name'              => 'Pro',
            'tagline'           => 'Terbaik untuk retensi jangka panjang',
            'price_usd'         => 490,
            'price_display'     => '$490',
            'interval'          => 'year',
            'stripe_price_id'   => STRIPE_PRICE_PRO_YEARLY,
            'show_watermark'    => false,
            'subscription_status' => 'active',
            'features'          => [
                'Semua fitur Pro bulanan',
                'Tagihan tahunan',
                'Tanpa watermark',
                'Prioritas dukungan',
            ],
            'highlight'         => false,
            'cta'               => 'Pro Tahunan',
        ],
    ];
}

/** Paket dengan tagline, fitur, dan CTA sesuai bahasa. */
function billing_plans_for_lang(string $lang): array
{
    require_once __DIR__ . '/i18n_plans.php';

    $plans  = billing_plans();
    $copy   = plan_strings($lang);

    foreach ($plans as $code => &$plan) {
        if (!isset($copy[$code])) {
            continue;
        }
        $plan['tagline']  = $copy[$code]['tagline'];
        $plan['features'] = $copy[$code]['features'];
        $plan['cta']      = $copy[$code]['cta'];
    }
    unset($plan);

    return $plans;
}

function billing_plan(string $code): ?array
{
    $plans = billing_plans();
    return $plans[$code] ?? null;
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
