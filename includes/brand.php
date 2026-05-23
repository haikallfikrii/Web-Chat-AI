<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/** Path relatif logo di web root */
function brand_logo_path(): string
{
    return '/assets/chatlm-logo.png';
}

function brand_logo_url(): string
{
    return app_base_url() . brand_logo_path();
}

/** HTML logo untuk header/nav */
function brand_mark_html(int $size = 32): string
{
    $url = brand_logo_url();
    $alt = htmlspecialchars(APP_NAME, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $src = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    return '<span class="brand-mark brand-mark--logo">'
        . '<img src="' . $src . '" alt="' . $alt . '" width="' . $size . '" height="' . $size . '" class="brand-logo" loading="eager" decoding="async">'
        . '</span>';
}

function brand_name_html(): string
{
    return htmlspecialchars(APP_NAME, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Favicon & Apple touch icon (Google Search min 48×48; root /favicon.ico disarankan) */
function brand_favicon_tags(): string
{
    $base = rtrim(app_base_url(), '/');
    $ico  = htmlspecialchars($base . '/favicon.ico', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $png  = htmlspecialchars(brand_logo_url(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $manifest = htmlspecialchars($base . '/site.webmanifest', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    return '<link rel="icon" href="' . $ico . '" sizes="any">'
        . '<link rel="icon" type="image/png" sizes="192x192" href="' . $png . '">'
        . '<link rel="apple-touch-icon" sizes="180x180" href="' . $png . '">'
        . '<link rel="manifest" href="' . $manifest . '">';
}
