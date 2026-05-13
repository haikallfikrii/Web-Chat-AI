-- ============================================================
-- Migrasi v3: dashboard auth MVP
-- ============================================================

USE chatpopup_db;

CREATE TABLE IF NOT EXISTS users (
    id               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    client_id        INT UNSIGNED    NOT NULL,
    name             VARCHAR(120)    NOT NULL,
    email            VARCHAR(255)    NOT NULL UNIQUE,
    password_hash    VARCHAR(255)    NOT NULL,
    role             ENUM('owner','admin') NOT NULL DEFAULT 'owner',
    is_active        TINYINT(1)      NOT NULL DEFAULT 1,
    last_login_at    DATETIME        NULL DEFAULT NULL,
    created_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_users_client (client_id),
    INDEX idx_users_email_active (email, is_active),
    CONSTRAINT fk_users_client FOREIGN KEY (client_id)
        REFERENCES clients (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contoh membuat user dashboard untuk client yang sudah ada:
-- INSERT INTO users (client_id, name, email, password_hash, role, is_active)
-- VALUES (
--   1,
--   'Owner Demo',
--   'owner@example.com',
--   '$2y$10$ISI_HASH_PASSWORD',
--   'owner',
--   1
-- );
