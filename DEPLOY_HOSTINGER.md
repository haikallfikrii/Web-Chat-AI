# Deploy ChatLM — Staging & Production (Hostinger + GitHub)

Panduan memisahkan **2 database**, **2 domain**, dan **2 branch Git** tanpa saling menimpa saat `git pull`.

| Lingkungan | Domain | Branch Git | Database (contoh nama) |
|------------|--------|------------|-------------------------|
| **Staging** | `staging.chatlm.tech` | `staging` | `uXXX_chatlm_staging` |
| **Production** | `chatlm.tech` | `production` | `u451240370_chatlm_prod` |
| Development | lokal | `main` | lokal / Docker |

---

## Prinsip penting

1. **`config.php`** — ada di Git (logika app, tanpa password).
2. **`config.local.php`** — **TIDAK** di Git; dibuat manual di tiap server.
3. Setiap `git pull` dari branch `staging` / `production` **tidak menghapus** `config.local.php`.
4. Staging dan production **wajib** punya database MySQL terpisah.

---

## Langkah 1 — Buat 2 database di Hostinger

Di **hPanel → Databases → MySQL Databases**:

### Database Staging
- Nama: mis. `u451240370_chatlm_staging`
- User: mis. `u451240370_stg_user`
- Password: buat password kuat
- Assign user ke database (All Privileges)

### Database Production
- Nama: mis. `u451240370_chatlm_prod`
- User: mis. `u451240370_prod_user`
- Password: **beda** dari staging
- Assign user ke database

Catat keempat nilai (host biasanya `localhost` atau `127.0.0.1`).

---

## Langkah 2 — Import struktur tabel (kedua database)

Untuk **masing-masing** database:

1. Buka **phpMyAdmin**
2. Klik database di sidebar kiri (staging ATAU production)
3. Tab **SQL**
4. Import / tempel isi file: **`schema/hostinger_install.sql`**
5. Klik **Execute**

Ulangi untuk database kedua. Jangan campur satu SQL untuk dua DB sekaligus.

> File `schema.sql` di root masih untuk development lokal (ada `CREATE DATABASE chatpopup_db`). Untuk Hostinger pakai **`schema/hostinger_install.sql`**.

---

## Langkah 3 — Deploy kode dari GitHub

### Staging server (`staging.chatlm.tech`)
- Git deploy branch: **`staging`**
- Document root: folder website staging

### Production server (`chatlm.tech`)
- Git deploy branch: **`production`**
- Document root: folder website production

Setelah pull pertama, **belum** akan jalan tanpa `config.local.php` (akan tampil pesan setup).

---

## Langkah 4 — Buat `config.local.php` di SETIAP server

File ini **tidak** ada di GitHub. Buat lewat File Manager Hostinger.

### Di server STAGING

1. Buka folder root website staging
2. Salin template:
   - `config.local.staging.example.php` → rename jadi **`config.local.php`**
3. Edit isi `config.local.php`:

```php
'DB_NAME' => 'u451240370_chatlm_staging',  // nama DB staging Anda
'DB_USER' => 'u451240370_stg_user',
'DB_PASS' => 'password_staging_anda',
'APP_SITE_URL' => 'https://staging.chatlm.tech',
'APP_ENV' => 'staging',
```

4. Isi `APP_SECRET` (min. 32 karakter acak, **beda** dari production)
5. Stripe: pakai key **`sk_test_` / `pk_test_`**
6. Webhook Stripe staging:
   ```
   https://staging.chatlm.tech/api/webhooks/stripe.php
   ```
7. Widget demo di beranda — set `LANDING_WIDGET_API_KEY` (API key 64 karakter dari dashboard)

### Widget tidak muncul di website / WordPress

1. **Dashboard → Allowed Origins** — isi URL situs pengunjung (bukan wp-admin saja), contoh:
   ```
   https://tokoanda.com, https://www.tokoanda.com
   ```
   Atau sementara `*` untuk uji. Simpan pengaturan.

2. **WordPress cache** (LiteSpeed, WP Rocket, dll.) — kosongkan cache setelah menambah script embed. Banyak kasus widget hanya terlihat saat admin karena halaman tamu masih cache lama tanpa script.

3. **Paste embed di footer global** (Appearance → Theme File Editor → `footer.php` sebelum `</body>`, atau plugin "Insert Headers and Footers") — jangan hanya di halaman draft/preview admin.

4. **chatlm.tech / staging** — pastikan `config.local.php` berisi `LANDING_WIDGET_API_KEY` yang sama dengan API key di dashboard, lalu `git pull` di server.

5. Buka **DevTools → Console** di halaman tamu: jika ada error CORS / `Origin not allowed`, tambahkan domain persis dari error ke Allowed Origins.

6. Pastikan **AI API key** + model sudah diisi di dashboard (widget tampil, chat bisa gagal tanpa ini).

### Di server PRODUCTION

1. Salin `config.local.production.example.php` → **`config.local.php`**
2. Edit dengan kredensial DB **production**
3. `APP_SITE_URL` => `https://chatlm.tech`
4. `APP_ENV` => `production`
5. `APP_SECRET` => **secret lain** (jangan sama dengan staging)
6. Stripe: **`sk_live_` / `pk_live_`**
7. Webhook production:
   ```
   https://chatlm.tech/api/webhooks/stripe.php
   ```

---

## Langkah 5 — Verifikasi

Buka di browser (sementara, untuk debug):

```
https://staging.chatlm.tech/health.php
https://chatlm.tech/health.php
```

Harus JSON seperti:

```json
{
  "ok": true,
  "app_env": "staging",
  "db_name": "u451240370_chatlm_staging",
  "db_connected": true,
  "tables_ok": true
}
```

Lalu:
- Daftar akun baru di staging → cek phpMyAdmin **database staging** (bukan production)
- Login dashboard staging

**Setelah OK**, hapus `health.php` di production atau blokir akses publik.

---

## Workflow Git sehari-hari

```
main        → development lokal
staging     → push → auto deploy staging.chatlm.tech → DB staging
production  → merge dari staging (setelah uji) → deploy chatlm.tech → DB production
```

Saat menambah kolom/tabel baru:
1. Tulis file `schema_migration_vX.sql`
2. Jalankan di phpMyAdmin **staging** dulu
3. Tes aplikasi di staging
4. Jalankan SQL yang sama di phpMyAdmin **production**

---

## Local development

```bash
cp config.local.staging.example.php config.local.php
# atau buat DB lokal dan sesuaikan DB_*
```

Atau gunakan file `.env` (juga di-ignore Git):

```bash
cp .env.example .env
```

---

## Troubleshooting

| Gejala | Penyebab | Solusi |
|--------|----------|--------|
| "setup diperlukan" / 503 | `config.local.php` belum ada | Buat file di server |
| DB connection failed | Nama DB/user/salah password | Cek hPanel + config.local.php |
| Data staging muncul di production | Satu DB dipakai dua domain | Pisah DB_NAME di config masing-masing server |
| config hilang setelah deploy | config.local.php ikut kehapus | Pastikan ada di .gitignore; jangan commit config.local.php |
| APP_SECRET error decrypt | Secret beda setelah ganti | Re-encrypt AI keys di dashboard |

---

## Checklist cepat

- [ ] DB staging dibuat + `hostinger_install.sql` di-import
- [ ] DB production dibuat + `hostinger_install.sql` di-import
- [ ] `config.local.php` di server staging (DB staging)
- [ ] `config.local.php` di server production (DB production)
- [ ] `APP_SECRET` berbeda antara staging & production
- [ ] Stripe test webhook → staging URL
- [ ] Stripe live webhook → production URL
- [ ] `check-config.php` OK di kedua domain
- [ ] Registrasi tes di staging tidak muncul di DB production
