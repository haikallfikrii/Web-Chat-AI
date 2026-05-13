<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('CLI only');
}

$password = $argv[1] ?? '';
if ($password === '') {
    fwrite(STDERR, "Usage: php scripts/generate_password_hash.php 'PasswordKuatAnda'\n");
    exit(1);
}

echo password_hash($password, PASSWORD_DEFAULT), PHP_EOL;
