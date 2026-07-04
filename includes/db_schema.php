<?php
declare(strict_types=1);

require_once __DIR__ . '/plans.php';

/**
 * Deteksi kolom Managed AI (schema v6) — aman jika migrasi belum dijalankan di server.
 */
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
        $stmt = $pdo->prepare('SHOW COLUMNS FROM `' . $table . '` LIKE :col');
        $stmt->execute([':col' => $column]);
        $cache[$key] = (bool) $stmt->fetch();
    }

    return $cache[$key];
}

function clients_managed_ai_ready(PDO $pdo): bool
{
    static $ready = null;
    if ($ready === null) {
        $ready = db_table_has_column($pdo, 'clients', 'plan_type');
    }

    return $ready;
}

function clients_select_managed_columns_sql(PDO $pdo, string $alias = 'c'): string
{
    if (!clients_managed_ai_ready($pdo)) {
        return '';
    }

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
