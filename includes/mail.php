<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/plans.php';

function mail_from_address(): string
{
    if (MAIL_FROM_ADDRESS !== '') {
        return MAIL_FROM_ADDRESS;
    }
    $host = parse_url(app_site_url(), PHP_URL_HOST) ?: 'localhost';
    return 'billing@' . $host;
}

function mail_support_address(): string
{
    return MAIL_SUPPORT !== '' ? MAIL_SUPPORT : mail_from_address();
}

/**
 * Template HTML email dengan identitas ChatPopup.AI.
 */
function mail_html_layout(string $title, string $body_html, ?string $preheader = null): string
{
    $site   = app_site_url();
    $brand  = APP_NAME;
    $year   = (string) date('Y');
    $pre    = $preheader !== null && $preheader !== ''
        ? '<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;">' . htmlspecialchars($preheader, ENT_QUOTES, 'UTF-8') . '</div>'
        : '';

    return '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>
</head>
<body style="margin:0;padding:0;background:#030712;font-family:Inter,-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;">
' . $pre . '
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#030712;padding:32px 16px;">
<tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#0f172a;border-radius:16px;border:1px solid rgba(255,255,255,.08);overflow:hidden;">
<tr><td style="padding:28px 32px 20px;background:linear-gradient(135deg,#00E59A 0%,#22d3ee 100%);">
<table role="presentation" width="100%"><tr>
<td style="font-size:22px;font-weight:800;color:#031018;letter-spacing:-.3px;">' . htmlspecialchars($brand, ENT_QUOTES, 'UTF-8') . '</td>
<td align="right" style="font-size:12px;color:rgba(3,16,24,.65);font-weight:600;">AI Chat Widget</td>
</tr></table>
</td></tr>
<tr><td style="padding:32px;color:#e2e8f0;font-size:15px;line-height:1.65;">
<h1 style="margin:0 0 16px;font-size:22px;font-weight:800;color:#f8fafc;letter-spacing:-.3px;">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>
' . $body_html . '
</td></tr>
<tr><td style="padding:20px 32px 28px;border-top:1px solid rgba(255,255,255,.06);font-size:12px;color:#64748b;line-height:1.5;">
<p style="margin:0 0 8px;">&copy; ' . $year . ' ' . htmlspecialchars($brand, ENT_QUOTES, 'UTF-8') . '. Semua hak dilindungi.</p>
<p style="margin:0;"><a href="' . htmlspecialchars($site, ENT_QUOTES, 'UTF-8') . '" style="color:#00E59A;text-decoration:none;">' . htmlspecialchars($site, ENT_QUOTES, 'UTF-8') . '</a>
 &nbsp;&middot;&nbsp; <a href="mailto:' . htmlspecialchars(mail_support_address(), ENT_QUOTES, 'UTF-8') . '" style="color:#94a3b8;text-decoration:none;">Dukungan</a></p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>';
}

function mail_button(string $url, string $label): string
{
    return '<p style="margin:24px 0 8px;text-align:center;">
<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;padding:14px 28px;background:linear-gradient(135deg,#00E59A,#22d3ee);color:#031018;font-weight:700;font-size:15px;text-decoration:none;border-radius:10px;">'
        . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a></p>';
}

function mail_info_box(string $html): string
{
    return '<div style="margin:20px 0;padding:16px 18px;background:rgba(0,229,154,.08);border:1px solid rgba(0,229,154,.25);border-radius:10px;color:#cbd5e1;">' . $html . '</div>';
}

/**
 * Kirim email HTML multipart (fallback plain).
 */
function send_html_email(string $to, string $subject, string $html, ?string $plain = null): bool
{
    $plain = $plain ?? strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html));
    $from  = mail_from_address();
    $name  = MAIL_FROM_NAME;
    $boundary = 'cp_' . bin2hex(random_bytes(12));

    $encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    $headers = [
        'From: ' . $name . ' <' . $from . '>',
        'Reply-To: ' . mail_support_address(),
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        'X-Mailer: ChatPopup.AI',
    ];

    $body = "--{$boundary}\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($plain)) . "\r\n"
        . "--{$boundary}\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($html)) . "\r\n"
        . "--{$boundary}--\r\n";

    $ok = @mail($to, $encoded_subject, $body, implode("\r\n", $headers));
    if (!$ok) {
        error_log('[mail] gagal kirim ke ' . $to . ' subj=' . $subject);
    }
    return (bool) $ok;
}

function send_subscription_activated_email(
    string $to_email,
    string $client_name,
    string $plan_name,
    string $interval_label
): bool {
    $dash = app_site_url() . '/dashboard.php';
    $body = '<p style="margin:0 0 12px;color:#cbd5e1;">Halo <strong style="color:#f8fafc;">' . htmlspecialchars($client_name, ENT_QUOTES, 'UTF-8') . '</strong>,</p>'
        . '<p style="margin:0 0 12px;">Pembayaran Anda berhasil. Langganan <strong style="color:#00E59A;">' . htmlspecialchars($plan_name, ENT_QUOTES, 'UTF-8') . '</strong>'
        . ($interval_label !== '' ? ' (' . htmlspecialchars($interval_label, ENT_QUOTES, 'UTF-8') . ')' : '')
        . ' sekarang <strong style="color:#f8fafc;">aktif</strong>.</p>'
        . mail_info_box(
            '<strong style="color:#00E59A;">Yang berubah:</strong><ul style="margin:8px 0 0;padding-left:18px;">'
            . '<li>Watermark di widget dihilangkan</li>'
            . '<li>Widget tetap aktif di website Anda</li>'
            . '<li>Kelola langganan kapan saja dari dashboard</li></ul>'
        )
        . mail_button($dash, 'Buka Dashboard');

    $html = mail_html_layout('Langganan Aktif', $body, 'Pembayaran berhasil — langganan Anda aktif.');
    $plain = "Langganan {$plan_name} aktif.\n\nBuka dashboard: {$dash}\n\n— " . APP_NAME;

    return send_html_email($to_email, 'Langganan Aktif — ' . APP_NAME, $html, $plain);
}

function send_subscription_cancelled_email(
    string $to_email,
    string $client_name,
    ?string $ends_at_human
): bool {
    $billing = app_site_url() . '/billing.php';
    $ends = $ends_at_human !== null && $ends_at_human !== ''
        ? '<p style="margin:0;">Akses berbayar berakhir pada: <strong style="color:#f8fafc;">' . htmlspecialchars($ends_at_human, ENT_QUOTES, 'UTF-8') . '</strong>. Setelah itu widget kembali nonaktif atau trial dengan watermark.</p>'
        : '<p style="margin:0;">Langganan Anda telah dibatalkan. Widget dapat berhenti berfungsi setelah periode berjalan habis.</p>';

    $body = '<p style="margin:0 0 12px;color:#cbd5e1;">Halo <strong style="color:#f8fafc;">' . htmlspecialchars($client_name, ENT_QUOTES, 'UTF-8') . '</strong>,</p>'
        . '<p style="margin:0 0 12px;">Kami mengonfirmasi <strong style="color:#f8fafc;">pembatalan langganan</strong> Anda.</p>'
        . mail_info_box($ends)
        . '<p style="margin:16px 0 0;color:#94a3b8;font-size:14px;">Ingin tetap menggunakan tanpa watermark? Anda dapat berlangganan lagi kapan saja.</p>'
        . mail_button($billing, 'Kelola / Berlangganan Lagi');

    $html = mail_html_layout('Langganan Dibatalkan', $body, 'Langganan Anda telah dibatalkan.');
    $plain = "Langganan dibatalkan.\n\nKelola: {$billing}\n\n— " . APP_NAME;

    return send_html_email($to_email, 'Langganan Dibatalkan — ' . APP_NAME, $html, $plain);
}

function send_checkout_receipt_email(
    string $to_email,
    string $client_name,
    string $plan_name,
    string $amount_display
): bool {
    $dash = app_site_url() . '/dashboard.php';
    $body = '<p style="margin:0 0 12px;color:#cbd5e1;">Terima kasih, <strong style="color:#f8fafc;">' . htmlspecialchars($client_name, ENT_QUOTES, 'UTF-8') . '</strong>!</p>'
        . '<p style="margin:0 0 12px;">Kami menerima pembayaran Anda untuk paket <strong style="color:#00E59A;">' . htmlspecialchars($plan_name, ENT_QUOTES, 'UTF-8') . '</strong>.</p>'
        . mail_info_box('<table role="presentation" width="100%" style="font-size:14px;">'
            . '<tr><td style="color:#94a3b8;padding:4px 0;">Total</td><td align="right" style="color:#f8fafc;font-weight:700;">' . htmlspecialchars($amount_display, ENT_QUOTES, 'UTF-8') . '</td></tr>'
            . '<tr><td style="color:#94a3b8;padding:4px 0;">Status</td><td align="right" style="color:#00E59A;font-weight:700;">Lunas</td></tr></table>')
        . '<p style="margin:12px 0 0;color:#94a3b8;font-size:13px;">Invoice resmi dari Stripe akan dikirim terpisah ke email ini.</p>'
        . mail_button($dash, 'Mulai Konfigurasi Widget');

    $html = mail_html_layout('Konfirmasi Pembayaran', $body, 'Terima kasih — pembayaran Anda diterima.');
    $plain = "Pembayaran {$plan_name} ({$amount_display}) diterima.\nDashboard: {$dash}\n\n— " . APP_NAME;

    return send_html_email($to_email, 'Konfirmasi Pembayaran — ' . APP_NAME, $html, $plain);
}

function send_password_reset_email_html(string $to_email, string $reset_link): bool
{
    $body = '<p style="margin:0 0 12px;color:#cbd5e1;">Kami menerima permintaan reset password untuk akun Anda.</p>'
        . '<p style="margin:0 0 12px;color:#94a3b8;font-size:14px;">Link berlaku <strong style="color:#f8fafc;">60 menit</strong>. Jika Anda tidak meminta ini, abaikan email ini.</p>'
        . mail_button($reset_link, 'Reset Password')
        . '<p style="margin:16px 0 0;font-size:12px;color:#64748b;word-break:break-all;">' . htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8') . '</p>';

    $html = mail_html_layout('Reset Password', $body, 'Reset password akun ChatPopup.AI Anda.');
    $plain = "Reset password (60 menit):\n{$reset_link}\n\n— " . APP_NAME;

    return send_html_email($to_email, 'Reset Password — ' . APP_NAME, $html, $plain);
}
