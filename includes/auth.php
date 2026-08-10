<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/lang.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function dashboard_base_url(): string
{
    return app_base_url();
}

function generate_client_api_key(): string
{
    return hash('sha256', random_bytes(32));
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_or_die(?string $token): void
{
    if (!verify_csrf($token)) {
        http_response_code(419);
        exit('CSRF token mismatch.');
    }
}

function verify_csrf(?string $token): bool
{
    $session_token = $_SESSION['csrf_token'] ?? '';
    return is_string($session_token)
        && is_string($token)
        && $session_token !== ''
        && hash_equals($session_token, $token);
}

function current_user(): ?array
{
    $user = $_SESSION['auth_user'] ?? null;
    return is_array($user) ? $user : null;
}

function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        header('Location: ' . app_url('/login.php'));
        exit;
    }
    return $user;
}

/**
 * Login attempt dengan error message yang ramah.
 *
 * @return array{ok: bool, error: ?string}
 */
function attempt_login(string $email, string $password): array
{
    $email = trim(mb_strtolower($email, 'UTF-8'));

    if ($email === '') {
        return ['ok' => false, 'error' => 'Email wajib diisi.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Format email tidak valid.'];
    }
    if ($password === '') {
        return ['ok' => false, 'error' => 'Password wajib diisi.'];
    }

    try {
        $pdo  = get_db();
        $stmt = $pdo->prepare(
            'SELECT u.id, u.client_id, u.name, u.email, u.password_hash, u.role, u.is_active,
                    c.name AS client_name, c.api_key, c.subscription_status, c.plan_code
             FROM users u
             INNER JOIN clients c ON c.id = u.client_id
             WHERE u.email = :email
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();

        if (!$row) {
            return ['ok' => false, 'error' => 'Email belum terdaftar. Silakan cek email atau daftar dulu.'];
        }
        if ((int) $row['is_active'] !== 1) {
            return ['ok' => false, 'error' => 'Akun Anda dinonaktifkan. Hubungi support.'];
        }
        if (!password_verify($password, (string) $row['password_hash'])) {
            return ['ok' => false, 'error' => 'Password salah. Coba lagi atau gunakan "Lupa password?"'];
        }

        session_regenerate_id(true);
        $_SESSION['auth_user'] = [
            'id'                  => (int) $row['id'],
            'client_id'           => (int) $row['client_id'],
            'name'                => (string) $row['name'],
            'email'               => (string) $row['email'],
            'role'                => (string) $row['role'],
            'client_name'         => (string) $row['client_name'],
            'client_api_key'      => (string) $row['api_key'],
            'subscription_status' => (string) $row['subscription_status'],
            'plan_code'           => (string) ($row['plan_code'] ?? 'trial'),
        ];

        $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')
            ->execute([':id' => (int) $row['id']]);

        return ['ok' => true, 'error' => null];

    } catch (Throwable $e) {
        error_log('[login] ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Terjadi kesalahan sistem. Silakan coba lagi sebentar.'];
    }
}

/** Backward-compat alias (lama: bool). */
function login_attempt(string $email, string $password): bool
{
    return attempt_login($email, $password)['ok'];
}

/**
 * Daftarkan user baru.
 * Membuat rows di clients, widget_settings, dan users dalam satu transaksi,
 * lalu auto-login agar user langsung masuk ke dashboard.
 *
 * @return array{ok: bool, error: ?string}
 */
function register_user(
    string $name,
    string $email,
    string $password,
    string $password_confirm,
    string $business_name
): array {
    $name          = trim($name);
    $email         = trim(mb_strtolower($email, 'UTF-8'));
    $business_name = trim($business_name);

    if ($name === '' || mb_strlen($name, 'UTF-8') > 120) {
        return ['ok' => false, 'error' => 'Nama Anda harus diisi (maks 120 karakter).'];
    }

    if ($business_name === '' || mb_strlen($business_name, 'UTF-8') > 150) {
        return ['ok' => false, 'error' => 'Nama bisnis/website harus diisi (maks 150 karakter).'];
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Format email tidak valid.'];
    }

    if (strlen($password) < 8) {
        return ['ok' => false, 'error' => 'Password minimal 8 karakter.'];
    }

    if ($password !== $password_confirm) {
        return ['ok' => false, 'error' => 'Konfirmasi password tidak cocok.'];
    }

    $pdo = get_db();

    $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $check->execute([':email' => $email]);
    if ($check->fetch()) {
        return ['ok' => false, 'error' => 'Email sudah terdaftar. Silakan login.'];
    }

    $api_key       = generate_client_api_key();
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        $trial_ends = (new DateTime('+' . (string) TRIAL_DAYS . ' days'))->format('Y-m-d H:i:s');

        $pdo->prepare(
            'INSERT INTO clients (name, email, api_key, subscription_status, plan_code, trial_ends_at)
             VALUES (:name, :email, :api_key, :status, :plan, :trial_ends)'
        )->execute([
            ':name'       => mb_substr($business_name, 0, 150, 'UTF-8'),
            ':email'      => $email,
            ':api_key'    => $api_key,
            ':status'     => 'trial',
            ':plan'       => 'free',
            ':trial_ends' => $trial_ends,
        ]);
        $client_id = (int) $pdo->lastInsertId();

        $pdo->prepare(
            'INSERT INTO widget_settings
                (client_id, primary_color, bot_name, bot_avatar_url, welcome_message,
                 n8n_webhook_url, allowed_origins, ai_provider, ai_api_key, ai_model,
                 ai_system_prompt, telegram_notify_enabled, telegram_chat_id)
             VALUES
                (:client_id, :primary_color, :bot_name, :bot_avatar_url, :welcome_message,
                 :n8n_webhook_url, :allowed_origins, :ai_provider, :ai_api_key, :ai_model,
                 :ai_system_prompt, :telegram_notify_enabled, :telegram_chat_id)'
        )->execute([
            ':client_id'               => $client_id,
            ':primary_color'           => '#14B8A6',
            ':bot_name'                => mb_substr($business_name, 0, 80, 'UTF-8') . ' Assistant',
            ':bot_avatar_url'          => '',
            ':welcome_message'         => 'Halo! Ada yang bisa saya bantu?',
            ':n8n_webhook_url'         => '',
            ':allowed_origins'         => '*',
            ':ai_provider'             => 'openrouter',
            ':ai_api_key'              => '',
            ':ai_model'                => 'openai/gpt-4o-mini',
            ':ai_system_prompt'        => '',
            ':telegram_notify_enabled' => 0,
            ':telegram_chat_id'        => null,
        ]);

        $pdo->prepare(
            'INSERT INTO users (client_id, name, email, password_hash, role, is_active)
             VALUES (:client_id, :name, :email, :password_hash, :role, 1)'
        )->execute([
            ':client_id'     => $client_id,
            ':name'          => mb_substr($name, 0, 120, 'UTF-8'),
            ':email'         => $email,
            ':password_hash' => $password_hash,
            ':role'          => 'owner',
        ]);
        $user_id = (int) $pdo->lastInsertId();

        require_once __DIR__ . '/managed_ai.php';
        billing_sync_client_plan_columns($pdo, $client_id, 'free');

        $pdo->commit();

        session_regenerate_id(true);
        $_SESSION['auth_user'] = [
            'id'                  => $user_id,
            'client_id'           => $client_id,
            'name'                => $name,
            'email'               => $email,
            'role'                => 'owner',
            'client_name'         => $business_name,
            'client_api_key'      => $api_key,
            'subscription_status' => 'trial',
            'plan_code'           => 'free',
        ];

        $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')
            ->execute([':id' => $user_id]);

        require_once __DIR__ . '/admin.php';
        admin_notify_new_registration($client_id, $business_name, $name, $email);

        return ['ok' => true, 'error' => null];

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[register] ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Registrasi gagal. Silakan coba lagi.'];
    }
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', (bool) $p['secure'], (bool) $p['httponly']);
    }
    session_destroy();
}

function fetch_dashboard_settings(int $client_id): ?array
{
    $pdo  = get_db();
    $stmt = $pdo->prepare(
        'SELECT c.name AS client_name, c.email AS client_email, c.api_key, c.subscription_status,
                ws.primary_color, ws.bot_name, ws.bot_avatar_url, ws.welcome_message,
                ws.allowed_origins, ws.ai_provider, ws.ai_api_key, ws.ai_model, ws.ai_system_prompt,
                ws.n8n_webhook_url, ws.telegram_notify_enabled, ws.telegram_chat_id
         FROM clients c
         INNER JOIN widget_settings ws ON ws.client_id = c.id
         WHERE c.id = :client_id
         LIMIT 1'
    );
    $stmt->execute([':client_id' => $client_id]);
    return $stmt->fetch() ?: null;
}

/* ─────────────────────────────────────────────────────────────
 * PASSWORD RESET
 * ─────────────────────────────────────────────────────────── */

/**
 * Buat token reset password, simpan hash-nya di DB, kembalikan token plain
 * untuk dikirim via email/Telegram. Selalu kembalikan struktur sukses agar
 * email yang tidak terdaftar tidak bocor.
 *
 * @return array{ok: bool, token: ?string, user_email: ?string, exists: bool, error: ?string}
 */
function create_password_reset_token(string $email): array
{
    $email = trim(mb_strtolower($email, 'UTF-8'));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'token' => null, 'user_email' => null, 'exists' => false, 'error' => 'Format email tidak valid.'];
    }

    try {
        $pdo  = get_db();
        $stmt = $pdo->prepare('SELECT id, email FROM users WHERE email = :email AND is_active = 1 LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['ok' => true, 'token' => null, 'user_email' => $email, 'exists' => false, 'error' => null];
        }

        $pdo->prepare('DELETE FROM password_resets WHERE user_id = :uid')
            ->execute([':uid' => (int) $user['id']]);

        $token      = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token);
        $expires_at = (new DateTime('+60 minutes'))->format('Y-m-d H:i:s');

        $pdo->prepare(
            'INSERT INTO password_resets (user_id, token_hash, expires_at)
             VALUES (:uid, :hash, :exp)'
        )->execute([
            ':uid'  => (int) $user['id'],
            ':hash' => $token_hash,
            ':exp'  => $expires_at,
        ]);

        return ['ok' => true, 'token' => $token, 'user_email' => $email, 'exists' => true, 'error' => null];

    } catch (Throwable $e) {
        error_log('[pwreset:create] ' . $e->getMessage());
        return ['ok' => false, 'token' => null, 'user_email' => $email, 'exists' => false, 'error' => 'Terjadi kesalahan sistem.'];
    }
}

/** Kembalikan user_id jika token valid & belum kedaluwarsa. */
function find_user_by_reset_token(string $token): ?int
{
    if ($token === '' || strlen($token) !== 64 || !ctype_xdigit($token)) {
        return null;
    }
    try {
        $pdo  = get_db();
        $hash = hash('sha256', $token);
        $stmt = $pdo->prepare(
            'SELECT user_id FROM password_resets
             WHERE token_hash = :hash AND used_at IS NULL AND expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute([':hash' => $hash]);
        $row = $stmt->fetch();
        return $row ? (int) $row['user_id'] : null;
    } catch (Throwable $e) {
        error_log('[pwreset:find] ' . $e->getMessage());
        return null;
    }
}

/**
 * Konsumsi token + set password baru.
 *
 * @return array{ok: bool, error: ?string}
 */
function consume_password_reset(string $token, string $new_password, string $confirm): array
{
    if (strlen($new_password) < 8) {
        return ['ok' => false, 'error' => 'Password baru minimal 8 karakter.'];
    }
    if ($new_password !== $confirm) {
        return ['ok' => false, 'error' => 'Konfirmasi password tidak cocok.'];
    }

    $user_id = find_user_by_reset_token($token);
    if ($user_id === null) {
        return ['ok' => false, 'error' => 'Link reset sudah kedaluwarsa atau tidak valid. Mohon ajukan ulang.'];
    }

    try {
        $pdo  = get_db();
        $hash = password_hash($new_password, PASSWORD_DEFAULT);

        $pdo->beginTransaction();
        $pdo->prepare('UPDATE users SET password_hash = :h WHERE id = :uid')
            ->execute([':h' => $hash, ':uid' => $user_id]);
        $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE user_id = :uid AND used_at IS NULL')
            ->execute([':uid' => $user_id]);
        $pdo->commit();

        return ['ok' => true, 'error' => null];

    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        error_log('[pwreset:consume] ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Gagal memperbarui password. Silakan coba lagi.'];
    }
}

/** Kirim link reset password via email HTML branded, sesuai bahasa aktif user saat ini. */
function send_password_reset_email(string $to_email, string $reset_link): bool
{
    require_once __DIR__ . '/mail.php';
    return send_password_reset_email_html($to_email, $reset_link, get_lang());
}
