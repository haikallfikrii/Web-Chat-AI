-- ============================================================
-- SaaS Chat Pop-up - Database Schema (v2: multi-provider AI)
-- ============================================================

CREATE DATABASE IF NOT EXISTS chatpopup_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE chatpopup_db;

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
    subscription_ends_at DATETIME      NULL DEFAULT NULL,
    billing_email      VARCHAR(255)    NULL DEFAULT NULL,
    created_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_api_key (api_key),
    INDEX idx_subscription (subscription_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stripe_webhook_events (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    stripe_event_id VARCHAR(255)    NOT NULL,
    event_type      VARCHAR(120)    NOT NULL,
    processed_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_stripe_event (stripe_event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: widget_settings
-- ai_api_key: simpan ciphertext Base64 (AES-256-GCM via APP_SECRET), bukan plaintext
-- telegram_chat_id: opsional; notifikasi pakai TELEGRAM_BOT_TOKEN di config.php
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS widget_settings (
    id               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    client_id        INT UNSIGNED    NOT NULL UNIQUE,
    primary_color    CHAR(7)         NOT NULL DEFAULT '#4F46E5',
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
-- Tabel: chat_messages (history percakapan untuk konteks AI)
-- role: user | assistant (setara OpenAI; Gemini memetakan assistant -> model)
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
-- Login dashboard per client untuk mengatur widget sendiri
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
-- Token reset password (hash) untuk fitur "lupa password"
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
-- Contoh data dummy (isi ai_api_key dengan ciphertext dari encrypt_secret PHP)
-- ------------------------------------------------------------
INSERT INTO clients (name, email, api_key, subscription_status) VALUES
(
    'Toko ABC',
    'admin@toko-abc.com',
    SHA2(CONCAT('toko-abc', UUID(), RAND()), 256),
    'active'
);

INSERT INTO widget_settings (
    client_id,
    primary_color,
    bot_name,
    bot_avatar_url,
    welcome_message,
    n8n_webhook_url,
    allowed_origins,
    ai_provider,
    ai_api_key,
    ai_model,
    ai_system_prompt,
    telegram_notify_enabled,
    telegram_chat_id
)
VALUES (
    LAST_INSERT_ID(),
    '#4F46E5',
    'Asisten Toko ABC',
    '',
    'Halo! Ada yang bisa saya bantu hari ini?',
    'https://n8n.yourdomain.com/webhook/YOUR-WEBHOOK-ID',
    'https://toko-abc.com',
    'openrouter',
    '',
    'openai/gpt-4o-mini',
    '',
    0,
    NULL
);

-- ------------------------------------------------------------
-- Contoh user dashboard
-- Password contoh di bawah adalah placeholder hasil password_hash().
-- Buat hash baru dengan: php scripts/generate_password_hash.php "PasswordKuatAnda"
-- ------------------------------------------------------------
INSERT INTO users (
    client_id,
    name,
    email,
    password_hash,
    role,
    is_active
) VALUES (
    (SELECT id FROM clients WHERE email = 'admin@toko-abc.com' LIMIT 1),
    'Owner Toko ABC',
    'owner@toko-abc.com',
    '$2y$10$CHANGE_ME_WITH_PASSWORD_HASH',
    'owner',
    1
);
