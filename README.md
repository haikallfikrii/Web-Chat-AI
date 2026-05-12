# SaaS Chat Pop-up Widget

Chat pop-up berbasis PHP/MySQL yang dapat di-host di Shared Hosting (Hostinger, Niagahoster, dll), terintegrasi dengan n8n untuk AI conversational.

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

## Konfigurasi Environment

Edit `config.php` atau set environment variable di server:

| Variable      | Keterangan                        | Default          |
|---------------|-----------------------------------|------------------|
| `DB_HOST`     | Host MySQL                        | `localhost`      |
| `DB_PORT`     | Port MySQL                        | `3306`           |
| `DB_NAME`     | Nama database                     | `chatpopup_db`   |
| `DB_USER`     | Username MySQL                    | `root`           |
| `DB_PASS`     | Password MySQL                    | *(kosong)*       |
| `APP_ENV`     | `development` / `production`      | `production`     |
| `APP_SECRET`  | Secret key aplikasi               | *(ganti ini!)*   |
| `WEBHOOK_TIMEOUT` | Timeout cURL ke n8n (detik) | `30`             |

> **Shared Hosting**: Jika tidak bisa set env var, edit konstanta langsung di `config.php`.

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
