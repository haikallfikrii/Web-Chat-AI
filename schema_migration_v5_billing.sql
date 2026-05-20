-- Billing & Stripe (v5)
-- Jalankan sekali di phpMyAdmin / mysql CLI setelah backup.

ALTER TABLE clients
    ADD COLUMN plan_code VARCHAR(40) NOT NULL DEFAULT 'trial' AFTER subscription_status,
    ADD COLUMN stripe_customer_id VARCHAR(255) NULL DEFAULT NULL AFTER plan_code,
    ADD COLUMN stripe_subscription_id VARCHAR(255) NULL DEFAULT NULL AFTER stripe_customer_id,
    ADD COLUMN trial_ends_at DATETIME NULL DEFAULT NULL AFTER stripe_subscription_id,
    ADD COLUMN subscription_ends_at DATETIME NULL DEFAULT NULL AFTER trial_ends_at,
    ADD COLUMN billing_email VARCHAR(255) NULL DEFAULT NULL AFTER subscription_ends_at;

CREATE TABLE IF NOT EXISTS stripe_webhook_events (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    stripe_event_id VARCHAR(255)    NOT NULL,
    event_type      VARCHAR(120)    NOT NULL,
    processed_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_stripe_event (stripe_event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trial 14 hari untuk akun yang belum punya tanggal
UPDATE clients
SET trial_ends_at = DATE_ADD(created_at, INTERVAL 14 DAY)
WHERE trial_ends_at IS NULL AND subscription_status IN ('trial', 'inactive');
