<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/plans.php';
require_once __DIR__ . '/mail.php';

/** @var bool|null */
$GLOBALS['_chatlm_admin_notif_ready'] = null;

function admin_notifications_table_ready(PDO $pdo): bool
{
    if ($GLOBALS['_chatlm_admin_notif_ready'] !== null) {
        return (bool) $GLOBALS['_chatlm_admin_notif_ready'];
    }

    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'admin_notifications'");
        $GLOBALS['_chatlm_admin_notif_ready'] = (bool) $stmt->fetch();
    } catch (Throwable) {
        $GLOBALS['_chatlm_admin_notif_ready'] = false;
    }

    return (bool) $GLOBALS['_chatlm_admin_notif_ready'];
}

/** @return list<string> */
function platform_admin_emails(): array
{
    $raw = defined('PLATFORM_ADMIN_EMAILS') ? (string) PLATFORM_ADMIN_EMAILS : '';
    if ($raw === '') {
        return ['team@chatlm.tech'];
    }

    $parts = preg_split('/[\s,;]+/', strtolower($raw)) ?: [];
    $out   = [];
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p !== '' && filter_var($p, FILTER_VALIDATE_EMAIL)) {
            $out[] = $p;
        }
    }

    return $out !== [] ? array_values(array_unique($out)) : ['team@chatlm.tech'];
}

function platform_notify_email(): string
{
    $to = defined('PLATFORM_NOTIFY_EMAIL') ? trim((string) PLATFORM_NOTIFY_EMAIL) : '';
    if ($to !== '' && filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return $to;
    }

    return 'team@chatlm.tech';
}

function is_platform_admin(?array $user = null): bool
{
    $user = $user ?? current_user();
    if ($user === null) {
        return false;
    }

    $email = strtolower(trim((string) ($user['email'] ?? '')));
    if ($email === '') {
        return false;
    }

    return in_array($email, platform_admin_emails(), true);
}

function require_platform_admin(): array
{
    $user = require_login();
    if (!is_platform_admin($user)) {
        http_response_code(403);
        exit('Akses ditolak.');
    }

    return $user;
}

function admin_plan_label(string $plan_code): string
{
    $plan = billing_plan($plan_code);
    if ($plan !== null && !empty($plan['name'])) {
        return (string) $plan['name'];
    }

    return $plan_code !== '' ? $plan_code : '—';
}

/**
 * @return array<string, int|float>
 */
function admin_summary_stats(PDO $pdo): array
{
    $clients = $pdo->query(
        "SELECT
            COUNT(*) AS total,
            SUM(subscription_status = 'active') AS active_count,
            SUM(subscription_status = 'trial') AS trial_count,
            SUM(subscription_status = 'inactive') AS inactive_count
         FROM clients"
    )->fetch();

    $msgToday = (int) $pdo->query(
        "SELECT COUNT(*) FROM chat_messages WHERE role = 'user' AND created_at >= CURDATE()"
    )->fetchColumn();

    $msg7 = (int) $pdo->query(
        "SELECT COUNT(*) FROM chat_messages WHERE role = 'user' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"
    )->fetchColumn();

    $msg30 = (int) $pdo->query(
        "SELECT COUNT(*) FROM chat_messages WHERE role = 'user' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
    )->fetchColumn();

    $sessions30 = (int) $pdo->query(
        "SELECT COUNT(DISTINCT session_id) FROM chat_messages
         WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
    )->fetchColumn();

    $newClients7 = (int) $pdo->query(
        "SELECT COUNT(*) FROM clients WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
    )->fetchColumn();

    return [
        'clients_total'    => (int) ($clients['total'] ?? 0),
        'clients_active'   => (int) ($clients['active_count'] ?? 0),
        'clients_trial'    => (int) ($clients['trial_count'] ?? 0),
        'clients_inactive' => (int) ($clients['inactive_count'] ?? 0),
        'messages_today'   => $msgToday,
        'messages_7d'      => $msg7,
        'messages_30d'     => $msg30,
        'sessions_30d'     => $sessions30,
        'registrations_7d' => $newClients7,
    ];
}

/**
 * @return list<array<string, mixed>>
 */
function admin_clients_list(PDO $pdo, int $limit = 200): array
{
    $limit = max(1, min(500, $limit));

    $sql = "
        SELECT
            c.id,
            c.name,
            c.email,
            c.subscription_status,
            c.plan_code,
            c.trial_ends_at,
            c.subscription_ends_at,
            c.created_at,
            u.name AS owner_name,
            u.email AS owner_email,
            u.last_login_at,
            (SELECT COUNT(*) FROM chat_messages cm WHERE cm.client_id = c.id AND cm.role = 'user') AS user_messages,
            (SELECT COUNT(DISTINCT cm.session_id) FROM chat_messages cm WHERE cm.client_id = c.id) AS sessions
        FROM clients c
        LEFT JOIN users u ON u.client_id = c.id AND u.role = 'owner'
        ORDER BY c.created_at DESC
        LIMIT {$limit}
    ";

    return $pdo->query($sql)->fetchAll() ?: [];
}

/**
 * Pesan pengunjung per hari (14 hari terakhir).
 *
 * @return list<array{day: string, cnt: int}>
 */
function admin_messages_per_day(PDO $pdo, int $days = 14): array
{
    $days = max(7, min(90, $days));

    $stmt = $pdo->prepare(
        "SELECT DATE(created_at) AS day, COUNT(*) AS cnt
         FROM chat_messages
         WHERE role = 'user' AND created_at >= DATE_SUB(CURDATE(), INTERVAL :d DAY)
         GROUP BY DATE(created_at)
         ORDER BY day ASC"
    );
    $stmt->execute([':d' => $days - 1]);
    $rows = $stmt->fetchAll() ?: [];

    $map = [];
    foreach ($rows as $row) {
        $map[(string) $row['day']] = (int) $row['cnt'];
    }

    $out = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $day = (new DateTime("-{$i} days"))->format('Y-m-d');
        $out[] = ['day' => $day, 'cnt' => $map[$day] ?? 0];
    }

    return $out;
}

function admin_unread_notification_count(PDO $pdo): int
{
    if (!admin_notifications_table_ready($pdo)) {
        return 0;
    }

    return (int) $pdo->query(
        'SELECT COUNT(*) FROM admin_notifications WHERE is_read = 0'
    )->fetchColumn();
}

/**
 * @return list<array<string, mixed>>
 */
function admin_fetch_notifications(PDO $pdo, int $limit = 50): array
{
    if (!admin_notifications_table_ready($pdo)) {
        return [];
    }

    $limit = max(1, min(200, $limit));
    $stmt  = $pdo->query(
        "SELECT id, event_type, client_id, title, body, is_read, created_at
         FROM admin_notifications
         ORDER BY created_at DESC
         LIMIT {$limit}"
    );

    return $stmt->fetchAll() ?: [];
}

function admin_mark_notifications_read(PDO $pdo, ?array $ids = null): void
{
    if (!admin_notifications_table_ready($pdo)) {
        return;
    }

    if ($ids === null || $ids === []) {
        $pdo->exec('UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0');
        return;
    }

    $ids = array_values(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0));
    if ($ids === []) {
        return;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt         = $pdo->prepare("UPDATE admin_notifications SET is_read = 1 WHERE id IN ({$placeholders})");
    $stmt->execute($ids);
}

function send_platform_admin_email(string $subject, string $body_html, string $preheader = ''): bool
{
    $to   = platform_notify_email();
    $html = mail_html_layout($subject, $body_html, $preheader, 'en');
    $plain = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], ["\n", "\n", "\n", "\n\n"], $body_html));

    return send_html_email($to, $subject, $html, $plain);
}

/**
 * Catat notifikasi dashboard + email ke tim.
 *
 * @param array<string, mixed> $meta
 */
function admin_notify_event(
    string $event_type,
    int $client_id,
    string $title,
    string $body,
    array $meta = []
): void {
    $pdo = get_db();

    $notif_id = 0;
    if (admin_notifications_table_ready($pdo)) {
        try {
            $pdo->prepare(
                'INSERT INTO admin_notifications (event_type, client_id, title, body, meta_json)
                 VALUES (:type, :cid, :title, :body, :meta)'
            )->execute([
                ':type'  => $event_type,
                ':cid'   => $client_id > 0 ? $client_id : null,
                ':title' => mb_substr($title, 0, 200, 'UTF-8'),
                ':body'  => $body,
                ':meta'  => $meta === [] ? null : json_encode($meta, JSON_UNESCAPED_UNICODE),
            ]);
            $notif_id = (int) $pdo->lastInsertId();
        } catch (Throwable $e) {
            error_log('[admin_notify] insert failed: ' . $e->getMessage());
        }
    }

    $adminUrl = app_site_url() . '/admin.php';
    $metaRows = '';
    foreach ($meta as $k => $v) {
        if (!is_scalar($v) || (string) $v === '') {
            continue;
        }
        $metaRows .= '<tr><td style="color:#94a3b8;padding:4px 0;">'
            . htmlspecialchars((string) $k, ENT_QUOTES, 'UTF-8')
            . '</td><td align="right" style="color:#f8fafc;">'
            . htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8') . '</td></tr>';
    }

    $emailBody = '<p style="margin:0 0 12px;color:#cbd5e1;">' . nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8')) . '</p>';
    if ($metaRows !== '') {
        $emailBody .= mail_info_box('<table role="presentation" width="100%" style="font-size:14px;">' . $metaRows . '</table>');
    }
    $emailBody .= mail_button($adminUrl, 'Buka Admin Dashboard');

    $envTag = APP_ENV === 'staging' ? ' [Staging]' : '';
    $subject = APP_NAME . $envTag . ' — ' . $title;

    $sent = send_platform_admin_email($subject, $emailBody, $title);

    if ($sent && $notif_id > 0) {
        try {
            $pdo->prepare('UPDATE admin_notifications SET email_sent = 1 WHERE id = :id')
                ->execute([':id' => $notif_id]);
        } catch (Throwable) {
            // non-fatal
        }
    }
}

function admin_notify_new_registration(
    int $client_id,
    string $business_name,
    string $owner_name,
    string $owner_email
): void {
    $title = 'Klien baru mendaftar';
    $body  = sprintf(
        "%s (%s) baru mendaftar.\nKontak: %s — %s",
        $business_name,
        $owner_name,
        $owner_email,
        admin_plan_label('free')
    );

    admin_notify_event('registration', $client_id, $title, $body, [
        'Bisnis'    => $business_name,
        'Owner'     => $owner_name,
        'Email'     => $owner_email,
        'Client ID' => (string) $client_id,
        'Paket'     => 'Trial / Free',
    ]);
}

function admin_notify_new_subscription(
    int $client_id,
    string $business_name,
    string $plan_code,
    string $client_email
): void {
    $planLabel = admin_plan_label($plan_code);
    $title     = 'Langganan baru';
    $body      = sprintf(
        "%s mengaktifkan paket %s.\nEmail billing: %s",
        $business_name,
        $planLabel,
        $client_email
    );

    admin_notify_event('subscription', $client_id, $title, $body, [
        'Bisnis'    => $business_name,
        'Paket'     => $planLabel,
        'Plan code' => $plan_code,
        'Email'     => $client_email,
        'Client ID' => (string) $client_id,
    ]);
}

// ─────────────────────────────────────────────────────────────────────────────
// CLIENT DETAIL & ACTIONS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Ambil detail lengkap klien untuk halaman admin.
 *
 * @return array<string, mixed>|null
 */
function admin_get_client(PDO $pdo, int $client_id): ?array
{
    $stmt = $pdo->prepare("
        SELECT
            c.*,
            u.id AS owner_user_id,
            u.name AS owner_name,
            u.email AS owner_email,
            u.last_login_at,
            ws.primary_color,
            ws.bot_name,
            ws.ai_provider,
            ws.ai_model,
            ws.telegram_notify_enabled,
            (SELECT COUNT(*) FROM chat_messages cm WHERE cm.client_id = c.id AND cm.role = 'user') AS total_messages,
            (SELECT COUNT(DISTINCT cm.session_id) FROM chat_messages cm WHERE cm.client_id = c.id) AS total_sessions
        FROM clients c
        LEFT JOIN users u ON u.client_id = c.id AND u.role = 'owner'
        LEFT JOIN widget_settings ws ON ws.client_id = c.id
        WHERE c.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $client_id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/**
 * Ubah paket langganan klien secara manual (admin action).
 */
function admin_change_client_plan(PDO $pdo, int $client_id, string $new_plan_code, string $new_status = 'active'): bool
{
    $plan = billing_plan($new_plan_code);
    if ($plan === null) {
        return false;
    }

    $valid_statuses = ['active', 'trial', 'inactive'];
    if (!in_array($new_status, $valid_statuses, true)) {
        $new_status = 'active';
    }

    try {
        $pdo->prepare("
            UPDATE clients SET
                plan_code = :plan,
                subscription_status = :status,
                updated_at = NOW()
            WHERE id = :id
        ")->execute([
            ':plan'   => $new_plan_code,
            ':status' => $new_status,
            ':id'     => $client_id,
        ]);

        require_once __DIR__ . '/managed_ai.php';
        billing_sync_client_plan_columns($pdo, $client_id, $new_plan_code);

        return true;
    } catch (Throwable $e) {
        error_log('[admin] change plan error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Perpanjang trial klien (tambah X hari dari sekarang atau dari trial_ends_at).
 */
function admin_extend_trial(PDO $pdo, int $client_id, int $days): bool
{
    if ($days < 1 || $days > 365) {
        return false;
    }

    try {
        $pdo->prepare("
            UPDATE clients SET
                trial_ends_at = DATE_ADD(COALESCE(trial_ends_at, NOW()), INTERVAL :days DAY),
                subscription_status = 'trial',
                trial_reminder_sent = 0,
                updated_at = NOW()
            WHERE id = :id
        ")->execute([
            ':days' => $days,
            ':id'   => $client_id,
        ]);

        return true;
    } catch (Throwable $e) {
        error_log('[admin] extend trial error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Toggle status aktif/nonaktif klien.
 */
function admin_toggle_client_status(PDO $pdo, int $client_id, string $new_status): bool
{
    $valid = ['active', 'inactive', 'trial'];
    if (!in_array($new_status, $valid, true)) {
        return false;
    }

    try {
        $pdo->prepare("
            UPDATE clients SET
                subscription_status = :status,
                updated_at = NOW()
            WHERE id = :id
        ")->execute([
            ':status' => $new_status,
            ':id'     => $client_id,
        ]);

        return true;
    } catch (Throwable $e) {
        error_log('[admin] toggle status error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Reset password user klien (generate random password dan kirim email).
 */
function admin_reset_client_password(PDO $pdo, int $user_id): ?string
{
    $new_password = bin2hex(random_bytes(8));
    $hash = password_hash($new_password, PASSWORD_DEFAULT);

    try {
        $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id")
            ->execute([':hash' => $hash, ':id' => $user_id]);

        return $new_password;
    } catch (Throwable $e) {
        error_log('[admin] reset password error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Impersonate: login sebagai klien (untuk debugging).
 * Returns session data untuk di-set.
 *
 * @return array<string, mixed>|null
 */
function admin_impersonate_client(PDO $pdo, int $client_id): ?array
{
    $stmt = $pdo->prepare("
        SELECT
            u.id AS user_id,
            u.name,
            u.email,
            u.role,
            c.id AS client_id,
            c.name AS client_name,
            c.api_key AS client_api_key,
            c.subscription_status,
            c.plan_code
        FROM users u
        JOIN clients c ON c.id = u.client_id
        WHERE c.id = :cid AND u.role = 'owner'
        LIMIT 1
    ");
    $stmt->execute([':cid' => $client_id]);
    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }

    return [
        'id'                  => (int) $row['user_id'],
        'client_id'           => (int) $row['client_id'],
        'name'                => (string) $row['name'],
        'email'               => (string) $row['email'],
        'role'                => (string) $row['role'],
        'client_name'         => (string) $row['client_name'],
        'client_api_key'      => (string) $row['client_api_key'],
        'subscription_status' => (string) $row['subscription_status'],
        'plan_code'           => (string) $row['plan_code'],
    ];
}

/**
 * Ambil log aktivitas klien (chat messages terbaru).
 *
 * @return list<array<string, mixed>>
 */
function admin_client_activity_log(PDO $pdo, int $client_id, int $limit = 50): array
{
    $stmt = $pdo->prepare("
        SELECT session_id, role, content, created_at
        FROM chat_messages
        WHERE client_id = :cid
        ORDER BY created_at DESC
        LIMIT :lim
    ");
    $stmt->bindValue(':cid', $client_id, PDO::PARAM_INT);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll() ?: [];
}

// ─────────────────────────────────────────────────────────────────────────────
// ANALYTICS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Hitung MRR (Monthly Recurring Revenue) dari langganan aktif.
 */
function admin_calculate_mrr(PDO $pdo): float
{
    $stmt = $pdo->query("
        SELECT plan_code FROM clients
        WHERE subscription_status = 'active' AND plan_code IS NOT NULL AND plan_code != 'free'
    ");
    $rows = $stmt->fetchAll() ?: [];

    $mrr = 0.0;
    foreach ($rows as $row) {
        $plan = billing_plan($row['plan_code']);
        if ($plan === null) {
            continue;
        }

        $price = (float) ($plan['price_usd'] ?? 0);
        $interval = $plan['interval'] ?? 'month';

        if ($interval === 'year') {
            $mrr += $price / 12;
        } else {
            $mrr += $price;
        }
    }

    return round($mrr, 2);
}

/**
 * Hitung conversion rate: trial → paid (30 hari terakhir).
 */
function admin_conversion_rate(PDO $pdo): array
{
    $trials = (int) $pdo->query("
        SELECT COUNT(*) FROM clients
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY)
    ")->fetchColumn();

    $converted = (int) $pdo->query("
        SELECT COUNT(*) FROM clients
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY)
          AND subscription_status = 'active'
          AND plan_code != 'free'
    ")->fetchColumn();

    $rate = $trials > 0 ? round(($converted / $trials) * 100, 1) : 0;

    return [
        'trials'    => $trials,
        'converted' => $converted,
        'rate'      => $rate,
    ];
}

/**
 * Top klien berdasarkan jumlah pesan.
 *
 * @return list<array<string, mixed>>
 */
function admin_top_clients_by_usage(PDO $pdo, int $limit = 10): array
{
    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.name,
            c.plan_code,
            c.subscription_status,
            COUNT(cm.id) AS message_count
        FROM clients c
        LEFT JOIN chat_messages cm ON cm.client_id = c.id AND cm.role = 'user'
        GROUP BY c.id
        ORDER BY message_count DESC
        LIMIT :lim
    ");
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll() ?: [];
}

/**
 * Daftar paket untuk dropdown admin.
 *
 * @return array<string, string>
 */
function admin_plan_options(): array
{
    $plans = billing_plan_definitions();
    $out = [];

    foreach ($plans as $code => $plan) {
        if (!empty($plan['hidden']) || !empty($plan['legacy_alias'])) {
            continue;
        }

        $name = $plan['name'] ?? $code;
        $track = $plan['track'] ?? '';
        $interval = $plan['interval'] ?? '';
        $price = $plan['price_display'] ?? '';

        $label = $name;
        if ($track !== '') {
            $label .= ' (' . strtoupper($track) . ')';
        }
        if ($interval !== '') {
            $label .= ' - ' . ($interval === 'year' ? 'Yearly' : 'Monthly');
        }
        if ($price !== '') {
            $label .= ' ' . $price;
        }

        $out[$code] = $label;
    }

    return $out;
}
