-- ChatLM v6 — Managed AI, quotas, BYOK vs Managed routing
-- Jalankan sekali di phpMyAdmin (production + staging).

ALTER TABLE clients
    ADD COLUMN plan_type VARCHAR(40) NOT NULL DEFAULT 'free' AFTER plan_code,
    ADD COLUMN api_key_source ENUM('user', 'system') NOT NULL DEFAULT 'user' AFTER plan_type,
    ADD COLUMN message_quota_limit INT UNSIGNED NOT NULL DEFAULT 0 AFTER api_key_source,
    ADD COLUMN message_quota_used INT UNSIGNED NOT NULL DEFAULT 0 AFTER message_quota_limit,
    ADD COLUMN quota_reset_at DATE NULL AFTER message_quota_used,
    ADD COLUMN max_websites INT UNSIGNED NOT NULL DEFAULT 1 AFTER quota_reset_at,
    ADD COLUMN remove_branding TINYINT(1) NOT NULL DEFAULT 0 AFTER max_websites,
    ADD COLUMN whitelist_domains TEXT NULL AFTER remove_branding;

-- Backfill dari plan_code lama
UPDATE clients SET plan_type = 'free' WHERE plan_code = 'free' OR plan_code = '' OR plan_code = 'trial';
UPDATE clients SET plan_type = 'byok_starter', api_key_source = 'user', message_quota_limit = 0, remove_branding = 1
    WHERE plan_code IN ('starter_monthly', 'starter_yearly');
UPDATE clients SET plan_type = 'byok_pro', api_key_source = 'user', message_quota_limit = 0, remove_branding = 1
    WHERE plan_code IN ('pro_monthly', 'pro_yearly');

UPDATE clients SET quota_reset_at = DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
    WHERE message_quota_limit > 0 AND quota_reset_at IS NULL;
