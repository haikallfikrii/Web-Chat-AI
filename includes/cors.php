<?php
declare(strict_types=1);

/**
 * CORS untuk widget embed — origin harus cocok persis (browser menolak header salah).
 */

function cors_request_origin(): string
{
    $origin = trim((string) ($_SERVER['HTTP_ORIGIN'] ?? ''));
    if ($origin !== '') {
        return cors_normalize_origin($origin);
    }

    $referer = trim((string) ($_SERVER['HTTP_REFERER'] ?? ''));
    if ($referer === '') {
        return '';
    }

    $parts = parse_url($referer);
    if (empty($parts['scheme']) || empty($parts['host'])) {
        return '';
    }

    $scheme = strtolower((string) $parts['scheme']);
    $host   = strtolower((string) $parts['host']);
    $port   = isset($parts['port']) ? (int) $parts['port'] : ($scheme === 'https' ? 443 : 80);

    return cors_build_origin($scheme, $host, $port);
}

function cors_build_origin(string $scheme, string $host, int $port): string
{
    $default = ($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80);
    $suffix  = $default ? '' : ':' . $port;

    return $scheme . '://' . $host . $suffix;
}

function cors_normalize_origin(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '' || $raw === '*') {
        return '*';
    }

    if (!preg_match('#^https?://#i', $raw)) {
        $raw = 'https://' . ltrim($raw, '/');
    }

    $parts = parse_url($raw);
    if (empty($parts['scheme']) || empty($parts['host'])) {
        return '';
    }

    $scheme = strtolower((string) $parts['scheme']);
    $host   = strtolower((string) $parts['host']);
    $port   = isset($parts['port']) ? (int) $parts['port'] : ($scheme === 'https' ? 443 : 80);

    return cors_build_origin($scheme, $host, $port);
}

function cors_host_key(string $origin): string
{
    $host = parse_url($origin, PHP_URL_HOST);
    if (!is_string($host) || $host === '') {
        return '';
    }

    return (string) preg_replace('/^www\./i', '', strtolower($host));
}

function cors_origins_match(string $request_origin, string $allowed_entry): bool
{
    if ($allowed_entry === '*' || $request_origin === '') {
        return true;
    }

    $req = cors_normalize_origin($request_origin);
    $alw = cors_normalize_origin($allowed_entry);

    if ($req === '' || $alw === '') {
        return false;
    }

    if ($req === $alw) {
        return true;
    }

    $reqScheme = parse_url($req, PHP_URL_SCHEME);
    $alwScheme = parse_url($alw, PHP_URL_SCHEME);
    if ($reqScheme !== $alwScheme) {
        return false;
    }

    return cors_host_key($req) === cors_host_key($alw);
}

/** @return list<string> */
function cors_parse_allowed_list(string $allowed_csv): array
{
    $allowed_csv = trim($allowed_csv);
    if ($allowed_csv === '' || $allowed_csv === '*') {
        return ['*'];
    }

    $parts = preg_split('/[\s,]+/', $allowed_csv, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $out   = [];

    foreach ($parts as $part) {
        $norm = cors_normalize_origin((string) $part);
        if ($norm !== '' && $norm !== '*') {
            $out[] = $norm;
        }
    }

    return $out === [] ? ['*'] : array_values(array_unique($out));
}

/**
 * Normalisasi input dashboard (simpan format konsisten).
 */
function cors_normalize_allowed_field(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '' || $raw === '*') {
        return '*';
    }

    $list = cors_parse_allowed_list($raw);

    return in_array('*', $list, true) ? '*' : implode(', ', $list);
}

/**
 * Terapkan header CORS untuk widget. Mengembalikan false jika origin ditolak.
 */
function cors_apply_for_widget(string $allowed_csv): bool
{
    $list = cors_parse_allowed_list($allowed_csv);
    $req  = cors_request_origin();

    if (in_array('*', $list, true)) {
        set_cors_headers($req !== '' ? $req : '*');

        return true;
    }

    if ($req === '') {
        set_cors_headers('*');

        return true;
    }

    foreach ($list as $entry) {
        if (cors_origins_match($req, $entry)) {
            set_cors_headers($req);

            return true;
        }
    }

    set_cors_headers($req);

    return false;
}

function cors_forbidden_message(string $allowed_csv): string
{
    $req = cors_request_origin();
    $hint = $req !== '' ? $req : 'situs Anda';

    return 'Domain tidak diizinkan (' . $hint . '). Tambahkan URL lengkap di Allowed Origins di dashboard ChatLM (contoh: https://domain-anda.com).';
}

/** Header CORS untuk respons error sebelum data klien diketahui. */
function cors_apply_permissive(): void
{
    $req = cors_request_origin();
    set_cors_headers($req !== '' ? $req : '*');
}

/**
 * Saat menyimpan pengaturan: pastikan domain ChatLM sendiri ikut terdaftar (demo di beranda).
 */
function cors_ensure_app_site_in_list(string $allowed_csv): string
{
    if ($allowed_csv === '*') {
        return '*';
    }

    $list = cors_parse_allowed_list($allowed_csv);
    $candidates = [app_base_url(), APP_SITE_URL];

    foreach ($candidates as $url) {
        $norm = cors_normalize_origin((string) $url);
        if ($norm !== '' && $norm !== '*' && !in_array($norm, $list, true)) {
            $list[] = $norm;
        }
    }

    return implode(', ', $list);
}
