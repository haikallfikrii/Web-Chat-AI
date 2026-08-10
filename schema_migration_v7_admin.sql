-- Admin dashboard: in-app notifications for new registrations & subscriptions
-- Jalankan sekali di staging & production (phpMyAdmin → SQL).

CREATE TABLE IF NOT EXISTS admin_notifications (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    event_type      ENUM('registration','subscription') NOT NULL,
    client_id       INT UNSIGNED    NULL DEFAULT NULL,
    title           VARCHAR(200)    NOT NULL,
    body            TEXT            NOT NULL,
    meta_json       JSON            NULL,
    is_read         TINYINT(1)      NOT NULL DEFAULT 0,
    email_sent      TINYINT(1)      NOT NULL DEFAULT 0,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_admin_notif_unread (is_read, created_at),
    INDEX idx_admin_notif_client (client_id),
    CONSTRAINT fk_admin_notif_client FOREIGN KEY (client_id)
        REFERENCES clients (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
