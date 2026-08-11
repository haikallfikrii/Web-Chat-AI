<?php
declare(strict_types=1);

/**
 * Klien SMTP minimal (tanpa dependensi eksternal).
 *
 * Dipakai karena mail() bawaan PHP tidak dapat diandalkan di shared hosting:
 * kegagalan sering tidak terdeteksi dan pesan kerap ditolak karena tidak
 * terautentikasi. Dengan SMTP berautentikasi, pengiriman bisa diverifikasi
 * dan alasan kegagalan terbaca di log.
 */

require_once __DIR__ . '/../config.php';

function smtp_configured(): bool
{
    return MAIL_SMTP_HOST !== '' && MAIL_SMTP_USER !== '' && MAIL_SMTP_PASS !== '';
}

/**
 * Baca satu respons SMTP (termasuk bentuk multi-baris "250-...\r\n250 ...").
 */
function smtp_read_response($socket, ?string &$raw = null): int
{
    $raw = '';

    while (($line = fgets($socket, 8192)) !== false) {
        $raw .= $line;

        // Baris terakhir memakai spasi setelah kode; tanda "-" berarti masih ada lanjutan.
        if (strlen($line) >= 4 && $line[3] === ' ') {
            break;
        }
    }

    if ($raw === '') {
        return 0;
    }

    return (int) substr($raw, 0, 3);
}

function smtp_command($socket, string $command, ?string &$raw = null): int
{
    fwrite($socket, $command . "\r\n");
    return smtp_read_response($socket, $raw);
}

/**
 * Kirim satu pesan RFC 5322 lengkap (header + baris kosong + body) via SMTP.
 *
 * @param list<string> $recipients
 */
function smtp_send_message(
    string $from,
    array $recipients,
    string $message,
    ?string &$error = null
): bool {
    $error = null;

    if (!smtp_configured()) {
        $error = 'SMTP belum dikonfigurasi.';
        return false;
    }

    $secure  = strtolower(MAIL_SMTP_SECURE);
    $host    = MAIL_SMTP_HOST;
    $port    = MAIL_SMTP_PORT;
    $timeout = MAIL_SMTP_TIMEOUT;
    $target  = ($secure === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;

    $context = stream_context_create([
        'ssl' => [
            'verify_peer'       => true,
            'verify_peer_name'  => true,
            'SNI_enabled'       => true,
        ],
    ]);

    $socket = @stream_socket_client(
        $target,
        $errno,
        $errstr,
        $timeout,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if (!$socket) {
        $error = sprintf('Tidak bisa terhubung ke %s (%d: %s)', $target, (int) $errno, (string) $errstr);
        return false;
    }

    stream_set_timeout($socket, $timeout);

    $close = static function ($sock): void {
        @fwrite($sock, "QUIT\r\n");
        @fclose($sock);
    };

    try {
        if (smtp_read_response($socket, $raw) !== 220) {
            $error = 'Sambutan server tidak valid: ' . trim((string) $raw);
            @fclose($socket);
            return false;
        }

        $ehloHost = parse_url(app_site_url(), PHP_URL_HOST) ?: 'localhost';

        if (smtp_command($socket, 'EHLO ' . $ehloHost, $raw) !== 250) {
            $error = 'EHLO ditolak: ' . trim((string) $raw);
            $close($socket);
            return false;
        }

        if ($secure === 'tls') {
            if (smtp_command($socket, 'STARTTLS', $raw) !== 220) {
                $error = 'STARTTLS ditolak: ' . trim((string) $raw);
                $close($socket);
                return false;
            }

            $crypto = @stream_socket_enable_crypto(
                $socket,
                true,
                STREAM_CRYPTO_METHOD_TLS_CLIENT
            );

            if ($crypto !== true) {
                $error = 'Gagal mengaktifkan TLS.';
                @fclose($socket);
                return false;
            }

            // Setelah TLS aktif, sesi harus diperkenalkan ulang.
            if (smtp_command($socket, 'EHLO ' . $ehloHost, $raw) !== 250) {
                $error = 'EHLO setelah STARTTLS ditolak: ' . trim((string) $raw);
                $close($socket);
                return false;
            }
        }

        if (smtp_command($socket, 'AUTH LOGIN', $raw) !== 334) {
            $error = 'Server menolak AUTH LOGIN: ' . trim((string) $raw);
            $close($socket);
            return false;
        }

        if (smtp_command($socket, base64_encode(MAIL_SMTP_USER), $raw) !== 334) {
            $error = 'Username ditolak: ' . trim((string) $raw);
            $close($socket);
            return false;
        }

        if (smtp_command($socket, base64_encode(MAIL_SMTP_PASS), $raw) !== 235) {
            $error = 'Autentikasi gagal: ' . trim((string) $raw);
            $close($socket);
            return false;
        }

        if (smtp_command($socket, 'MAIL FROM:<' . $from . '>', $raw) !== 250) {
            $error = 'MAIL FROM ditolak: ' . trim((string) $raw);
            $close($socket);
            return false;
        }

        $accepted = 0;
        foreach ($recipients as $rcpt) {
            $code = smtp_command($socket, 'RCPT TO:<' . $rcpt . '>', $raw);
            if ($code === 250 || $code === 251) {
                $accepted++;
            } else {
                error_log('[smtp] penerima ditolak ' . $rcpt . ': ' . trim((string) $raw));
            }
        }

        if ($accepted === 0) {
            $error = 'Semua penerima ditolak server.';
            $close($socket);
            return false;
        }

        if (smtp_command($socket, 'DATA', $raw) !== 354) {
            $error = 'DATA ditolak: ' . trim((string) $raw);
            $close($socket);
            return false;
        }

        // Titik di awal baris harus digandakan agar tidak dianggap akhir pesan.
        $payload = preg_replace('/^\./m', '..', str_replace("\r\n", "\n", $message));
        $payload = str_replace("\n", "\r\n", (string) $payload);

        fwrite($socket, $payload . "\r\n.\r\n");

        if (smtp_read_response($socket, $raw) !== 250) {
            $error = 'Pesan ditolak saat pengiriman: ' . trim((string) $raw);
            $close($socket);
            return false;
        }

        $close($socket);
        return true;
    } catch (Throwable $e) {
        $error = 'Kesalahan SMTP: ' . $e->getMessage();
        @fclose($socket);
        return false;
    }
}
