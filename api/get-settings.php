<?php
/**
 * api/get-settings.php
 * Mengembalikan konfigurasi widget berdasarkan api_key.
 * Dipanggil oleh widget.js saat pertama kali dimuat.
 *
 * Method : GET
 * Header : X-Api-Key: <64-char hex>
 *
 * Response 200:
 * {
 *   "bot_name":        "...",
 *   "primary_color":   "#4F46E5",
 *   "bot_avatar_url":  "...",
 *   "welcome_message": "..."
 * }
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/plans.php';
require_once __DIR__ . '/../includes/billing.php';
require_once __DIR__ . '/../includes/cors.php';

// ── 1. Handle preflight OPTIONS ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    set_cors_headers($_SERVER['HTTP_ORIGIN'] ?? '*');
    http_response_code(204);
    exit;
}

// ── 2. Hanya izinkan GET ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json(['error' => 'Method not allowed.'], 405);
}

// ── 3. Ambil & validasi API Key dari header ──────────────────
$api_key = trim($_SERVER['HTTP_X_API_KEY'] ?? '');

if (!is_valid_api_key($api_key)) {
    cors_apply_permissive();
    send_json(['error' => 'Invalid or missing API key.'], 401);
}

// ── 4. Query settings berdasarkan api_key ────────────────────
try {
    $pdo = get_db();

    $stmt = $pdo->prepare("
        SELECT
            c.id                    AS client_id,
            c.subscription_status,
            c.plan_code,
            ws.primary_color,
            ws.bot_name,
            ws.bot_avatar_url,
            ws.welcome_message,
            ws.allowed_origins
        FROM clients c
        INNER JOIN widget_settings ws ON ws.client_id = c.id
        WHERE c.api_key = :api_key
        LIMIT 1
    ");

    $stmt->execute([':api_key' => $api_key]);
    $row = $stmt->fetch();

} catch (PDOException $e) {
    error_log('[get-settings] DB error: ' . $e->getMessage());
    cors_apply_permissive();
    send_json(['error' => 'Internal server error.'], 500);
}

// ── 5. Pastikan klien ditemukan & aktif ──────────────────────
if (!$row) {
    cors_apply_permissive();
    send_json(['error' => 'API key not found.'], 404);
}

// ── 6. CORS — selalu izinkan domain app + daftar Allowed Origins klien ─
$allowed_for_cors = cors_ensure_app_site_in_list((string) ($row['allowed_origins'] ?? '*'));
if (!cors_apply_for_widget($allowed_for_cors) && !cors_is_same_server_request()) {
    send_json(['error' => cors_forbidden_message((string) ($row['allowed_origins'] ?? ''))], 403);
}

header('Cache-Control: public, max-age=300'); // Cache 5 menit di browser

// ── 7. Kembalikan pengaturan widget (tidak ekspos data sensitif) ─
$show_watermark = billing_should_show_watermark([
    'subscription_status' => $row['subscription_status'],
    'plan_code'           => $row['plan_code'] ?? 'trial',
]);

send_json([
    'bot_name'         => $row['bot_name'],
    'primary_color'    => $row['primary_color'],
    'bot_avatar_url'   => $row['bot_avatar_url'],
    'welcome_message'  => $row['welcome_message'],
    'show_watermark'     => $show_watermark,
    'watermark_brand'    => APP_NAME,
    'watermark_url'      => app_base_url(),
    'watermark_logo_url' => app_base_url() . '/assets/chatlm-logo.png',
]);
