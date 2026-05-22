# ChatLM — AI Chat Widget

ChatLM (`chatlm.tech`) — widget chat AI berbasis PHP/MySQL untuk shared hosting, multi-provider AI, billing Stripe, dan branding widget.

## Struktur Project

```
/
├── config.php               # Koneksi DB & helper global
├── schema.sql               # Skema database MySQL
├── .htaccess                # Keamanan & optimasi Apache
│
├── api/
│   ├── .htaccess            # Keamanan folder API
│   ├── get-settings.php     # GET pengaturan widget
│   └── chat.php             # POST pesan → n8n → balasan
│
└── widget/
    └── widget.js            # Script yang di-embed di situs klien
```

## Alur Kerja

```
Website Klien
  └─ <script src="widget.js" data-api-key="..." data-base-url="...">
        │
        ├── GET /api/get-settings.php   (load warna, nama bot, welcome msg)
        │
        └── POST /api/chat.php          (kirim pesan user)
                │
                └── cURL → n8n Webhook (dengan session_id untuk memori AI)
                              │
                              └── Jawaban AI → PHP → Widget
```

## Setup Database

1. Buat database baru di cPanel/phpMyAdmin.
2. Import `schema.sql`.
3. Salin `api_key` dari tabel `clients` untuk diberikan ke klien.

## Staging vs Production (Hostinger)

**Jangan** simpan password database di Git. Pakai `config.local.php` per server:

| Server | Domain | File config | Database |
|--------|--------|-------------|----------|
| Staging | `staging.chatlm.tech` | `config.local.php` (dari `config.local.staging.example.php`) | DB staging terpisah |
| Production | `chatlm.tech` | `config.local.php` (dari `config.local.production.example.php`) | DB production terpisah |

Panduan lengkap: **[DEPLOY_HOSTINGER.md](DEPLOY_HOSTINGER.md)**

Import tabel baru: `schema/hostinger_install.sql` di phpMyAdmin (per database).

Cek koneksi: `/health.php` (hapus setelah setup di production).

| Variable | Keterangan |
|----------|------------|
| `DB_NAME` | Nama DB **berbeda** per lingkungan |
| `APP_ENV` | `staging` atau `production` |
| `APP_SITE_URL` | URL penuh domain tersebut |
| `APP_SECRET` | Min. 32 karakter, **beda** staging vs production |

## Embed Widget ke Website Klien

Tempel kode berikut sebelum tag `</body>`:

```html
<script
  src="https://yourdomain.com/widget/widget.js"
  data-api-key="API_KEY_64_KARAKTER_DISINI"
  data-base-url="https://yourdomain.com"
  async
></script>
```

## Konfigurasi n8n

### Format Payload yang Diterima n8n (dari PHP):
```json
{
  "session_id": "uuid-v4-unik-per-sesi",
  "message":    "Pesan dari user",
  "client_id":  1,
  "bot_name":   "Asisten Toko ABC"
}
```

### Format Response yang Diharapkan dari n8n:
n8n bisa mengembalikan salah satu format ini:

```json
{ "reply": "Jawaban dari AI" }
```
```json
{ "message": "Jawaban dari AI" }
```
```json
{ "output": "Jawaban dari AI" }
```
Atau plain text: `Jawaban dari AI`

### Tips n8n untuk Memori Percakapan:
- Gunakan node **"Chat Memory Buffer"** atau **"Postgres Chat Memory"**.
- Gunakan `{{ $json.session_id }}` sebagai `sessionId` di node memory n8n.
- Ini memungkinkan AI mengingat konteks percakapan sebelumnya.

## Fitur Keamanan

- **SQL Injection**: Semua query menggunakan PDO Prepared Statements.
- **XSS**: Output di-escape dengan `htmlspecialchars` dan `escapeHtml` (JS).
- **Shadow DOM**: CSS widget terisolasi — tidak bisa dirusak/merusak CSS host.
- **CORS**: Dikontrol per-klien via kolom `allowed_origins` di database.
- **API Key Validation**: Validasi format hex 64 karakter sebelum query DB.
- **Input Validation**: Batas panjang pesan, validasi UUID, sanitasi semua input.
- **cURL Safety**: SSL verify aktif, redirect diblokir, timeout ketat.
- **Log tanpa expose**: Error detail hanya di-log server, tidak ditampilkan ke user.

## Menambah Klien Baru

```sql
-- 1. Insert klien
INSERT INTO clients (name, email, api_key, subscription_status)
VALUES (
    'Nama Toko',
    'email@toko.com',
    SHA2(CONCAT('nama-toko', UUID(), RAND()), 256),
    'active'
);

-- 2. Insert pengaturan widget
INSERT INTO widget_settings
    (client_id, primary_color, bot_name, bot_avatar_url, welcome_message, n8n_webhook_url, allowed_origins)
VALUES (
    LAST_INSERT_ID(),
    '#10B981',
    'Asisten Toko',
    'https://toko.com/avatar.png',
    'Halo! Ada yang bisa saya bantu?',
    'https://n8n.yourdomain.com/webhook/YOUR-ID',
    'https://toko.com'
);

-- 3. Ambil api_key untuk diberikan ke klien
SELECT api_key FROM clients WHERE email = 'email@toko.com';
```

## Requirements Server

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Ekstensi PHP: `pdo`, `pdo_mysql`, `curl`, `json`, `mbstring`
- Apache dengan `mod_rewrite` aktif
