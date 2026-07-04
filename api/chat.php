<?php
/**
 * api/chat.php
 * Menerima pesan dari widget → simpan ke chat_messages → (opsional Telegram)
 * → multi-provider AI atau webhook n8n legacy → balasan ke widget.
 *
 * Method  : POST
 * Headers : Content-Type: application/json
 *           X-Api-Key: <64-char hex>
 *
 * Body JSON:
 * {
 *   "session_id": "<uuid-v4>",
 *   "message":    "Halo!"
 * }
 */

declare(strict_types=1);

set_time_limit(60);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/ai_providers.php';
require_once __DIR__ . '/../includes/db_schema.php';
require_once __DIR__ . '/../includes/managed_ai.php';
require_once __DIR__ . '/../includes/cors.php';

// ── 1. CORS — harus dikirim SEBELUM semua validasi ───────────
// Ditetapkan ke '*' dulu agar semua error response bisa dibaca
// browser. Akan diganti dengan origin spesifik klien setelah
// data client berhasil diambil dari DB (langkah 8).
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Api-Key');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── 2. Hanya izinkan POST ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['error' => 'Method not allowed.'], 405);
}

// ── 3. Validasi API Key ──────────────────────────────────────
$api_key_header = trim($_SERVER['HTTP_X_API_KEY'] ?? '');

if (!is_valid_api_key($api_key_header)) {
    send_json(['error' => 'Invalid or missing API key.'], 401);
}

// ── 4. Parse body JSON ───────────────────────────────────────
$raw_body = file_get_contents('php://input');

if (empty($raw_body) || strlen($raw_body) > 65536) {
    send_json(['error' => 'Request body invalid or too large.'], 400);
}

$body = json_decode($raw_body, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($body)) {
    send_json(['error' => 'Invalid JSON body.'], 400);
}

// ── 5. Validasi field wajib ──────────────────────────────────
$session_id = trim($body['session_id'] ?? '');
$message    = trim($body['message']    ?? '');

if (!is_valid_uuid($session_id)) {
    $session_id = generate_uuid_v4();
}

if ($message === '' || mb_strlen($message, 'UTF-8') > 4000) {
    send_json(['error' => 'Message is empty or too long (max 4000 chars).'], 422);
}

// ── 6. Ambil data klien & pengaturan widget ──────────────────
try {
    $pdo = get_db();
    $managedCols = clients_select_managed_columns_sql($pdo);

    $stmt = $pdo->prepare("
        SELECT
            c.id                  AS client_id,
            c.subscription_status,
            c.plan_code,
            {$managedCols}
            c.name                AS client_name,
            ws.n8n_webhook_url,
            ws.allowed_origins,
            ws.bot_name,
            ws.ai_provider,
            ws.ai_api_key,
            ws.ai_model,
            ws.ai_system_prompt,
            ws.telegram_notify_enabled,
            ws.telegram_chat_id
        FROM clients c
        INNER JOIN widget_settings ws ON ws.client_id = c.id
        WHERE c.api_key = :api_key
        LIMIT 1
    ");

    $stmt->execute([':api_key' => $api_key_header]);
    $client = $stmt->fetch();
    if (is_array($client)) {
        $client = clients_enrich_row($client, $pdo);
    }

} catch (PDOException $e) {
    error_log('[chat] DB error: ' . $e->getMessage());
    send_json(['error' => 'Internal server error.'], 500);
}

if (!$client) {
    send_json(['error' => 'API key not found.'], 404);
}

if ($client['subscription_status'] === 'inactive') {
    send_json(['error' => 'Subscription is inactive.'], 403);
}

$client = billing_refresh_quota_if_needed($pdo, $client);

// ── 7. Domain whitelist (Referer / Origin) ───────────────────
$allowed_origins = (string) ($client['allowed_origins'] ?? '*');
$referer         = (string) ($_SERVER['HTTP_REFERER'] ?? '');
if (!managed_ai_validate_referer($referer, $allowed_origins) && !cors_is_same_server_request()) {
    send_json(['error' => 'This domain is not allowed to use this widget. Add it in Allowed Origins.'], 403);
}

// ── 8. Kuota pesan (Managed AI) ──────────────────────────────
if (managed_ai_quota_exceeded($client)) {
    send_json(['error' => 'Message quota exceeded. Please upgrade your plan.'], 402);
}

// ── 9. Credentials: Managed (system) vs BYOK (user key) ────────
$credentials = managed_ai_resolve_credentials($client);
$has_ai      = $credentials['ok'];

$n8n_raw = trim((string) ($client['n8n_webhook_url'] ?? ''));
$n8n_ok  = $n8n_raw !== ''
    && filter_var($n8n_raw, FILTER_VALIDATE_URL)
    && preg_match('/^https?:\/\//i', $n8n_raw) === 1;

if (!$has_ai && !$n8n_ok) {
    send_json([
        'error' => $credentials['error'] ?? 'AI is not configured. Add your API key (BYOK) or upgrade to a Managed plan.',
    ], 503);
}

// ── 10. CORS — selalu izinkan domain app + Allowed Origins klien ─
$allowed_for_cors = cors_ensure_app_site_in_list($allowed_origins);
if (!cors_apply_for_widget($allowed_for_cors) && !cors_is_same_server_request()) {
    send_json(['error' => cors_forbidden_message($allowed_origins)], 403);
}

$user_ip = get_client_ip();

// ── 9. Riwayat percakapan (sebelum pesan user baru disimpan) ─
$history_rows = fetch_chat_history_rows(
    $pdo,
    (int) $client['client_id'],
    $session_id,
    48
);

// ── 10. Simpan pesan user ────────────────────────────────────
chat_message_insert($pdo, (int) $client['client_id'], $session_id, 'user', $message, $user_ip);

// ── 11. Notifikasi Telegram (pesan masuk) ────────────────────
if ((int) ($client['telegram_notify_enabled'] ?? 0) === 1) {
    $tg_chat = trim((string) ($client['telegram_chat_id'] ?? ''));
    telegram_notify_new_message(
        $tg_chat,
        (string) $client['client_name'],
        $session_id,
        $message
    );
}

// ── 12. Panggil AI atau n8n ──────────────────────────────────
if ($has_ai) {
    $widget_cfg = [
        'ai_provider'      => (string) ($credentials['provider'] ?? $client['ai_provider']),
        'ai_model'         => (string) ($credentials['model'] ?? $client['ai_model']),
        'ai_system_prompt' => (string) ($client['ai_system_prompt'] ?? ''),
        'bot_name'         => (string) ($client['bot_name'] ?? 'Assistant'),
    ];

    $ai_result = ai_chat_complete(
        $widget_cfg,
        (string) $credentials['api_key'],
        $history_rows,
        $message
    );

    if (!$ai_result['ok']) {
        $err = $ai_result['error'] ?? 'AI gagal memproses permintaan.';
        send_json(['error' => $err], 502);
    }

    $bot_reply = (string) $ai_result['reply'];

    if (!empty($credentials['managed']) && (int) ($client['message_quota_limit'] ?? 0) > 0) {
        managed_ai_increment_usage($pdo, (int) $client['client_id']);
    }
} else {
    $payload = json_encode([
        'session_id' => $session_id,
        'message'    => $message,
        'client_id'  => (int) $client['client_id'],
        'bot_name'   => $client['bot_name'],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $bot_reply = call_n8n_webhook($n8n_raw, $payload);

    if ($bot_reply === null) {
        send_json([
            'error' => 'Webhook n8n tidak merespon atau timeout. Periksa URL workflow dan log server.',
        ], 502);
    }
}

// ── 13. Simpan jawaban asisten ───────────────────────────────
chat_message_insert($pdo, (int) $client['client_id'], $session_id, 'assistant', $bot_reply, '');

// ── 14. Response ─────────────────────────────────────────────
send_json([
    'reply'      => $bot_reply,
    'session_id' => $session_id,
]);

// ════════════════════════════════════════════════════════════
// FUNGSI HELPER LOKAL
// ════════════════════════════════════════════════════════════

function chat_message_insert(
    PDO $pdo,
    int $client_id,
    string $session_id,
    string $role,
    string $body,
    string $ip
): void {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO chat_messages (client_id, session_id, role, body, ip_address)
            VALUES (:client_id, :session_id, :role, :body, :ip)
        ");
        $stmt->execute([
            ':client_id'  => $client_id,
            ':session_id' => $session_id,
            ':role'       => $role === 'assistant' ? 'assistant' : 'user',
            ':body'       => mb_substr($body, 0, 65535, 'UTF-8'),
            ':ip'         => mb_substr($ip, 0, 45, 'UTF-8'),
        ]);
    } catch (PDOException $e) {
        error_log('[chat] chat_messages insert error: ' . $e->getMessage());
        send_json(['error' => 'Gagal menyimpan percakapan.'], 500);
    }
}

/**
 * Legacy n8n webhook.
 */
function call_n8n_webhook(string $url, string $json_payload): ?string
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $json_payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json_payload),
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => WEBHOOK_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_MAXREDIRS      => 0,
    ]);

    $response   = curl_exec($ch);
    $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error !== '') {
        error_log('[chat] n8n cURL error: ' . $curl_error);
        return null;
    }

    if ($http_code < 200 || $http_code >= 300) {
        error_log('[chat] n8n HTTP ' . $http_code . ': ' . mb_substr((string) $response, 0, 1500));
        return null;
    }

    $decoded = json_decode((string) $response, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        return $decoded['reply']
            ?? $decoded['message']
            ?? $decoded['output']
            ?? $decoded['text']
            ?? $decoded['response']
            ?? (is_string($decoded[0] ?? null) ? $decoded[0] : null)
            ?? json_encode($decoded, JSON_UNESCAPED_UNICODE);
    }

    return is_string($response) && $response !== '' ? trim($response) : null;
}

function get_client_ip(): string
{
    $headers = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'REMOTE_ADDR',
    ];

    foreach ($headers as $header) {
        $ip = $_SERVER[$header] ?? '';
        if ($ip !== '') {
            $ip = trim(explode(',', $ip)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return 'unknown';
}

function generate_uuid_v4(): string
{
    $data    = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
