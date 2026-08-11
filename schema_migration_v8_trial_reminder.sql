-- Trial reminder email tracking
-- Jalankan sekali di phpMyAdmin (database u451240370_chatlm_prod)

ALTER TABLE clients
    ADD COLUMN trial_reminder_sent TINYINT(1) NOT NULL DEFAULT 0 AFTER trial_ends_at;

-- Index untuk query cron
CREATE INDEX idx_clients_trial_reminder ON clients (subscription_status, trial_ends_at, trial_reminder_sent);
