<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/plans.php';
require_once __DIR__ . '/brand.php';
require_once __DIR__ . '/i18n_mail.php';

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
 * Template HTML email dengan identitas ChatLM. $lang menentukan bahasa footer/preheader/dir.
 */
function mail_html_layout(string $title, string $body_html, ?string $preheader = null, string $lang = 'en'): string
{
    $mt     = mail_strings($lang);
    $site   = app_site_url();
    $brand  = APP_NAME;
    $logo   = brand_logo_url();
    $year   = (string) date('Y');
    $logo_html = '<img src="' . htmlspecialchars($logo, ENT_QUOTES, 'UTF-8') . '" alt="" width="36" height="36" style="vertical-align:middle;margin-right:10px;border-radius:8px;">';
    $pre    = $preheader !== null && $preheader !== ''
        ? '<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;">' . htmlspecialchars($preheader, ENT_QUOTES, 'UTF-8') . '</div>'
        : '';
    $rights = sprintf($mt['footer_rights'], (int) $year, $brand);

    return '<!DOCTYPE html>
<html lang="' . htmlspecialchars($mt['html_lang'], ENT_QUOTES, 'UTF-8') . '">
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
<tr><td style="padding:28px 32px 20px;background:linear-gradient(135deg,#14B8A6 0%,#2DD4BF 100%);">
<table role="presentation" width="100%"><tr>
<td style="font-size:22px;font-weight:800;color:#031018;letter-spacing:-.3px;">' . $logo_html . htmlspecialchars($brand, ENT_QUOTES, 'UTF-8') . '</td>
<td align="right" style="font-size:12px;color:rgba(3,16,24,.65);font-weight:600;">' . htmlspecialchars($mt['header_tag'], ENT_QUOTES, 'UTF-8') . '</td>
</tr></table>
</td></tr>
<tr><td style="padding:32px;color:#e2e8f0;font-size:15px;line-height:1.65;">
<h1 style="margin:0 0 16px;font-size:22px;font-weight:800;color:#f8fafc;letter-spacing:-.3px;">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>
' . $body_html . '
</td></tr>
<tr><td style="padding:20px 32px 28px;border-top:1px solid rgba(255,255,255,.06);font-size:12px;color:#64748b;line-height:1.5;">
<p style="margin:0 0 8px;">' . htmlspecialchars($rights, ENT_QUOTES, 'UTF-8') . '</p>
<p style="margin:0;"><a href="' . htmlspecialchars($site, ENT_QUOTES, 'UTF-8') . '" style="color:#14B8A6;text-decoration:none;">' . htmlspecialchars($site, ENT_QUOTES, 'UTF-8') . '</a>
 &nbsp;&middot;&nbsp; <a href="mailto:' . htmlspecialchars(mail_support_address(), ENT_QUOTES, 'UTF-8') . '" style="color:#94a3b8;text-decoration:none;">' . htmlspecialchars($mt['footer_support'], ENT_QUOTES, 'UTF-8') . '</a></p>
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
<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;padding:14px 28px;background:linear-gradient(135deg,#14B8A6,#2DD4BF);color:#031018;font-weight:700;font-size:15px;text-decoration:none;border-radius:10px;">'
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
        'X-Mailer: ' . APP_NAME,
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
    string $interval_label,
    string $lang = 'en'
): bool {
    $mt   = mail_strings($lang);
    $dash = app_site_url() . '/dashboard.php';
    $interval_suffix = $interval_label !== '' ? ' (' . htmlspecialchars($interval_label, ENT_QUOTES, 'UTF-8') . ')' : '';

    $body = '<p style="margin:0 0 12px;color:#cbd5e1;">' . sprintf(htmlspecialchars($mt['greeting'], ENT_QUOTES, 'UTF-8'), '<strong style="color:#f8fafc;">' . htmlspecialchars($client_name, ENT_QUOTES, 'UTF-8') . '</strong>') . '</p>'
        . '<p style="margin:0 0 12px;">' . sprintf($mt['active_line1'], htmlspecialchars($plan_name, ENT_QUOTES, 'UTF-8'), $interval_suffix) . '</p>'
        . mail_info_box(
            '<strong style="color:#14B8A6;">' . htmlspecialchars($mt['active_changed'], ENT_QUOTES, 'UTF-8') . '</strong><ul style="margin:8px 0 0;padding-left:18px;">'
            . '<li>' . htmlspecialchars($mt['active_item1'], ENT_QUOTES, 'UTF-8') . '</li>'
            . '<li>' . htmlspecialchars($mt['active_item2'], ENT_QUOTES, 'UTF-8') . '</li>'
            . '<li>' . htmlspecialchars($mt['active_item3'], ENT_QUOTES, 'UTF-8') . '</li></ul>'
        )
        . mail_button($dash, $mt['btn_dashboard']);

    $html = mail_html_layout($mt['active_title'], $body, $mt['active_preheader'], $lang);
    $plain = sprintf($mt['plain_active'], $plan_name, $dash, APP_NAME);

    return send_html_email($to_email, sprintf($mt['subject_active'], APP_NAME), $html, $plain);
}

function send_subscription_cancelled_email(
    string $to_email,
    string $client_name,
    ?string $ends_at_human,
    string $lang = 'en'
): bool {
    $mt      = mail_strings($lang);
    $billing = app_site_url() . '/billing.php';
    $ends = $ends_at_human !== null && $ends_at_human !== ''
        ? '<p style="margin:0;">' . sprintf($mt['cancel_ends'], htmlspecialchars($ends_at_human, ENT_QUOTES, 'UTF-8')) . '</p>'
        : '<p style="margin:0;">' . htmlspecialchars($mt['cancel_ended'], ENT_QUOTES, 'UTF-8') . '</p>';

    $body = '<p style="margin:0 0 12px;color:#cbd5e1;">' . sprintf(htmlspecialchars($mt['greeting'], ENT_QUOTES, 'UTF-8'), '<strong style="color:#f8fafc;">' . htmlspecialchars($client_name, ENT_QUOTES, 'UTF-8') . '</strong>') . '</p>'
        . '<p style="margin:0 0 12px;">' . $mt['cancel_confirm'] . '</p>'
        . mail_info_box($ends)
        . '<p style="margin:16px 0 0;color:#94a3b8;font-size:14px;">' . htmlspecialchars($mt['cancel_note'], ENT_QUOTES, 'UTF-8') . '</p>'
        . mail_button($billing, $mt['btn_manage']);

    $html = mail_html_layout($mt['cancel_title'], $body, $mt['cancel_preheader'], $lang);
    $plain = sprintf($mt['plain_cancel'], $billing, APP_NAME);

    return send_html_email($to_email, sprintf($mt['subject_cancel'], APP_NAME), $html, $plain);
}

function send_checkout_receipt_email(
    string $to_email,
    string $client_name,
    string $plan_name,
    string $amount_display,
    string $lang = 'en'
): bool {
    $mt   = mail_strings($lang);
    $dash = app_site_url() . '/dashboard.php';

    $body = '<p style="margin:0 0 12px;color:#cbd5e1;">' . sprintf($mt['receipt_thanks'], htmlspecialchars($client_name, ENT_QUOTES, 'UTF-8')) . '</p>'
        . '<p style="margin:0 0 12px;">' . sprintf($mt['receipt_received'], htmlspecialchars($plan_name, ENT_QUOTES, 'UTF-8')) . '</p>'
        . mail_info_box('<table role="presentation" width="100%" style="font-size:14px;">'
            . '<tr><td style="color:#94a3b8;padding:4px 0;">' . htmlspecialchars($mt['receipt_total'], ENT_QUOTES, 'UTF-8') . '</td><td align="right" style="color:#f8fafc;font-weight:700;">' . htmlspecialchars($amount_display, ENT_QUOTES, 'UTF-8') . '</td></tr>'
            . '<tr><td style="color:#94a3b8;padding:4px 0;">' . htmlspecialchars($mt['receipt_status'], ENT_QUOTES, 'UTF-8') . '</td><td align="right" style="color:#14B8A6;font-weight:700;">' . htmlspecialchars($mt['receipt_paid'], ENT_QUOTES, 'UTF-8') . '</td></tr></table>')
        . '<p style="margin:12px 0 0;color:#94a3b8;font-size:13px;">' . htmlspecialchars($mt['receipt_note'], ENT_QUOTES, 'UTF-8') . '</p>'
        . mail_button($dash, $mt['btn_configure']);

    $html = mail_html_layout($mt['receipt_title'], $body, $mt['receipt_preheader'], $lang);
    $plain = sprintf($mt['plain_receipt'], $plan_name, $amount_display, $dash, APP_NAME);

    return send_html_email($to_email, sprintf($mt['subject_receipt'], APP_NAME), $html, $plain);
}

function send_password_reset_email_html(string $to_email, string $reset_link, string $lang = 'en'): bool
{
    $mt = mail_strings($lang);
    $body = '<p style="margin:0 0 12px;color:#cbd5e1;">' . htmlspecialchars($mt['reset_line1'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p style="margin:0 0 12px;color:#94a3b8;font-size:14px;">' . $mt['reset_line2'] . '</p>'
        . mail_button($reset_link, $mt['btn_reset'])
        . '<p style="margin:16px 0 0;font-size:12px;color:#64748b;word-break:break-all;">' . htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8') . '</p>';

    $html = mail_html_layout($mt['reset_title'], $body, sprintf($mt['reset_preheader'], APP_NAME), $lang);
    $plain = sprintf($mt['plain_reset'], $reset_link, APP_NAME);

    return send_html_email($to_email, sprintf($mt['subject_reset'], APP_NAME), $html, $plain);
}

/**
 * Kirim email selamat datang + info trial gratis setelah registrasi.
 */
function send_welcome_email(
    string $to_email,
    string $client_name,
    int $trial_days,
    string $lang = 'en'
): bool {
    $mt   = mail_strings($lang);
    $dash = app_site_url() . '/dashboard.php';
    $docs = app_site_url() . '/docs.php';

    $body = '<p style="margin:0 0 12px;color:#cbd5e1;">'
        . sprintf(htmlspecialchars($mt['greeting'], ENT_QUOTES, 'UTF-8'), '<strong style="color:#f8fafc;">' . htmlspecialchars($client_name, ENT_QUOTES, 'UTF-8') . '</strong>')
        . '</p>'
        . '<p style="margin:0 0 12px;">' . sprintf($mt['welcome_line1'], '<strong style="color:#14B8A6;">' . APP_NAME . '</strong>') . '</p>'
        . mail_info_box(
            '<strong style="color:#14B8A6;">' . sprintf(htmlspecialchars($mt['welcome_trial'], ENT_QUOTES, 'UTF-8'), $trial_days) . '</strong>'
            . '<p style="margin:8px 0 0;color:#cbd5e1;">' . htmlspecialchars($mt['welcome_trial_desc'], ENT_QUOTES, 'UTF-8') . '</p>'
        )
        . '<p style="margin:16px 0 8px;color:#e2e8f0;">' . htmlspecialchars($mt['welcome_steps'], ENT_QUOTES, 'UTF-8') . '</p>'
        . '<ol style="margin:0 0 16px;padding-left:20px;color:#94a3b8;">'
        . '<li style="margin-bottom:6px;">' . htmlspecialchars($mt['welcome_step1'], ENT_QUOTES, 'UTF-8') . '</li>'
        . '<li style="margin-bottom:6px;">' . htmlspecialchars($mt['welcome_step2'], ENT_QUOTES, 'UTF-8') . '</li>'
        . '<li style="margin-bottom:6px;">' . htmlspecialchars($mt['welcome_step3'], ENT_QUOTES, 'UTF-8') . '</li>'
        . '</ol>'
        . mail_button($dash, $mt['btn_start'])
        . '<p style="margin:16px 0 0;font-size:13px;color:#64748b;">'
        . sprintf($mt['welcome_docs'], '<a href="' . htmlspecialchars($docs, ENT_QUOTES, 'UTF-8') . '" style="color:#14B8A6;text-decoration:none;">' . htmlspecialchars($mt['welcome_docs_link'], ENT_QUOTES, 'UTF-8') . '</a>')
        . '</p>';

    $html = mail_html_layout($mt['welcome_title'], $body, sprintf($mt['welcome_preheader'], APP_NAME, $trial_days), $lang);
    $plain = sprintf($mt['plain_welcome'], $client_name, APP_NAME, $trial_days, $dash);

    return send_html_email($to_email, sprintf($mt['subject_welcome'], APP_NAME), $html, $plain);
}

/**
 * Kirim email reminder 3 hari sebelum trial habis.
 */
function send_trial_expiring_email(
    string $to_email,
    string $client_name,
    int $days_left,
    string $lang = 'en'
): bool {
    $mt      = mail_strings($lang);
    $pricing = app_site_url() . '/pricing.php';
    $dash    = app_site_url() . '/dashboard.php';

    $body = '<p style="margin:0 0 12px;color:#cbd5e1;">'
        . sprintf(htmlspecialchars($mt['greeting'], ENT_QUOTES, 'UTF-8'), '<strong style="color:#f8fafc;">' . htmlspecialchars($client_name, ENT_QUOTES, 'UTF-8') . '</strong>')
        . '</p>'
        . '<p style="margin:0 0 12px;">' . sprintf($mt['trial_exp_line1'], '<strong style="color:#fbbf24;">' . $days_left . '</strong>') . '</p>'
        . mail_info_box(
            '<strong style="color:#fbbf24;">' . htmlspecialchars($mt['trial_exp_warn'], ENT_QUOTES, 'UTF-8') . '</strong>'
            . '<ul style="margin:8px 0 0;padding-left:18px;color:#cbd5e1;">'
            . '<li>' . htmlspecialchars($mt['trial_exp_item1'], ENT_QUOTES, 'UTF-8') . '</li>'
            . '<li>' . htmlspecialchars($mt['trial_exp_item2'], ENT_QUOTES, 'UTF-8') . '</li>'
            . '</ul>'
        )
        . '<p style="margin:16px 0 0;color:#e2e8f0;">' . htmlspecialchars($mt['trial_exp_cta'], ENT_QUOTES, 'UTF-8') . '</p>'
        . mail_button($pricing, $mt['btn_upgrade'])
        . '<p style="margin:16px 0 0;font-size:13px;color:#64748b;">'
        . sprintf($mt['trial_exp_note'], '<a href="' . htmlspecialchars($dash, ENT_QUOTES, 'UTF-8') . '" style="color:#14B8A6;text-decoration:none;">' . htmlspecialchars($mt['trial_exp_dash'], ENT_QUOTES, 'UTF-8') . '</a>')
        . '</p>';

    $html = mail_html_layout($mt['trial_exp_title'], $body, sprintf($mt['trial_exp_preheader'], $days_left), $lang);
    $plain = sprintf($mt['plain_trial_exp'], $client_name, $days_left, $pricing);

    return send_html_email($to_email, sprintf($mt['subject_trial_exp'], APP_NAME, $days_left), $html, $plain);
}
