<?php
declare(strict_types=1);

require_once __DIR__ . '/plans.php';

/** @var bool|null */
$GLOBALS['_chatlm_managed_ai_ready'] = null;

/**
 * Semua kolom schema v6 harus ada; jika tidak, pakai query legacy.
 */
function clients_managed_ai_ready(PDO $pdo): bool
{
    if ($GLOBALS['_chatlm_managed_ai_ready'] !== null) {
        return (bool) $GLOBALS['_chatlm_managed_ai_ready'];
    }

    $required = [
        'plan_type',
        'api_key_source',
        'message_quota_limit',
        'message_quota_used',
        'quota_reset_at',
        'remove_branding',
    ];

    $ready = true;
    foreach ($required as $column) {
        if (!db_table_has_column($pdo, 'clients', $column)) {
            $ready = false;
            break;
        }
    }

    $GLOBALS['_chatlm_managed_ai_ready'] = $ready;

    return $ready;
}

function clients_disable_managed_ai_mode(): void
{
    $GLOBALS['_chatlm_managed_ai_ready'] = false;
}

function db_table_has_column(PDO $pdo, string $table, string $column): bool
{
    static $cache = [];

    $table  = preg_replace('/[^a-z0-9_]/i', '', $table) ?: 'clients';
    $column = preg_replace('/[^a-z0-9_]/i', '', $column) ?: '';
    if ($column === '') {
        return false;
    }

    $key = $table . '.' . $column;
    if (!array_key_exists($key, $cache)) {
        try {
            $stmt = $pdo->prepare('SHOW COLUMNS FROM `' . $table . '` LIKE :col');
            $stmt->execute([':col' => $column]);
            $cache[$key] = (bool) $stmt->fetch();
        } catch (PDOException) {
            $cache[$key] = false;
        }
    }

    return $cache[$key];
}

function clients_managed_select_sql(string $alias = 'c'): string
{
    $a = preg_replace('/[^a-z0-9_]/i', '', $alias) ?: 'c';

    return $a . '.plan_type,
            ' . $a . '.api_key_source,
            ' . $a . '.message_quota_limit,
            ' . $a . '.message_quota_used,
            ' . $a . '.quota_reset_at,
            ' . $a . '.remove_branding,';
}

function clients_infer_plan_type_from_code(string $plan_code): string
{
    if ($plan_code === 'free' || $plan_code === '' || $plan_code === 'trial') {
        return 'free';
    }

    foreach (['managed_agency', 'managed_pro', 'managed_starter', 'byok_agency', 'byok_pro', 'byok_starter'] as $type) {
        if (str_starts_with($plan_code, $type)) {
            return $type;
        }
    }

    if (in_array($plan_code, ['starter_monthly', 'starter_yearly'], true)) {
        return 'byok_starter';
    }
    if (in_array($plan_code, ['pro_monthly', 'pro_yearly'], true)) {
        return 'byok_pro';
    }

    return 'free';
}

/**
 * @param array<string, mixed> $row
 * @return array<string, mixed>
 */
function clients_enrich_row(array $row, PDO $pdo): array
{
    if (clients_managed_ai_ready($pdo)) {
        return $row;
    }

    $planCode = (string) ($row['plan_code'] ?? 'free');
    $status   = (string) ($row['subscription_status'] ?? 'trial');

    $row['plan_type']           = clients_infer_plan_type_from_code($planCode);
    $row['api_key_source']      = str_starts_with((string) $row['plan_type'], 'managed_') ? 'system' : 'user';
    $row['message_quota_limit'] = 0;
    $row['message_quota_used']  = 0;
    $row['quota_reset_at']      = null;
    $row['remove_branding']     = 0;

    if ($status === 'active') {
        $plan = billing_plan($planCode);
        if ($plan !== null && !empty($plan['remove_branding'])) {
            $row['remove_branding'] = 1;
        } elseif (in_array($planCode, ['starter_monthly', 'starter_yearly', 'pro_monthly', 'pro_yearly'], true)) {
            $row['remove_branding'] = 1;
        }
    }

    return $row;
}

/**
 * Query klien + widget; otomatis fallback jika kolom v6 belum ada di DB.
 *
 * @return array<string, mixed>|null
 */
function clients_fetch_widget_row(PDO $pdo, string $api_key, string $selectTail): ?array
{
    $attempts = [
        clients_managed_ai_ready($pdo),
        false,
    ];

    $lastError = null;

    foreach ($attempts as $withManaged) {
        if (!$withManaged) {
            clients_disable_managed_ai_mode();
        }

        $managedCols = $withManaged ? clients_managed_select_sql() : '';

        $sql = "
            SELECT
                c.id AS client_id,
                c.subscription_status,
                c.plan_code,
                {$managedCols}
                {$selectTail}
            FROM clients c
            INNER JOIN widget_settings ws ON ws.client_id = c.id
            WHERE c.api_key = :api_key
            LIMIT 1
        ";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':api_key' => $api_key]);
            $row = $stmt->fetch();

            if (!is_array($row)) {
                return null;
            }

            return clients_enrich_row($row, $pdo);
        } catch (PDOException $e) {
            $lastError = $e;
            if ($withManaged && stripos($e->getMessage(), 'Unknown column') !== false) {
                clients_disable_managed_ai_mode();
                continue;
            }
            throw $e;
        }
    }

    if ($lastError instanceof PDOException) {
        throw $lastError;
    }

    return null;
}
