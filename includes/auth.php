<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

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
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
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
    $session_token = $_SESSION['csrf_token'] ?? '';
    if (!is_string($session_token) || !is_string($token) || !hash_equals($session_token, $token)) {
        http_response_code(419);
        exit('CSRF token mismatch.');
    }
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
        header('Location: /login.php');
        exit;
    }

    return $user;
}

function login_attempt(string $email, string $password): bool
{
    $email = trim(mb_strtolower($email, 'UTF-8'));
    if ($email === '' || $password === '') {
        return false;
    }

    $pdo = get_db();
    $stmt = $pdo->prepare(
        'SELECT u.id, u.client_id, u.name, u.email, u.password_hash, u.role, u.is_active,
                c.name AS client_name, c.api_key, c.subscription_status
         FROM users u
         INNER JOIN clients c ON c.id = u.client_id
         WHERE u.email = :email
         LIMIT 1'
    );
    $stmt->execute([':email' => $email]);
    $row = $stmt->fetch();

    if (!$row || (int) $row['is_active'] !== 1) {
        return false;
    }

    if (!password_verify($password, (string) $row['password_hash'])) {
        return false;
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
    ];

    $update = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
    $update->execute([':id' => (int) $row['id']]);

    return true;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}

function fetch_dashboard_settings(int $client_id): ?array
{
    $pdo = get_db();
    $stmt = $pdo->prepare(
        'SELECT c.name AS client_name, c.email AS client_email, c.api_key, c.subscription_status,
                ws.primary_color, ws.bot_name, ws.bot_avatar_url, ws.welcome_message,
                ws.allowed_origins, ws.ai_provider, ws.ai_model, ws.ai_system_prompt,
                ws.n8n_webhook_url, ws.telegram_notify_enabled, ws.telegram_chat_id
         FROM clients c
         INNER JOIN widget_settings ws ON ws.client_id = c.id
         WHERE c.id = :client_id
         LIMIT 1'
    );
    $stmt->execute([':client_id' => $client_id]);
    return $stmt->fetch() ?: null;
}
