<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/includes/brand.php';
require_once __DIR__ . '/includes/admin.php';

$user = require_platform_admin();
$pdo  = get_db();

$migration_needed = !admin_notifications_table_ready($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$migration_needed) {
    verify_csrf_or_die($_POST['csrf_token'] ?? '');
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'mark_all_read') {
        admin_mark_notifications_read($pdo, null);
        set_flash('success', 'Semua notifikasi ditandai sudah dibaca.');
        header('Location: ' . app_url('/admin.php'));
        exit;
    }
}

$stats         = admin_summary_stats($pdo);
$clients       = admin_clients_list($pdo);
$chart         = admin_messages_per_day($pdo, 14);
$notifications = admin_fetch_notifications($pdo);
$unread        = admin_unread_notification_count($pdo);
$flash         = get_flash();

$chartMax = 1;
foreach ($chart as $row) {
    $chartMax = max($chartMax, (int) $row['cnt']);
}

function admin_fmt_dt(?string $dt): string
{
    if ($dt === null || $dt === '') {
        return '—';
    }
    try {
        return (new DateTime($dt))->format('d M Y H:i');
    } catch (Throwable) {
        return $dt;
    }
}

function admin_status_badge(string $status): string
{
    return match ($status) {
        'active'   => 'badge-green',
        'inactive' => 'badge-red',
        default    => 'badge-yellow',
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
<title>Admin — <?= e(APP_NAME) ?></title>
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
.adm-wrap{max-width:1280px;margin:0 auto;padding:24px 24px 64px;position:relative;z-index:1}
.adm-hero{margin-bottom:22px}
.adm-hero h1{font-size:26px;font-weight:800;margin:0 0 6px;letter-spacing:-.4px}
.adm-hero p{margin:0;color:var(--text-2);font-size:14px}
.adm-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px}
.adm-stat{padding:18px 20px;border-radius:var(--r-lg);background:var(--glass-2);border:1px solid var(--border-2)}
.adm-stat label{display:block;font-size:12px;color:var(--muted);margin-bottom:6px;font-weight:600;text-transform:uppercase;letter-spacing:.04em}
.adm-stat strong{font-size:28px;font-weight:800;color:var(--text);letter-spacing:-.5px}
.adm-stat small{display:block;margin-top:4px;font-size:12px;color:var(--text-2)}
.adm-layout{display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start}
.adm-panel{border-radius:var(--r-lg);background:var(--glass-2);border:1px solid var(--border-2);overflow:hidden}
.adm-panel-head{padding:16px 20px;border-bottom:1px solid var(--border-2);display:flex;align-items:center;justify-content:space-between;gap:12px}
.adm-panel-head h2{margin:0;font-size:16px;font-weight:700}
.adm-table-wrap{overflow-x:auto}
table.adm-table{width:100%;border-collapse:collapse;font-size:13px}
table.adm-table th,table.adm-table td{padding:11px 14px;text-align:left;border-bottom:1px solid var(--border-2);vertical-align:top}
table.adm-table th{color:var(--muted);font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.04em;background:rgba(0,0,0,.15)}
table.adm-table tr:last-child td{border-bottom:none}
table.adm-table td.mono{font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--text-2)}
.adm-chart{padding:16px 20px 20px;display:flex;align-items:flex-end;gap:6px;height:140px}
.adm-bar{flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;min-width:0}
.adm-bar-fill{width:100%;max-width:28px;border-radius:6px 6px 4px 4px;background:linear-gradient(180deg,var(--green),rgba(0,229,154,.35));min-height:4px;transition:height .3s}
.adm-bar span{font-size:10px;color:var(--muted);white-space:nowrap}
.adm-notif-list{max-height:520px;overflow-y:auto}
.adm-notif{padding:14px 18px;border-bottom:1px solid var(--border-2);font-size:13px;line-height:1.5}
.adm-notif.unread{background:rgba(0,229,154,.06);border-left:3px solid var(--green)}
.adm-notif time{display:block;font-size:11px;color:var(--muted);margin-top:6px}
.adm-notif-type{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--cyan);margin-bottom:4px}
.adm-alert{margin-bottom:18px;padding:14px 16px;border-radius:12px;border:1px solid rgba(251,191,36,.35);background:rgba(251,191,36,.08);color:#fde68a;font-size:13px;line-height:1.5}
.adm-alert code{font-family:'JetBrains Mono',monospace;font-size:12px}
@media(max-width:1024px){.adm-grid{grid-template-columns:repeat(2,1fr)}.adm-layout{grid-template-columns:1fr}}
@media(max-width:560px){.adm-grid{grid-template-columns:1fr}.adm-nav{padding:0 14px}}
</style>
</head>
<body>
<div class="bg-grid"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<header class="adm-nav">
  <a class="logo" href="<?= e(app_url('/admin.php')) ?>"><?= icon('shield', 18) ?> <?= e(APP_NAME) ?> <span>Admin</span></a>
  <div class="adm-nav-actions">
    <?php if ($unread > 0): ?>
      <span class="badge badge-yellow"><?= (int) $unread ?> notif</span>
    <?php endif; ?>
    <a class="btn btn-ghost btn-sm" href="<?= e(app_url('/dashboard.php')) ?>">Dashboard klien</a>
    <a class="btn btn-ghost btn-sm" href="<?= e(app_url('/logout.php')) ?>">Keluar</a>
  </div>
</header>

<main class="adm-wrap">
  <div class="adm-hero">
    <h1>Platform overview</h1>
    <p>Monitor klien, paket langganan, analitik widget, dan notifikasi registrasi/pembelian.</p>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type'] === 'success' ? 'success' : 'error') ?>"><?= e($flash['message']) ?></div>
  <?php endif; ?>

  <?php if ($migration_needed): ?>
    <div class="adm-alert">
      Tabel <code>admin_notifications</code> belum ada. Jalankan migrasi SQL
      <code>schema_migration_v7_admin.sql</code> di phpMyAdmin (database staging/production).
      Notifikasi in-app & email tim akan aktif setelah migrasi.
    </div>
  <?php endif; ?>

  <section class="adm-grid">
    <div class="adm-stat">
      <label>Total klien</label>
      <strong><?= (int) $stats['clients_total'] ?></strong>
      <small>+<?= (int) $stats['registrations_7d'] ?> dalam 7 hari</small>
    </div>
    <div class="adm-stat">
      <label>Langganan aktif</label>
      <strong><?= (int) $stats['clients_active'] ?></strong>
      <small><?= (int) $stats['clients_trial'] ?> trial · <?= (int) $stats['clients_inactive'] ?> nonaktif</small>
    </div>
    <div class="adm-stat">
      <label>Pesan pengunjung</label>
      <strong><?= (int) $stats['messages_today'] ?></strong>
      <small>Hari ini · <?= (int) $stats['messages_7d'] ?> / 7 hari</small>
    </div>
    <div class="adm-stat">
      <label>Analitik 30 hari</label>
      <strong><?= (int) $stats['messages_30d'] ?></strong>
      <small><?= (int) $stats['sessions_30d'] ?> sesi chat</small>
    </div>
  </section>

  <section class="adm-layout">
    <div>
      <div class="adm-panel" style="margin-bottom:20px">
        <div class="adm-panel-head">
          <h2>Pesan pengunjung (14 hari)</h2>
        </div>
        <div class="adm-chart" aria-hidden="true">
          <?php foreach ($chart as $row):
              $h = (int) round(((int) $row['cnt'] / $chartMax) * 100);
              $label = (new DateTime((string) $row['day']))->format('d/m');
          ?>
            <div class="adm-bar" title="<?= e($row['day']) ?>: <?= (int) $row['cnt'] ?>">
              <div class="adm-bar-fill" style="height:<?= max(4, $h) ?>%"></div>
              <span><?= e($label) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="adm-panel">
        <div class="adm-panel-head">
          <h2>Semua klien</h2>
          <span class="badge badge-green"><?= count($clients) ?> terbaru</span>
        </div>
        <div class="adm-table-wrap">
          <table class="adm-table">
            <thead>
              <tr>
                <th>Bisnis</th>
                <th>Owner</th>
                <th>Paket</th>
                <th>Status</th>
                <th>Pesan</th>
                <th>Daftar</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($clients === []): ?>
                <tr><td colspan="6">Belum ada klien.</td></tr>
              <?php else: ?>
                <?php foreach ($clients as $c): ?>
                  <tr>
                    <td>
                      <strong><?= e((string) $c['name']) ?></strong><br>
                      <span class="mono">#<?= (int) $c['id'] ?></span>
                    </td>
                    <td>
                      <?= e((string) ($c['owner_name'] ?? '—')) ?><br>
                      <span class="mono"><?= e((string) ($c['owner_email'] ?? $c['email'])) ?></span>
                    </td>
                    <td><?= e(admin_plan_label((string) ($c['plan_code'] ?? ''))) ?></td>
                    <td><span class="badge <?= admin_status_badge((string) $c['subscription_status']) ?>"><?= e((string) $c['subscription_status']) ?></span></td>
                    <td><?= (int) ($c['user_messages'] ?? 0) ?> msg<br><span class="mono"><?= (int) ($c['sessions'] ?? 0) ?> sesi</span></td>
                    <td class="mono"><?= e(admin_fmt_dt((string) $c['created_at'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <aside class="adm-panel">
      <div class="adm-panel-head">
        <h2>Notifikasi</h2>
        <?php if (!$migration_needed && $unread > 0): ?>
          <form method="post" action="<?= e(app_url('/admin.php')) ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="mark_all_read">
            <button type="submit" class="btn btn-ghost btn-sm">Tandai dibaca</button>
          </form>
        <?php endif; ?>
      </div>
      <div class="adm-notif-list">
        <?php if ($notifications === []): ?>
          <div class="adm-notif">Belum ada notifikasi.</div>
        <?php else: ?>
          <?php foreach ($notifications as $n): ?>
            <article class="adm-notif<?= (int) ($n['is_read'] ?? 1) === 0 ? ' unread' : '' ?>">
              <div class="adm-notif-type"><?= e((string) $n['event_type']) ?></div>
              <strong><?= e((string) $n['title']) ?></strong>
              <p style="margin:6px 0 0;color:var(--text-2)"><?= nl2br(e((string) $n['body'])) ?></p>
              <time><?= e(admin_fmt_dt((string) $n['created_at'])) ?></time>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </aside>
  </section>
</main>
</body>
</html>
