-- ============================================================
-- Migrasi v1 -> v2 (jalankan jika DB sudah ada dari instalasi lama)
-- ============================================================

USE chatpopup_db;

-- Kolom baru widget_settings
ALTER TABLE widget_settings
    MODIFY COLUMN n8n_webhook_url VARCHAR(500) NOT NULL DEFAULT '';

ALTER TABLE widget_settings
    ADD COLUMN ai_provider ENUM('openai','google','deepseek','openrouter')
        NOT NULL DEFAULT 'openrouter' AFTER allowed_origins,
    ADD COLUMN ai_api_key TEXT NOT NULL DEFAULT '' AFTER ai_provider,
    ADD COLUMN ai_model VARCHAR(120) NOT NULL DEFAULT 'openai/gpt-4o-mini' AFTER ai_api_key,
    ADD COLUMN ai_system_prompt TEXT NOT NULL DEFAULT '' AFTER ai_model,
    ADD COLUMN telegram_notify_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER ai_system_prompt,
    ADD COLUMN telegram_chat_id VARCHAR(64) NULL DEFAULT NULL AFTER telegram_notify_enabled;

-- Tabel chat_messages (jika belum ada)
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

-- Opsional: salin riwayat lama dari chat_logs (jalankan manual jika tabel itu masih ada)
-- INSERT INTO chat_messages (client_id, session_id, role, body, ip_address, created_at)
-- SELECT client_id, session_id,
--        CASE WHEN role = 'user' THEN 'user' ELSE 'assistant' END,
--        message, ip_address, created_at
-- FROM chat_logs;
