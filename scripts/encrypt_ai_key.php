<?php
/**
 * CLI: menghasilkan ciphertext untuk kolom widget_settings.ai_api_key
 * Gunakan: php scripts/encrypt_ai_key.php 'sk-xxxxx'
 * Pastikan APP_SECRET sama dengan yang dipakai server produksi.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('CLI only');
}

require_once __DIR__ . '/../config.php';

$key = $argv[1] ?? '';
if ($key === '') {
    fwrite(STDERR, "Usage: php scripts/encrypt_ai_key.php 'YOUR_PLAINTEXT_API_KEY'\n");
    exit(1);
}

echo encrypt_secret($key), PHP_EOL;
