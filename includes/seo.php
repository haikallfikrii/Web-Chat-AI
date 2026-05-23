<?php
declare(strict_types=1);

require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/brand.php';

/**
 * URL absolut kanonis (tanpa fragment).
 */
function seo_absolute_url(string $path = '/', array $params = []): string
{
    $base = rtrim(app_site_url(), '/');
    $rel  = app_url($path, $params);

    if (str_starts_with($rel, 'http://') || str_starts_with($rel, 'https://')) {
        return $rel;
    }

    return $base . ($rel === '' || $rel === '/' ? '/' : $rel);
}

/**
 * Gambar OG default (logo produk, min 200×200 untuk Google).
 */
function seo_default_image(): string
{
    return app_base_url() . '/assets/chatlm-logo.png';
}

/**
 * @param array{
 *   title: string,
 *   description: string,
 *   path?: string,
 *   params?: array<string,string>,
 *   type?: string,
 *   image?: string,
 *   noindex?: bool,
 *   article_published?: string,
 *   json_ld?: array<int, array<string, mixed>>,
 * } $opts
 */
function seo_render_head(array $opts): void
{
    $title       = trim($opts['title']);
    $description = trim($opts['description']);
    $path        = $opts['path'] ?? '/';
    $params      = $opts['params'] ?? [];
    $type        = $opts['type'] ?? 'website';
    $image       = $opts['image'] ?? seo_default_image();
    $noindex     = !empty($opts['noindex']);
    $canonical   = seo_absolute_url($path, $params);
    $site        = app_base_url();
    $appName     = APP_NAME;

    $titleEsc = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $descEsc  = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
    $canEsc   = htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8');
    $imgEsc   = htmlspecialchars($image, ENT_QUOTES, 'UTF-8');
    $siteEsc  = htmlspecialchars($site, ENT_QUOTES, 'UTF-8');
    $nameEsc  = htmlspecialchars($appName, ENT_QUOTES, 'UTF-8');

    echo "<title>{$titleEsc}</title>\n";
    echo "<meta name=\"description\" content=\"{$descEsc}\">\n";
    echo "<meta name=\"robots\" content=\"" . ($noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large') . "\">\n";
    echo "<link rel=\"canonical\" href=\"{$canEsc}\">\n";

    seo_render_hreflang($path, $params);

    echo "<meta property=\"og:type\" content=\"" . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . "\">\n";
    echo "<meta property=\"og:site_name\" content=\"{$nameEsc}\">\n";
    echo "<meta property=\"og:title\" content=\"{$titleEsc}\">\n";
    echo "<meta property=\"og:description\" content=\"{$descEsc}\">\n";
    echo "<meta property=\"og:url\" content=\"{$canEsc}\">\n";
    echo "<meta property=\"og:image\" content=\"{$imgEsc}\">\n";
    echo "<meta property=\"og:locale\" content=\"" . seo_og_locale(get_lang()) . "\">\n";

    echo "<meta name=\"twitter:card\" content=\"summary_large_image\">\n";
    echo "<meta name=\"twitter:title\" content=\"{$titleEsc}\">\n";
    echo "<meta name=\"twitter:description\" content=\"{$descEsc}\">\n";
    echo "<meta name=\"twitter:image\" content=\"{$imgEsc}\">\n";

    if ($type === 'article' && !empty($opts['article_published'])) {
        $pub = htmlspecialchars($opts['article_published'], ENT_QUOTES, 'UTF-8');
        echo "<meta property=\"article:published_time\" content=\"{$pub}\">\n";
    }

    $jsonLd = $opts['json_ld'] ?? [];
    $jsonLd[] = [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => $appName,
        'url'      => $site,
        'logo'     => seo_default_image(),
    ];

    if (count($jsonLd) === 1) {
        echo '<script type="application/ld+json">' . json_encode($jsonLd[0], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
    } else {
        echo '<script type="application/ld+json">' . json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
    }
}

function seo_og_locale(string $lang): string
{
    return match ($lang) {
        'id' => 'id_ID',
        'es' => 'es_ES',
        'fr' => 'fr_FR',
        'pt' => 'pt_BR',
        'ja' => 'ja_JP',
        default => 'en_US',
    };
}

function seo_render_hreflang(string $path, array $baseParams = []): void
{
    $langs = ['en', 'id', 'es', 'fr', 'pt', 'ja'];
    foreach ($langs as $code) {
        $params = array_merge($baseParams, ['lang' => $code]);
        $href   = seo_absolute_url($path, $params);
        echo '<link rel="alternate" hreflang="' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    }
    $default = seo_absolute_url($path, array_merge($baseParams, ['lang' => 'en']));
    echo '<link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($default, ENT_QUOTES, 'UTF-8') . '">' . "\n";
}

/** Meta title + description untuk halaman landing (per bahasa). */
function seo_landing_meta(string $lang): array
{
    $all = [
        'en' => [
            'title' => 'ChatLM — AI Chat Widget for Any Website | Free Trial',
            'description' => 'Embed an AI chat widget in 5 minutes. OpenAI, Gemini, DeepSeek & OpenRouter. Custom branding, chat memory, Telegram alerts. Start free.',
        ],
        'id' => [
            'title' => 'ChatLM — Widget Chat AI untuk Website | Trial Gratis',
            'description' => 'Pasang widget chat AI di website dalam 5 menit. Multi-provider, branding kustom, memori chat. Daftar gratis — tanpa kartu kredit.',
        ],
        'es' => [
            'title' => 'ChatLM — Widget de Chat IA para tu Web | Prueba Gratis',
            'description' => 'Añade un widget de chat IA en 5 minutos. OpenAI, Gemini, DeepSeek y OpenRouter. Marca personalizada. Empieza gratis.',
        ],
        'fr' => [
            'title' => 'ChatLM — Widget Chat IA pour votre Site | Essai Gratuit',
            'description' => 'Intégrez un widget chat IA en 5 minutes. Multi-fournisseurs, branding personnalisé. Essai gratuit.',
        ],
        'pt' => [
            'title' => 'ChatLM — Widget de Chat IA para Sites | Trial Grátis',
            'description' => 'Adicione chat IA ao seu site em 5 minutos. OpenAI, Gemini, DeepSeek. Comece grátis.',
        ],
        'ja' => [
            'title' => 'ChatLM — ウェブサイト向けAIチャットウィジェット | 無料トライアル',
            'description' => '5分でAIチャットを埋め込み。OpenAI・Gemini・DeepSeek対応。無料で始められます。',
        ],
    ];

    return $all[$lang] ?? $all['en'];
}

function seo_pricing_meta(string $lang): array
{
    $all = [
        'en' => [
            'title' => 'Pricing — AI Chat Widget Plans (Free, Starter, Pro) | ChatLM',
            'description' => 'Compare ChatLM plans: free widget with watermark, Starter & Pro without branding. Monthly or yearly billing in USD.',
        ],
        'id' => [
            'title' => 'Harga Paket ChatLM — Free, Starter, Pro',
            'description' => 'Bandingkan paket widget chat AI: gratis dengan watermark, Starter & Pro tanpa branding. Harga transparan USD.',
        ],
        'es' => ['title' => 'Precios ChatLM — Planes Free, Starter, Pro', 'description' => 'Planes de widget chat IA: gratis, Starter y Pro. Precios en USD.'],
        'fr' => ['title' => 'Tarifs ChatLM — Offres Free, Starter, Pro', 'description' => 'Forfaits widget chat IA. Gratuit, Starter et Pro. Facturation USD.'],
        'pt' => ['title' => 'Preços ChatLM — Planos Free, Starter, Pro', 'description' => 'Planos de widget chat IA. Comece grátis ou upgrade sem marca d\'água.'],
        'ja' => ['title' => 'ChatLM 料金プラン — Free / Starter / Pro', 'description' => 'AIチャットウィジェットの料金。無料プランからProまで。'],
    ];

    return $all[$lang] ?? $all['en'];
}

function seo_landing_json_ld(string $lang): array
{
    $meta = seo_landing_meta($lang);

    return [
        '@context' => 'https://schema.org',
        '@type'    => 'SoftwareApplication',
        'name'     => APP_NAME,
        'applicationCategory' => 'BusinessApplication',
        'operatingSystem'     => 'Web',
        'description'         => $meta['description'],
        'url'                 => app_base_url(),
        'offers'              => [
            '@type'         => 'Offer',
            'price'         => '0',
            'priceCurrency' => 'USD',
        ],
    ];
}
