<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

function stripe_configured(): bool
{
    return STRIPE_SECRET_KEY !== '' && str_starts_with(STRIPE_SECRET_KEY, 'sk_');
}

/**
 * @param array<string, string|int|bool> $params
 * @return array<string, mixed>|null
 */
function stripe_api_request(string $method, string $path, array $params = []): ?array
{
    if (!stripe_configured()) {
        error_log('[stripe] STRIPE_SECRET_KEY tidak dikonfigurasi');
        return null;
    }

    $url = 'https://api.stripe.com/v1' . $path;
    $ch  = curl_init();

    $headers = [
        'Authorization: Bearer ' . STRIPE_SECRET_KEY,
        'Content-Type: application/x-www-form-urlencoded',
    ];

    $opts = [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ];

    if (strtoupper($method) === 'GET' && $params !== []) {
        $opts[CURLOPT_URL] .= '?' . http_build_query($params);
    } elseif ($params !== []) {
        $opts[CURLOPT_POST]       = true;
        $opts[CURLOPT_POSTFIELDS] = stripe_encode_params($params);
    }

    curl_setopt_array($ch, $opts);
    $raw  = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($raw === false || $err !== '') {
        error_log('[stripe] cURL error: ' . $err);
        return null;
    }

    $data = json_decode((string) $raw, true);
    if (!is_array($data)) {
        error_log('[stripe] invalid JSON');
        return null;
    }

    if ($code < 200 || $code >= 300) {
        $msg = $data['error']['message'] ?? ('HTTP ' . $code);
        error_log('[stripe] API error: ' . $msg);
        return null;
    }

    return $data;
}

/**
 * Encode nested params untuk Stripe (metadata[key]=value).
 *
 * @param array<string, mixed> $params
 */
function stripe_encode_params(array $params, string $prefix = ''): string
{
    $parts = [];
    foreach ($params as $key => $value) {
        $k = $prefix === '' ? (string) $key : $prefix . '[' . $key . ']';
        if (is_array($value)) {
            $parts[] = stripe_encode_params($value, $k);
        } elseif (is_bool($value)) {
            $parts[] = rawurlencode($k) . '=' . ($value ? 'true' : 'false');
        } elseif ($value !== null) {
            $parts[] = rawurlencode($k) . '=' . rawurlencode((string) $value);
        }
    }
    return implode('&', $parts);
}

/**
 * @return array<string, mixed>|null
 */
function stripe_create_checkout_session(array $params): ?array
{
    return stripe_api_request('POST', '/checkout/sessions', $params);
}

/**
 * @return array<string, mixed>|null
 */
function stripe_create_customer(string $email, string $name, array $metadata = []): ?array
{
    return stripe_api_request('POST', '/customers', [
        'email'    => $email,
        'name'     => $name,
        'metadata' => $metadata,
    ]);
}

/**
 * @return array<string, mixed>|null
 */
function stripe_create_portal_session(string $customer_id, string $return_url): ?array
{
    return stripe_api_request('POST', '/billing_portal/sessions', [
        'customer'   => $customer_id,
        'return_url' => $return_url,
    ]);
}

/**
 * Verifikasi signature webhook Stripe.
 */
function stripe_verify_webhook(string $payload, string $sig_header, string $secret): bool
{
    if ($secret === '' || $sig_header === '') {
        return false;
    }

    $parts = [];
    foreach (explode(',', $sig_header) as $item) {
        $kv = explode('=', trim($item), 2);
        if (count($kv) === 2) {
            $parts[$kv[0]] = $kv[1];
        }
    }

    $t     = $parts['t'] ?? '';
    $v1    = $parts['v1'] ?? '';
    if ($t === '' || $v1 === '') {
        return false;
    }

    $signed   = $t . '.' . $payload;
    $expected = hash_hmac('sha256', $signed, $secret);

    if (!hash_equals($expected, $v1)) {
        return false;
    }

    $tolerance = 300;
    if (abs(time() - (int) $t) > $tolerance) {
        return false;
    }

    return true;
}
