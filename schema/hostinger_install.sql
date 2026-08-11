-- ============================================================
-- ChatLM — Instalasi tabel (Hostinger / phpMyAdmin)
-- ============================================================
-- CARA PAKAI:
-- 1. Buat database baru di hPanel → Databases (mis. uXXX_chatlm_staging)
-- 2. Buat user MySQL & assign ke database tersebut
-- 3. Buka phpMyAdmin → PILIH database di sidebar kiri
-- 4. Tab SQL → tempel file ini → Execute
--
-- Ulangi untuk database KEDUA (production) dengan nama DB berbeda.
-- Jangan jalankan CREATE DATABASE di shared hosting jika tidak diizinkan.
-- ============================================================

-- ------------------------------------------------------------
-- Tabel: clients
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS clients (
    id               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name             VARCHAR(150)    NOT NULL,
    email            VARCHAR(255)    NOT NULL UNIQUE,
    api_key          CHAR(64)        NOT NULL UNIQUE,
    subscription_status ENUM('active','inactive','trial') NOT NULL DEFAULT 'trial',
    plan_code          VARCHAR(40)     NOT NULL DEFAULT 'free',
    stripe_customer_id VARCHAR(255)    NULL DEFAULT NULL,
    stripe_subscription_id VARCHAR(255) NULL DEFAULT NULL,
    trial_ends_at      DATETIME        NULL DEFAULT NULL,
    trial_reminder_sent TINYINT(1)     NOT NULL DEFAULT 0,
    subscription_ends_at DATETIME      NULL DEFAULT NULL,
    billing_email      VARCHAR(255)    NULL DEFAULT NULL,
    created_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_api_key (api_key),
    INDEX idx_subscription (subscription_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: widget_settings
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS widget_settings (
    id               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    client_id        INT UNSIGNED    NOT NULL UNIQUE,
    primary_color    CHAR(7)         NOT NULL DEFAULT '#14B8A6',
    bot_name         VARCHAR(80)     NOT NULL DEFAULT 'Assistant',
    bot_avatar_url   VARCHAR(500)    NOT NULL DEFAULT '',
    welcome_message  TEXT            NOT NULL,
    n8n_webhook_url  VARCHAR(500)    NOT NULL DEFAULT '',
    allowed_origins  TEXT            NOT NULL DEFAULT '*',
    ai_provider      ENUM('openai','google','deepseek','openrouter')
                         NOT NULL DEFAULT 'openrouter',
    ai_api_key       TEXT            NOT NULL,
    ai_model         VARCHAR(120)    NOT NULL DEFAULT 'openai/gpt-4o-mini',
    ai_system_prompt TEXT            NOT NULL DEFAULT '',
    telegram_notify_enabled TINYINT(1) NOT NULL DEFAULT 0,
    telegram_chat_id        VARCHAR(64)  NULL DEFAULT NULL,
    created_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_ws_client FOREIGN KEY (client_id)
        REFERENCES clients (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: chat_messages
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS chat_messages (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id        INT UNSIGNED    NOT NULL,
    session_id       CHAR(36)        NOT NULL,
    role             ENUM('user','assistant') NOT NULL,
    body             TEXT            NOT NULL,
    ip_address       VARCHAR(45)     NOT NULL DEFAULT '',
    created_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_sess       (client_id, session_id, id),
    INDEX idx_created    (created_at),
    CONSTRAINT fk_cm_client FOREIGN KEY (client_id)
        REFERENCES clients (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: users
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- Tabel: password_resets
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS password_resets (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED    NOT NULL,
    token_hash  CHAR(64)        NOT NULL,
    expires_at  DATETIME        NOT NULL,
    used_at     DATETIME        NULL DEFAULT NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_token_hash (token_hash),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at),
    CONSTRAINT fk_pwreset_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: stripe_webhook_events (billing)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS stripe_webhook_events (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    stripe_event_id VARCHAR(255)    NOT NULL,
    event_type      VARCHAR(120)    NOT NULL,
    processed_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_stripe_event (stripe_event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: admin_notifications (dashboard admin platform)
-- ------------------------------------------------------------
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
