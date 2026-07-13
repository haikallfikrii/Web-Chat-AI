<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/plans.php';
require_once __DIR__ . '/db_schema.php';
require_once __DIR__ . '/managed_ai.php';
require_once __DIR__ . '/stripe_client.php';
require_once __DIR__ . '/mail.php';
require_once __DIR__ . '/lang.php';

/** Normalize any string to a supported UI language, defaulting to English. */
function billing_normalize_lang(string $lang): string
{
    $allowed = ['en', 'id', 'es', 'fr', 'pt', 'ja'];
    $lang = strtolower(trim($lang));
    return in_array($lang, $allowed, true) ? $lang : 'en';
}

/**
 * @return array<string, mixed>|null
 */
function billing_fetch_client(int $client_id): ?array
{
    $pdo   = get_db();
    $cols  = [
        'id', 'name', 'email', 'subscription_status', 'plan_code',
    ];
    if (clients_managed_ai_ready($pdo)) {
        $cols = array_merge($cols, [
            'plan_type', 'api_key_source', 'message_quota_limit', 'message_quota_used',
            'quota_reset_at', 'max_websites', 'remove_branding', 'whitelist_domains',
        ]);
    }
    $cols = array_merge($cols, [
        'stripe_customer_id', 'stripe_subscription_id',
        'trial_ends_at', 'subscription_ends_at', 'billing_email',
    ]);

    $stmt = $pdo->prepare(
        'SELECT ' . implode(', ', $cols) . ' FROM clients WHERE id = :id LIMIT 1'
    );
    $stmt->execute([':id' => $client_id]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }

    return clients_enrich_row($row, $pdo);
}

function billing_should_show_watermark(array $client): bool
{
    if ((int) ($client['remove_branding'] ?? 0) === 1) {
        return false;
    }

    $status = (string) ($client['subscription_status'] ?? 'trial');
    if ($status === 'active') {
        $plan = billing_plan((string) ($client['plan_code'] ?? ''));
        if ($plan !== null && empty($plan['show_watermark'])) {
            return false;
        }

        return false;
    }

    return true;
}

function billing_refresh_session(int $client_id): void
{
    if (!isset($_SESSION['auth_user']) || !is_array($_SESSION['auth_user'])) {
        return;
    }
    if ((int) ($_SESSION['auth_user']['client_id'] ?? 0) !== $client_id) {
        return;
    }
    $c = billing_fetch_client($client_id);
    if ($c === null) {
        return;
    }
    $_SESSION['auth_user']['subscription_status'] = (string) $c['subscription_status'];
    $_SESSION['auth_user']['plan_code']             = (string) ($c['plan_code'] ?? 'trial');
}

/**
 * Aktifkan paket gratis (trial + watermark).
 */
function billing_activate_free_plan(int $client_id): bool
{
    $pdo = get_db();
    $ends = (new DateTime('+' . TRIAL_DAYS . ' days'))->format('Y-m-d H:i:s');

    $pdo->prepare(
        'UPDATE clients SET
            subscription_status = :status,
            plan_code = :plan,
            trial_ends_at = :trial_ends,
            stripe_subscription_id = NULL,
            subscription_ends_at = NULL
         WHERE id = :id'
    )->execute([
        ':status'     => 'trial',
        ':plan'       => 'free',
        ':trial_ends' => $ends,
        ':id'         => $client_id,
    ]);

    billing_sync_client_plan_columns($pdo, $client_id, 'free');

    billing_refresh_session($client_id);
    return true;
}

/**
 * Setelah pembayaran Stripe sukses.
 */
function billing_activate_paid_plan(
    int $client_id,
    string $plan_code,
    ?string $stripe_customer_id,
    ?string $stripe_subscription_id,
    ?string $period_end_iso
): void {
    $plan = billing_plan($plan_code);
    if ($plan === null) {
        return;
    }

    $ends = null;
    if ($period_end_iso !== null && $period_end_iso !== '') {
        try {
            $dt = new DateTime('@' . (int) strtotime($period_end_iso));
            $ends = $dt->format('Y-m-d H:i:s');
        } catch (Throwable) {
            $ends = null;
        }
    }

    $pdo = get_db();
    $pdo->prepare(
        'UPDATE clients SET
            subscription_status = :status,
            plan_code = :plan,
            stripe_customer_id = COALESCE(:cust, stripe_customer_id),
            stripe_subscription_id = :sub_id,
            subscription_ends_at = :sub_ends,
            trial_ends_at = NULL
         WHERE id = :id'
    )->execute([
        ':status'   => 'active',
        ':plan'     => $plan_code,
        ':cust'     => $stripe_customer_id,
        ':sub_id'   => $stripe_subscription_id,
        ':sub_ends' => $ends,
        ':id'       => $client_id,
    ]);

    billing_sync_client_plan_columns($pdo, $client_id, $plan_code);

    billing_refresh_session($client_id);
}

function billing_deactivate_subscription(int $client_id, bool $immediate = false): void
{
    $pdo = get_db();
    if ($immediate) {
        $pdo->prepare(
            'UPDATE clients SET subscription_status = :s, stripe_subscription_id = NULL WHERE id = :id'
        )->execute([':s' => 'inactive', ':id' => $client_id]);
    } else {
        $pdo->prepare(
            'UPDATE clients SET subscription_status = :s WHERE id = :id'
        )->execute([':s' => 'inactive', ':id' => $client_id]);
    }
    billing_refresh_session($client_id);
}

function billing_mark_cancel_at_period_end(int $client_id, ?string $period_end_iso): void
{
    $ends = null;
    if ($period_end_iso !== null) {
        $ts = is_numeric($period_end_iso) ? (int) $period_end_iso : strtotime($period_end_iso);
        if ($ts > 0) {
            $ends = (new DateTime('@' . $ts))->format('Y-m-d H:i:s');
        }
    }
    $pdo = get_db();
    $pdo->prepare(
        'UPDATE clients SET subscription_ends_at = :ends WHERE id = :id'
    )->execute([':ends' => $ends, ':id' => $client_id]);
    billing_refresh_session($client_id);
}

function billing_ensure_stripe_customer(int $client_id, string $email, string $name): ?string
{
    $client = billing_fetch_client($client_id);
    if ($client === null) {
        return null;
    }
    if (!empty($client['stripe_customer_id'])) {
        return (string) $client['stripe_customer_id'];
    }

    $created = stripe_create_customer($email, $name, [
        'client_id' => (string) $client_id,
    ]);
    if ($created === null || empty($created['id'])) {
        return null;
    }

    $cid = (string) $created['id'];
    $pdo = get_db();
    $pdo->prepare(
        'UPDATE clients SET stripe_customer_id = :cid, billing_email = :email WHERE id = :id'
    )->execute([':cid' => $cid, ':email' => $email, ':id' => $client_id]);

    return $cid;
}

/**
 * @return array{ok: bool, url?: string, error?: string}
 */
function billing_create_checkout(int $client_id, string $plan_code, string $user_email): array
{
    $plan = billing_plan($plan_code);
    if ($plan === null) {
        return ['ok' => false, 'error' => 'Paket tidak ditemukan.'];
    }

    if (($plan['price_usd'] ?? 0) <= 0) {
        billing_activate_free_plan($client_id);
        return ['ok' => true, 'url' => app_site_url() . '/billing-success.php?plan=free'];
    }

    $price_id = trim((string) ($plan['stripe_price_id'] ?? ''));
    if ($price_id === '') {
        return ['ok' => false, 'error' => 'Stripe Price ID belum dikonfigurasi untuk paket ini.'];
    }

    if (!stripe_configured()) {
        return ['ok' => false, 'error' => 'Stripe belum dikonfigurasi di server.'];
    }

    $client = billing_fetch_client($client_id);
    if ($client === null) {
        return ['ok' => false, 'error' => 'Akun tidak ditemukan.'];
    }

    $customer_id = billing_ensure_stripe_customer(
        $client_id,
        $user_email,
        (string) $client['name']
    );
    if ($customer_id === null) {
        return ['ok' => false, 'error' => 'Gagal membuat pelanggan Stripe.'];
    }

    $checkout_lang = billing_normalize_lang(get_lang());
    $base = app_site_url();
    $session = stripe_create_checkout_session([
        'mode'                => 'subscription',
        'customer'            => $customer_id,
        'client_reference_id' => (string) $client_id,
        'line_items'          => [
            ['price' => $price_id, 'quantity' => 1],
        ],
        'success_url'         => $base . '/billing-success.php?session_id={CHECKOUT_SESSION_ID}&lang=' . $checkout_lang,
        'cancel_url'          => $base . '/billing-cancel.php?plan=' . rawurlencode($plan_code) . '&lang=' . $checkout_lang,
        'metadata'            => [
            'client_id' => (string) $client_id,
            'plan_code' => $plan_code,
            'lang'      => $checkout_lang,
        ],
        'subscription_data'   => [
            'metadata' => [
                'client_id' => (string) $client_id,
                'plan_code' => $plan_code,
                'lang'      => $checkout_lang,
            ],
        ],
        'allow_promotion_codes' => true,
        'billing_address_collection' => 'auto',
    ]);

    if ($session === null || empty($session['url'])) {
        return ['ok' => false, 'error' => 'Gagal membuat sesi checkout Stripe.'];
    }

    return ['ok' => true, 'url' => (string) $session['url']];
}

function billing_webhook_already_processed(string $event_id): bool
{
    $pdo  = get_db();
    $stmt = $pdo->prepare('SELECT id FROM stripe_webhook_events WHERE stripe_event_id = :eid LIMIT 1');
    $stmt->execute([':eid' => $event_id]);
    return (bool) $stmt->fetch();
}

function billing_webhook_mark_processed(string $event_id, string $event_type): void
{
    $pdo = get_db();
    try {
        $pdo->prepare(
            'INSERT INTO stripe_webhook_events (stripe_event_id, event_type) VALUES (:eid, :type)'
        )->execute([':eid' => $event_id, ':type' => $event_type]);
    } catch (PDOException $e) {
        // duplicate OK
    }
}

function billing_handle_checkout_completed(array $session): void
{
    $client_id = (int) ($session['metadata']['client_id'] ?? $session['client_reference_id'] ?? 0);
    $plan_code = (string) ($session['metadata']['plan_code'] ?? '');
    if ($client_id <= 0 || $plan_code === '') {
        return;
    }

    $customer_id = (string) ($session['customer'] ?? '');
    $sub_id      = (string) ($session['subscription'] ?? '');

    billing_activate_paid_plan($client_id, $plan_code, $customer_id, $sub_id, null);

    $client = billing_fetch_client($client_id);
    if ($client === null) {
        return;
    }

    $plan = billing_plan($plan_code);
    $to   = (string) ($client['billing_email'] ?: $client['email']);
    $name = (string) $client['name'];
    $interval = billing_interval_label($plan['interval'] ?? null);
    $mail_lang = billing_normalize_lang((string) ($session['metadata']['lang'] ?? 'en'));

    send_checkout_receipt_email(
        $to,
        $name,
        (string) ($plan['name'] ?? $plan_code),
        (string) ($plan['price_display'] ?? '') . $interval,
        $mail_lang
    );
    send_subscription_activated_email(
        $to,
        $name,
        (string) ($plan['name'] ?? $plan_code),
        trim($interval, '/'),
        $mail_lang
    );
}

function billing_handle_subscription_updated(array $sub): void
{
    $client_id = (int) ($sub['metadata']['client_id'] ?? 0);
    if ($client_id <= 0) {
        return;
    }

    $status = (string) ($sub['status'] ?? '');
    $plan_code = (string) ($sub['metadata']['plan_code'] ?? '');
    $period_end = isset($sub['current_period_end']) ? (string) $sub['current_period_end'] : null;

    if (in_array($status, ['active', 'trialing'], true)) {
        if ($plan_code !== '') {
            billing_activate_paid_plan(
                $client_id,
                $plan_code,
                (string) ($sub['customer'] ?? ''),
                (string) ($sub['id'] ?? ''),
                $period_end
            );
        }
        return;
    }

    if (in_array($status, ['canceled', 'unpaid', 'past_due'], true)) {
        if (!empty($sub['cancel_at_period_end'])) {
            billing_mark_cancel_at_period_end($client_id, $period_end);
        }
        if ($status === 'canceled' || $status === 'unpaid') {
            billing_deactivate_subscription($client_id, $status === 'canceled');
        }
    }
}

function billing_handle_subscription_deleted(array $sub): void
{
    $client_id = (int) ($sub['metadata']['client_id'] ?? 0);
    if ($client_id <= 0) {
        return;
    }

    billing_deactivate_subscription($client_id, true);

    $client = billing_fetch_client($client_id);
    if ($client === null) {
        return;
    }

    $ends = $client['subscription_ends_at'] ?? null;
    $ends_human = $ends ? (new DateTime((string) $ends))->format('d M Y H:i') : null;
    $mail_lang = billing_normalize_lang((string) ($sub['metadata']['lang'] ?? 'en'));

    send_subscription_cancelled_email(
        (string) ($client['billing_email'] ?: $client['email']),
        (string) $client['name'],
        $ends_human,
        $mail_lang
    );
}

function billing_process_stripe_event(array $event): void
{
    $type = (string) ($event['type'] ?? '');
    $obj  = $event['data']['object'] ?? null;
    if (!is_array($obj)) {
        return;
    }

    match ($type) {
        'checkout.session.completed' => billing_handle_checkout_completed($obj),
        'customer.subscription.updated' => billing_handle_subscription_updated($obj),
        'customer.subscription.deleted' => billing_handle_subscription_deleted($obj),
        default => null,
    };
}

function billing_trial_days_left(?string $trial_ends_at): ?int
{
    if ($trial_ends_at === null || $trial_ends_at === '') {
        return null;
    }
    try {
        $end = new DateTime($trial_ends_at);
        $now = new DateTime();
        if ($end <= $now) {
            return 0;
        }
        return (int) $now->diff($end)->days;
    } catch (Throwable) {
        return null;
    }
}
