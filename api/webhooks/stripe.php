<?php
/**
 * Stripe Webhook endpoint
 * URL: https://yourdomain.com/api/webhooks/stripe.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/billing.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$payload = file_get_contents('php://input');
$sig     = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

if ($payload === false || $payload === '') {
    http_response_code(400);
    exit;
}

if (STRIPE_WEBHOOK_SECRET === '') {
    error_log('[stripe webhook] STRIPE_WEBHOOK_SECRET kosong');
    http_response_code(500);
    exit;
}

if (!stripe_verify_webhook($payload, $sig, STRIPE_WEBHOOK_SECRET)) {
    error_log('[stripe webhook] signature invalid');
    http_response_code(400);
    exit;
}

$event = json_decode($payload, true);
if (!is_array($event) || empty($event['id'])) {
    http_response_code(400);
    exit;
}

$event_id   = (string) $event['id'];
$event_type = (string) ($event['type'] ?? '');

if (billing_webhook_already_processed($event_id)) {
    http_response_code(200);
    echo json_encode(['received' => true, 'duplicate' => true]);
    exit;
}

try {
    billing_process_stripe_event($event);
    billing_webhook_mark_processed($event_id, $event_type);
} catch (Throwable $e) {
    error_log('[stripe webhook] ' . $e->getMessage());
    http_response_code(500);
    exit;
}

http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['received' => true]);
