<?php
declare(strict_types=1);

require_once __DIR__ . '/plans.php';
require_once __DIR__ . '/db_schema.php';

/** @return array<string, mixed> */
function ai_system_config(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $path = dirname(__DIR__) . '/config/ai_config.php';
        $cfg  = is_file($path) ? require $path : ['enabled' => false, 'api_key' => ''];
    }

    return $cfg;
}

function billing_plan_is_managed(string $plan_type): bool
{
    return str_starts_with($plan_type, 'managed_');
}

function billing_plan_is_byok(string $plan_type): bool
{
    return str_starts_with($plan_type, 'byok_') || $plan_type === 'free';
}

/**
 * Sinkronkan kolom billing dari definisi paket.
 */
function billing_sync_client_plan_columns(PDO $pdo, int $client_id, string $plan_code): void
{
    if (!clients_managed_ai_ready($pdo)) {
        return;
    }

    $plan = billing_plan($plan_code);
    if ($plan === null) {
        return;
    }

    $quotaLimit = (int) ($plan['message_quota_limit'] ?? 0);
    $resetAt    = $quotaLimit > 0 ? billing_next_quota_reset_date() : null;

    $pdo->prepare(
        'UPDATE clients SET
            plan_type = :plan_type,
            api_key_source = :api_key_source,
            message_quota_limit = :q_limit,
            message_quota_used = CASE
                WHEN quota_reset_at IS NULL OR quota_reset_at < CURDATE() THEN 0
                ELSE message_quota_used
            END,
            quota_reset_at = COALESCE(:q_reset, quota_reset_at),
            max_websites = :max_sites,
            remove_branding = :remove_branding
         WHERE id = :id'
    )->execute([
        ':plan_type'       => (string) ($plan['plan_type'] ?? 'free'),
        ':api_key_source'  => (string) ($plan['api_key_source'] ?? 'user'),
        ':q_limit'         => $quotaLimit,
        ':q_reset'         => $resetAt,
        ':max_sites'       => (int) ($plan['max_websites'] ?? 1),
        ':remove_branding' => !empty($plan['remove_branding']) ? 1 : 0,
        ':id'              => $client_id,
    ]);
}

function billing_next_quota_reset_date(): string
{
    return (new DateTime('first day of next month'))->format('Y-m-d');
}

/**
 * Reset kuota bulanan jika sudah lewat tanggal reset.
 *
 * @param array<string, mixed> $client
 * @return array<string, mixed>
 */
function billing_refresh_quota_if_needed(PDO $pdo, array $client): array
{
    if (!clients_managed_ai_ready($pdo)) {
        return $client;
    }

    $limit = (int) ($client['message_quota_limit'] ?? 0);
    if ($limit <= 0) {
        return $client;
    }

    $resetAt = $client['quota_reset_at'] ?? null;
    $today   = (new DateTime('today'))->format('Y-m-d');

    if ($resetAt === null || $resetAt === '' || $today >= (string) $resetAt) {
        $next = billing_next_quota_reset_date();
        $pdo->prepare(
            'UPDATE clients SET message_quota_used = 0, quota_reset_at = :d WHERE id = :id'
        )->execute([':d' => $next, ':id' => (int) $client['client_id']]);
        $client['message_quota_used'] = 0;
        $client['quota_reset_at']     = $next;
    }

    return $client;
}

/**
 * Validasi domain dari Referer / Origin terhadap allowed_origins (satu domain per baris atau koma).
 */
function managed_ai_validate_referer(string $referer, string $allowed_origins): bool
{
    $allowed_origins = trim($allowed_origins);
    if ($allowed_origins === '' || $allowed_origins === '*') {
        return true;
    }

    $origin = managed_ai_extract_origin($referer);
    if ($origin === '') {
        $origin = managed_ai_extract_origin($_SERVER['HTTP_ORIGIN'] ?? '');
    }
    if ($origin === '') {
        return false;
    }

    $parts = preg_split('/[\s,]+/', $allowed_origins) ?: [];
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '' || $part === '*') {
            return true;
        }
        $allowed = managed_ai_normalize_origin($part);
        if ($allowed !== '' && strcasecmp($allowed, $origin) === 0) {
            return true;
        }
    }

    return false;
}

function managed_ai_extract_origin(string $url): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }
    $parsed = parse_url($url);
    if (!is_array($parsed) || empty($parsed['host'])) {
        return '';
    }
    $scheme = strtolower((string) ($parsed['scheme'] ?? 'https'));

    return $scheme . '://' . strtolower((string) $parsed['host'])
        . (isset($parsed['port']) ? ':' . (int) $parsed['port'] : '');
}

function managed_ai_normalize_origin(string $value): string
{
    $value = trim($value);
    if ($value === '' || $value === '*') {
        return $value === '*' ? '*' : '';
    }
    if (!preg_match('#^https?://#i', $value)) {
        $value = 'https://' . $value;
    }

    return managed_ai_extract_origin($value);
}

/**
 * @param array<string, mixed> $client
 */
function managed_ai_quota_exceeded(array $client): bool
{
    $planType = (string) ($client['plan_type'] ?? 'free');
    if (!billing_plan_is_managed($planType)) {
        return false;
    }

    $limit = (int) ($client['message_quota_limit'] ?? 0);
    if ($limit <= 0) {
        return false;
    }

    return (int) ($client['message_quota_used'] ?? 0) >= $limit;
}

function managed_ai_increment_usage(PDO $pdo, int $client_id): void
{
    if (!clients_managed_ai_ready($pdo)) {
        return;
    }

    $pdo->prepare(
        'UPDATE clients SET message_quota_used = message_quota_used + 1 WHERE id = :id'
    )->execute([':id' => $client_id]);
}

/**
 * Resolve API key + model untuk chat request.
 *
 * @param array<string, mixed> $client Row dari DB (termasuk widget settings)
 * @return array{ok:bool, provider?:string, api_key?:string, model?:string, error?:string, managed?:bool}
 */
function managed_ai_resolve_credentials(array $client): array
{
    $apiSource = (string) ($client['api_key_source'] ?? 'user');
    $planType  = (string) ($client['plan_type'] ?? 'free');

    if ($apiSource === 'system' || billing_plan_is_managed($planType)) {
        $sys = ai_system_config();
        if (empty($sys['enabled']) || trim((string) ($sys['api_key'] ?? '')) === '') {
            return [
                'ok'    => false,
                'error' => 'Managed AI is not configured on the server. Contact support.',
            ];
        }

        $model = trim((string) ($client['ai_model'] ?? ''));
        if ($model === '') {
            $model = (string) ($sys['default_model'] ?? 'openai/gpt-4o-mini');
        }

        return [
            'ok'       => true,
            'managed'  => true,
            'provider' => (string) ($sys['provider'] ?? 'openrouter'),
            'api_key'  => (string) $sys['api_key'],
            'model'    => $model,
        ];
    }

    $enc = (string) ($client['ai_api_key'] ?? '');
    $key = decrypt_secret($enc);
    if ($key === null || trim($key) === '') {
        return [
            'ok'    => false,
            'error' => 'Add your AI provider API key in the dashboard (BYOK plan).',
        ];
    }

    $model = trim((string) ($client['ai_model'] ?? ''));
    if ($model === '') {
        return [
            'ok'    => false,
            'error' => 'Set an AI model in your dashboard settings.',
        ];
    }

    return [
        'ok'       => true,
        'managed'  => false,
        'provider' => (string) ($client['ai_provider'] ?? 'openrouter'),
        'api_key'  => $key,
        'model'    => $model,
    ];
}

/**
 * @param array<string, mixed> $row Client + widget row
 * @return array<string, mixed>
 */
function widget_settings_public_payload(array $row): array
{
    $removeBranding = (int) ($row['remove_branding'] ?? 0) === 1;
    $showWatermark  = !$removeBranding;

    if (!$removeBranding) {
        $showWatermark = billing_should_show_watermark([
            'subscription_status' => $row['subscription_status'] ?? 'trial',
            'plan_code'           => $row['plan_code'] ?? 'free',
            'remove_branding'     => 0,
        ]);
    }

    $planType = (string) ($row['plan_type'] ?? 'free');
    $limit    = (int) ($row['message_quota_limit'] ?? 0);
    $used     = (int) ($row['message_quota_used'] ?? 0);

    $payload = [
        'bot_name'            => $row['bot_name'],
        'primary_color'       => $row['primary_color'],
        'bot_avatar_url'      => $row['bot_avatar_url'],
        'welcome_message'     => $row['welcome_message'],
        'show_watermark'      => $showWatermark,
        'plan_type'           => $planType,
        'api_key_source'      => (string) ($row['api_key_source'] ?? 'user'),
        'billing_track'       => billing_plan_is_managed($planType) ? 'managed' : 'byok',
        'message_quota_limit' => $limit,
        'message_quota_used'  => $used,
        'message_quota_remaining' => $limit > 0 ? max(0, $limit - $used) : null,
    ];

    if ($showWatermark) {
        $payload['watermark_brand']    = APP_NAME;
        $payload['watermark_url']      = app_base_url();
        $payload['watermark_logo_url'] = app_base_url() . '/assets/chatlm-logo.png';
    }

    return $payload;
}
