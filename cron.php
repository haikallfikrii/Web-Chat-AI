<?php
declare(strict_types=1);

/**
 * Cron job untuk email otomatis:
 * - Trial expiring reminder (3 hari sebelum habis)
 *
 * Jalankan setiap hari via cron (Hostinger hPanel → Cron Jobs):
 *   /usr/bin/php /home/u451240370/domains/chatlm.tech/public_html/cron.php
 *
 * Atau via URL (dengan secret key):
 *   https://chatlm.tech/cron.php?key=YOUR_CRON_SECRET
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/mail.php';

$cron_secret = config_env('CRON_SECRET', '');

if (php_sapi_name() !== 'cli') {
    $key = $_GET['key'] ?? '';
    if ($cron_secret === '' || $key !== $cron_secret) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

$pdo = get_db();
$now = new DateTime();
$results = [
    'trial_expiring_sent' => 0,
    'errors' => [],
];

// Check if trial_reminder_sent column exists
$hasReminderCol = false;
try {
    $pdo->query("SELECT trial_reminder_sent FROM clients LIMIT 1");
    $hasReminderCol = true;
} catch (Throwable) {
    $results['errors'][] = 'Column trial_reminder_sent not found. Run migration schema_migration_v8_trial_reminder.sql';
}

$clients = [];
if ($hasReminderCol) {
    $stmt = $pdo->prepare("
        SELECT c.id, c.name, c.email, c.trial_ends_at, c.trial_reminder_sent,
               u.name AS owner_name
        FROM clients c
        LEFT JOIN users u ON u.client_id = c.id AND u.role = 'owner'
        WHERE c.subscription_status = 'trial'
          AND c.trial_ends_at IS NOT NULL
          AND c.trial_reminder_sent = 0
          AND c.trial_ends_at BETWEEN :now AND :limit
        ORDER BY c.trial_ends_at ASC
        LIMIT 100
    ");

    $limit_date = (clone $now)->modify('+4 days')->format('Y-m-d 23:59:59');
    $stmt->execute([
        ':now'   => $now->format('Y-m-d H:i:s'),
        ':limit' => $limit_date,
    ]);

    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

foreach ($clients as $client) {
    try {
        $trial_ends = new DateTime($client['trial_ends_at']);
        $days_left = max(1, (int) $now->diff($trial_ends)->days);

        $client_name = $client['owner_name'] ?: $client['name'];
        $email = $client['email'];

        $sent = send_trial_expiring_email($email, $client_name, $days_left, 'en');

        if ($sent) {
            $pdo->prepare('UPDATE clients SET trial_reminder_sent = 1 WHERE id = :id')
                ->execute([':id' => $client['id']]);
            $results['trial_expiring_sent']++;
        } else {
            $results['errors'][] = "Failed to send email to {$email}";
        }
    } catch (Throwable $e) {
        $results['errors'][] = "Error for client #{$client['id']}: " . $e->getMessage();
        error_log('[cron] trial reminder error: ' . $e->getMessage());
    }
}

header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT);
