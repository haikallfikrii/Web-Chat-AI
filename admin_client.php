<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/includes/brand.php';
require_once __DIR__ . '/includes/admin.php';
require_once __DIR__ . '/includes/mail.php';

$user = require_platform_admin();
$pdo  = get_db();

$client_id = (int) ($_GET['id'] ?? 0);
if ($client_id < 1) {
    header('Location: ' . app_url('/admin.php'));
    exit;
}

$client = admin_get_client($pdo, $client_id);
if ($client === null) {
    set_flash('error', 'Klien tidak ditemukan.');
    header('Location: ' . app_url('/admin.php'));
    exit;
}

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die($_POST['csrf_token'] ?? '');
    $action = (string) ($_POST['action'] ?? '');

    switch ($action) {
        case 'change_plan':
            $new_plan = (string) ($_POST['plan_code'] ?? '');
            $new_status = (string) ($_POST['status'] ?? 'active');
            if (admin_change_client_plan($pdo, $client_id, $new_plan, $new_status)) {
                $flash = ['type' => 'success', 'message' => 'Paket berhasil diubah ke ' . admin_plan_label($new_plan)];
                $client = admin_get_client($pdo, $client_id);
            } else {
                $flash = ['type' => 'error', 'message' => 'Gagal mengubah paket.'];
            }
            break;

        case 'extend_trial':
            $days = (int) ($_POST['days'] ?? 7);
            if (admin_extend_trial($pdo, $client_id, $days)) {
                $flash = ['type' => 'success', 'message' => "Trial diperpanjang {$days} hari."];
                $client = admin_get_client($pdo, $client_id);
            } else {
                $flash = ['type' => 'error', 'message' => 'Gagal memperpanjang trial.'];
            }
            break;

        case 'toggle_status':
            $new_status = (string) ($_POST['status'] ?? '');
            if (admin_toggle_client_status($pdo, $client_id, $new_status)) {
                $flash = ['type' => 'success', 'message' => 'Status diubah ke ' . $new_status];
                $client = admin_get_client($pdo, $client_id);
            } else {
                $flash = ['type' => 'error', 'message' => 'Gagal mengubah status.'];
            }
            break;

        case 'reset_password':
            $owner_user_id = (int) ($client['owner_user_id'] ?? 0);
            if ($owner_user_id > 0) {
                $new_pw = admin_reset_client_password($pdo, $owner_user_id);
                if ($new_pw !== null) {
                    $flash = ['type' => 'success', 'message' => 'Password direset. Password baru: ' . $new_pw];
                } else {
                    $flash = ['type' => 'error', 'message' => 'Gagal reset password.'];
                }
            } else {
                $flash = ['type' => 'error', 'message' => 'User owner tidak ditemukan.'];
            }
            break;

        case 'impersonate':
            $session_data = admin_impersonate_client($pdo, $client_id);
            if ($session_data !== null) {
                $_SESSION['admin_impersonating'] = true;
                $_SESSION['admin_original_user'] = $_SESSION['auth_user'];
                $_SESSION['auth_user'] = $session_data;
                header('Location: ' . app_url('/dashboard.php'));
                exit;
            } else {
                $flash = ['type' => 'error', 'message' => 'Gagal impersonate klien.'];
            }
            break;

        case 'send_email':
            $subject = trim((string) ($_POST['email_subject'] ?? ''));
            $body = trim((string) ($_POST['email_body'] ?? ''));
            $to_email = (string) ($client['owner_email'] ?? $client['email']);
            if ($subject !== '' && $body !== '' && $to_email !== '') {
                $html = mail_html_layout($subject, '<p style="margin:0;color:#cbd5e1;white-space:pre-wrap;">' . nl2br(htmlspecialchars($body)) . '</p>', '', 'en');
                if (send_html_email($to_email, $subject, $html)) {
                    $flash = ['type' => 'success', 'message' => 'Email terkirim ke ' . $to_email];
                } else {
                    $flash = ['type' => 'error', 'message' => 'Gagal mengirim email.'];
                }
            } else {
                $flash = ['type' => 'error', 'message' => 'Subject dan body email harus diisi.'];
            }
            break;
    }
}

$activity = admin_client_activity_log($pdo, $client_id, 30);
$plan_options = admin_plan_options();

function fmt_dt(?string $dt): string {
    if ($dt === null || $dt === '') return '—';
    try { return (new DateTime($dt))->format('d M Y H:i'); }
    catch (Throwable) { return $dt; }
}

function status_badge(string $status): string {
    return match ($status) {
        'active' => 'badge-green',
        'inactive' => 'badge-red',
        default => 'badge-yellow',
    };
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#030712">
<?= brand_favicon_tags() ?>
<title>Klien: <?= e($client['name']) ?> — Admin <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/theme.css">
<style>
.adm-nav{position:sticky;top:0;z-index:100;height:62px;display:flex;align-items:center;gap:14px;padding:0 24px;
  background:rgba(3,7,18,.85);backdrop-filter:blur(20px);border-bottom:1px solid var(--border-2)}
.adm-nav .logo{font-weight:800;font-size:15px;color:var(--text);text-decoration:none;display:flex;align-items:center;gap:8px}
.adm-nav .logo span{color:var(--green)}
.adm-nav-actions{margin-left:auto;display:flex;align-items:center;gap:10px;font-size:13px}
.adm-wrap{max-width:1100px;margin:0 auto;padding:24px 24px 64px;position:relative;z-index:1}
.adm-back{display:inline-flex;align-items:center;gap:6px;color:var(--text-2);font-size:13px;text-decoration:none;margin-bottom:16px}
.adm-back:hover{color:var(--green)}
.adm-hero{margin-bottom:24px}
.adm-hero h1{font-size:24px;font-weight:800;margin:0 0 6px;letter-spacing:-.4px;display:flex;align-items:center;gap:12px}
.adm-hero p{margin:0;color:var(--text-2);font-size:14px}
.adm-grid{display:grid;grid-template-columns:1fr 380px;gap:20px;align-items:start}
@media(max-width:900px){.adm-grid{grid-template-columns:1fr}}
.adm-panel{border-radius:var(--r-lg);background:var(--glass-2);border:1px solid var(--border-2);overflow:hidden;margin-bottom:20px}
.adm-panel-head{padding:16px 20px;border-bottom:1px solid var(--border-2);display:flex;align-items:center;justify-content:space-between;gap:12px}
.adm-panel-head h2{margin:0;font-size:16px;font-weight:700}
.adm-panel-body{padding:20px}
.adm-info-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border-2);font-size:14px}
.adm-info-row:last-child{border-bottom:none}
.adm-info-row label{color:var(--text-2)}
.adm-info-row span{color:var(--text);font-weight:500}
.adm-info-row .mono{font-family:'JetBrains Mono',monospace;font-size:12px}
.adm-actions{display:flex;flex-direction:column;gap:12px}
.adm-action-card{padding:16px;border:1px solid var(--border-2);border-radius:12px;background:rgba(0,0,0,.2)}
.adm-action-card h3{margin:0 0 10px;font-size:14px;font-weight:700;color:var(--text)}
.adm-action-card p{margin:0 0 12px;font-size:13px;color:var(--text-2)}
.adm-action-card form{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
.adm-action-card select,.adm-action-card input[type="number"]{padding:8px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);color:var(--text);font-size:13px;min-width:120px}
.adm-action-card .btn{padding:8px 16px;font-size:13px}
.adm-log{max-height:400px;overflow-y:auto;font-size:13px}
.adm-log-item{padding:10px 0;border-bottom:1px solid var(--border-2)}
.adm-log-item:last-child{border-bottom:none}
.adm-log-item .role{font-size:11px;font-weight:700;text-transform:uppercase;margin-bottom:4px}
.adm-log-item .role.user{color:var(--cyan)}
.adm-log-item .role.assistant{color:var(--green)}
.adm-log-item .content{color:var(--text-2);word-break:break-word}
.adm-log-item .time{font-size:11px;color:var(--muted);margin-top:4px}
.alert{padding:14px 16px;border-radius:10px;margin-bottom:16px;font-size:14px}
.alert-success{background:rgba(0,229,154,.1);border:1px solid rgba(0,229,154,.3);color:#6ee7b7}
.alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
textarea.adm-textarea{width:100%;min-height:80px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);color:var(--text);font-size:13px;font-family:inherit;resize:vertical;box-sizing:border-box}
input.adm-input{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--bg);color:var(--text);font-size:13px;box-sizing:border-box}
</style>
</head>
<body>
<div class="bg-grid"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<header class="adm-nav">
  <a class="logo" href="<?= e(app_url('/admin.php')) ?>"><?= icon('shield', 18) ?> <?= e(APP_NAME) ?> <span>Admin</span></a>
  <div class="adm-nav-actions">
    <a class="btn btn-ghost btn-sm" href="<?= e(app_url('/admin.php')) ?>">← Dashboard Admin</a>
    <a class="btn btn-ghost btn-sm" href="<?= e(app_url('/logout.php')) ?>">Keluar</a>
  </div>
</header>

<main class="adm-wrap">
  <a href="<?= e(app_url('/admin.php')) ?>" class="adm-back"><?= icon('arrow-left', 16) ?> Kembali ke daftar klien</a>

  <div class="adm-hero">
    <h1>
      <?= e($client['name']) ?>
      <span class="badge <?= status_badge($client['subscription_status']) ?>"><?= e($client['subscription_status']) ?></span>
    </h1>
    <p>Client ID: #<?= (int) $client['id'] ?> · Terdaftar <?= fmt_dt($client['created_at']) ?></p>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= e($flash['message']) ?></div>
  <?php endif; ?>

  <div class="adm-grid">
    <div>
      <!-- Info Klien -->
      <div class="adm-panel">
        <div class="adm-panel-head"><h2>Informasi Klien</h2></div>
        <div class="adm-panel-body">
          <div class="adm-info-row"><label>Nama Bisnis</label><span><?= e($client['name']) ?></span></div>
          <div class="adm-info-row"><label>Email</label><span class="mono"><?= e($client['email']) ?></span></div>
          <div class="adm-info-row"><label>Owner</label><span><?= e($client['owner_name'] ?? '—') ?></span></div>
          <div class="adm-info-row"><label>Email Owner</label><span class="mono"><?= e($client['owner_email'] ?? '—') ?></span></div>
          <div class="adm-info-row"><label>Paket</label><span><?= e(admin_plan_label($client['plan_code'] ?? '')) ?></span></div>
          <div class="adm-info-row"><label>Status</label><span class="badge <?= status_badge($client['subscription_status']) ?>"><?= e($client['subscription_status']) ?></span></div>
          <div class="adm-info-row"><label>Trial Ends</label><span><?= fmt_dt($client['trial_ends_at'] ?? null) ?></span></div>
          <div class="adm-info-row"><label>Subscription Ends</label><span><?= fmt_dt($client['subscription_ends_at'] ?? null) ?></span></div>
          <div class="adm-info-row"><label>Last Login</label><span><?= fmt_dt($client['last_login_at'] ?? null) ?></span></div>
          <div class="adm-info-row"><label>API Key</label><span class="mono" style="font-size:11px"><?= e(substr($client['api_key'] ?? '', 0, 20)) ?>...</span></div>
        </div>
      </div>

      <!-- Widget Settings -->
      <div class="adm-panel">
        <div class="adm-panel-head"><h2>Widget Settings</h2></div>
        <div class="adm-panel-body">
          <div class="adm-info-row"><label>Bot Name</label><span><?= e($client['bot_name'] ?? '—') ?></span></div>
          <div class="adm-info-row"><label>AI Provider</label><span><?= e($client['ai_provider'] ?? '—') ?></span></div>
          <div class="adm-info-row"><label>AI Model</label><span class="mono"><?= e($client['ai_model'] ?? '—') ?></span></div>
          <div class="adm-info-row"><label>Telegram Notif</label><span><?= ($client['telegram_notify_enabled'] ?? 0) ? '✅ Aktif' : '❌ Nonaktif' ?></span></div>
          <div class="adm-info-row"><label>Primary Color</label><span style="display:flex;align-items:center;gap:8px"><span style="width:16px;height:16px;border-radius:4px;background:<?= e($client['primary_color'] ?? '#14B8A6') ?>"></span> <?= e($client['primary_color'] ?? '#14B8A6') ?></span></div>
        </div>
      </div>

      <!-- Usage Stats -->
      <div class="adm-panel">
        <div class="adm-panel-head"><h2>Usage Stats</h2></div>
        <div class="adm-panel-body">
          <div class="adm-info-row"><label>Total Pesan</label><span><?= number_format((int) ($client['total_messages'] ?? 0)) ?></span></div>
          <div class="adm-info-row"><label>Total Sesi</label><span><?= number_format((int) ($client['total_sessions'] ?? 0)) ?></span></div>
        </div>
      </div>

      <!-- Activity Log -->
      <div class="adm-panel">
        <div class="adm-panel-head"><h2>Aktivitas Chat Terbaru</h2></div>
        <div class="adm-panel-body adm-log">
          <?php if ($activity === []): ?>
            <p style="color:var(--text-2);margin:0">Belum ada aktivitas chat.</p>
          <?php else: ?>
            <?php foreach ($activity as $log): ?>
              <div class="adm-log-item">
                <div class="role <?= e($log['role']) ?>"><?= e($log['role']) ?></div>
                <div class="content"><?= e(mb_substr($log['content'], 0, 200)) ?><?= mb_strlen($log['content']) > 200 ? '...' : '' ?></div>
                <div class="time"><?= fmt_dt($log['created_at']) ?></div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Actions Sidebar -->
    <aside class="adm-actions">
      <!-- Change Plan -->
      <div class="adm-action-card">
        <h3>📦 Ubah Paket</h3>
        <p>Set paket langganan klien secara manual.</p>
        <form method="post" action="<?= e(app_url('/admin_client.php', ['id' => $client_id])) ?>">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="change_plan">
          <select name="plan_code">
            <?php foreach ($plan_options as $code => $label): ?>
              <option value="<?= e($code) ?>" <?= ($client['plan_code'] ?? '') === $code ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
          <select name="status">
            <option value="active" <?= ($client['subscription_status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="trial" <?= ($client['subscription_status'] ?? '') === 'trial' ? 'selected' : '' ?>>Trial</option>
            <option value="inactive" <?= ($client['subscription_status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
          </select>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
      </div>

      <!-- Extend Trial -->
      <div class="adm-action-card">
        <h3>⏰ Perpanjang Trial</h3>
        <p>Tambah hari trial dari tanggal saat ini.</p>
        <form method="post" action="<?= e(app_url('/admin_client.php', ['id' => $client_id])) ?>">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="extend_trial">
          <input type="number" name="days" value="7" min="1" max="365" style="width:80px"> hari
          <button type="submit" class="btn btn-primary">Perpanjang</button>
        </form>
      </div>

      <!-- Toggle Status -->
      <div class="adm-action-card">
        <h3>🔄 Ubah Status</h3>
        <p>Toggle status langganan klien.</p>
        <form method="post" action="<?= e(app_url('/admin_client.php', ['id' => $client_id])) ?>">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="toggle_status">
          <select name="status">
            <option value="active">Active</option>
            <option value="trial">Trial</option>
            <option value="inactive">Inactive</option>
          </select>
          <button type="submit" class="btn btn-primary">Set Status</button>
        </form>
      </div>

      <!-- Impersonate -->
      <div class="adm-action-card">
        <h3>👤 Login sebagai Klien</h3>
        <p>Masuk ke dashboard klien untuk debugging.</p>
        <form method="post" action="<?= e(app_url('/admin_client.php', ['id' => $client_id])) ?>">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="impersonate">
          <button type="submit" class="btn btn-ghost">Impersonate →</button>
        </form>
      </div>

      <!-- Reset Password -->
      <div class="adm-action-card">
        <h3>🔑 Reset Password</h3>
        <p>Generate password baru untuk user owner.</p>
        <form method="post" action="<?= e(app_url('/admin_client.php', ['id' => $client_id])) ?>" onsubmit="return confirm('Reset password user ini?')">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="reset_password">
          <button type="submit" class="btn btn-ghost">Reset Password</button>
        </form>
      </div>

      <!-- Send Email -->
      <div class="adm-action-card">
        <h3>✉️ Kirim Email</h3>
        <p>Kirim email manual ke klien.</p>
        <form method="post" action="<?= e(app_url('/admin_client.php', ['id' => $client_id])) ?>">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="send_email">
          <input type="text" name="email_subject" placeholder="Subject email" class="adm-input" style="margin-bottom:8px" required>
          <textarea name="email_body" placeholder="Isi email..." class="adm-textarea" required></textarea>
          <button type="submit" class="btn btn-primary" style="margin-top:8px">Kirim Email</button>
        </form>
      </div>
    </aside>
  </div>
</main>
</body>
</html>
