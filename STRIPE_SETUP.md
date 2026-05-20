# Panduan Setup Stripe — ChatPopup.AI

Dokumen ini menjelaskan langkah demi langkah mengaktifkan pembayaran langganan (subscription) untuk ChatPopup.AI.

---

## Ringkasan arsitektur

| Komponen | File / URL |
|----------|------------|
| Halaman paket | `/pricing.php` |
| Konfirmasi checkout | `/checkout.php` |
| Sukses / batal | `/billing-success.php`, `/billing-cancel.php` |
| Kelola langganan | `/billing.php` (Stripe Customer Portal) |
| Webhook Stripe | `https://domain-anda.com/api/webhooks/stripe.php` |
| Watermark trial/free | API `get-settings.php` + `widget.js` |

---

## Langkah 1 — Buat akun Stripe

1. Buka [https://dashboard.stripe.com/register](https://dashboard.stripe.com/register)
2. Lengkapi verifikasi bisnis (nama, negara, rekening bank)
3. Untuk **target global + Indonesia**: daftar entity sesuai domisili Anda; Stripe mendukung kartu internasional dan banyak metode lokal per negara

**Mode Test vs Live**

- **Test mode** (toggle di dashboard): untuk development, kartu uji `4242 4242 4242 4242`
- **Live mode**: untuk production setelah website siap

---

## Langkah 2 — Buat produk & harga (Price)

Di Stripe Dashboard → **Product catalog** → **Add product**

Buat **4 harga berlangganan** (atau sesuaikan dengan kebutuhan):

| Paket di app | Nama produk | Interval | Contoh harga |
|--------------|-------------|----------|--------------|
| `starter_monthly` | ChatPopup Starter | Monthly | $19 |
| `pro_monthly` | ChatPopup Pro | Monthly | $49 |
| `starter_yearly` | ChatPopup Starter Annual | Yearly | $190 |
| `pro_yearly` | ChatPopup Pro Annual | Yearly | $490 |

Untuk setiap harga, setelah dibuat salin **Price ID** (format `price_1ABC...`).

---

## Langkah 3 — API Keys

Stripe Dashboard → **Developers** → **API keys**

| Key | Env variable | Keterangan |
|-----|--------------|------------|
| Publishable key | `STRIPE_PUBLISHABLE_KEY` | `pk_test_...` atau `pk_live_...` |
| Secret key | `STRIPE_SECRET_KEY` | `sk_test_...` atau `sk_live_...` — **rahasia** |

Jangan commit secret key ke Git.

---

## Langkah 4 — Webhook

Stripe Dashboard → **Developers** → **Webhooks** → **Add endpoint**

**Endpoint URL:**

```
https://agent.jomsite.com/api/webhooks/stripe.php
```

(Ganti dengan domain production Anda.)

**Events to send** (pilih minimal):

- `checkout.session.completed`
- `customer.subscription.updated`
- `customer.subscription.deleted`

Setelah dibuat, buka endpoint → **Signing secret** → salin ke env:

```
STRIPE_WEBHOOK_SECRET=whsec_...
```

**Testing lokal (opsional):**

```bash
stripe listen --forward-to localhost:8080/api/webhooks/stripe.php
```

Gunakan signing secret dari output CLI untuk development.

---

## Langkah 5 — Customer Portal (pembatalan & invoice)

Stripe Dashboard → **Settings** → **Billing** → **Customer portal**

Aktifkan:

- Cancel subscriptions
- Update payment methods
- View invoice history

Return URL otomatis diarahkan ke `/billing.php` oleh kode aplikasi.

Saat user membatalkan di portal, Stripe mengirim webhook `customer.subscription.deleted` → status client jadi `inactive` + **email HTML pembatalan**.

---

## Langkah 6 — Environment variables di server

Di Hostinger (atau panel hosting): **Advanced** → **Environment variables**, atau file `.env` di luar `public_html`:

```bash
# URL situs (wajib untuk redirect Stripe & watermark)
APP_SITE_URL=https://agent.jomsite.com
APP_NAME=ChatPopup.AI

# Stripe
STRIPE_SECRET_KEY=sk_live_xxxxxxxx
STRIPE_PUBLISHABLE_KEY=pk_live_xxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxx

STRIPE_PRICE_STARTER_MONTHLY=price_xxxxxxxx
STRIPE_PRICE_PRO_MONTHLY=price_xxxxxxxx
STRIPE_PRICE_STARTER_YEARLY=price_xxxxxxxx
STRIPE_PRICE_PRO_YEARLY=price_xxxxxxxx

# Email (From untuk notifikasi)
MAIL_FROM_ADDRESS=billing@agent.jomsite.com
MAIL_FROM_NAME=ChatPopup.AI
MAIL_SUPPORT=support@email-anda.com

# Trial hari (default 14)
TRIAL_DAYS=14
```

Copy dari `.env.example` di root proyek.

---

## Langkah 7 — Migrasi database

Jalankan SQL di phpMyAdmin:

```
schema_migration_v5_billing.sql
```

Ini menambah kolom `plan_code`, `stripe_customer_id`, `stripe_subscription_id`, `trial_ends_at`, dll. dan tabel `stripe_webhook_events`.

Jika `ALTER TABLE` error "duplicate column", kolom sudah ada — lanjutkan ke tabel webhook saja.

---

## Langkah 8 — Email transaksional

Aplikasi memakai PHP `mail()`. Untuk deliverability production disarankan:

- **SMTP Hostinger** (Email → akun → SMTP)
- Atau **Resend / SendGrid / Amazon SES** (butuh patch ke `includes/mail.php` jika ingin API)

Pastikan SPF/DKIM domain sudah diverifikasi di panel DNS.

Email yang dikirim otomatis:

| Event | Subject |
|-------|---------|
| Checkout sukses | Konfirmasi Pembayaran — ChatPopup.AI |
| Langganan aktif | Langganan Aktif — ChatPopup.AI |
| Langganan dibatalkan | Langganan Dibatalkan — ChatPopup.AI |
| Reset password | Reset Password — ChatPopup.AI (HTML) |

---

## Langkah 9 — Uji end-to-end (Test mode)

1. Set semua key `sk_test_` / `pk_test_` / `whsec_` dari mode test
2. Login dashboard → **Lihat Paket** → pilih Starter → **Bayar dengan Stripe**
3. Kartu test: `4242 4242 4242 4242`, expiry masa depan, CVC sembarang
4. Setelah redirect sukses, cek:
   - Status badge dashboard = **Active**
   - Widget tanpa watermark "Powered by..."
   - Email masuk inbox (atau spam)
5. Di Stripe → Webhooks → lihat event `checkout.session.completed` status **200**

**Uji pembatalan:**

1. Buka `/billing.php` → **Stripe Customer Portal**
2. Cancel subscription
3. Cek email pembatalan + status `inactive` setelah webhook

---

## Langkah 10 — Go Live

1. Ganti semua key ke **Live mode**
2. Buat ulang webhook endpoint dengan URL production + live signing secret
3. Buat produk/harga di live (Price ID baru — update env)
4. Uji satu pembayaran real kecil ($19) sebelum promosi

---

## Paket & watermark

| Paket | Harga | Watermark | Status DB |
|-------|-------|-----------|-----------|
| Free | $0 | Ya, link ke `APP_SITE_URL` | `trial` |
| Trial (daftar baru) | $0 | Ya | `trial` + `trial_ends_at` |
| Starter / Pro (bayar) | Stripe | Tidak | `active` |
| Kedaluwarsa / batal | — | — | `inactive` (chat diblokir) |

Watermark teks: **Powered by ChatPopup.AI** → link ke website Anda.

---

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| "Stripe belum dikonfigurasi" | `STRIPE_SECRET_KEY` kosong atau bukan `sk_` |
| "Price ID belum dikonfigurasi" | Isi `STRIPE_PRICE_*` di env |
| Webhook 400 signature | `STRIPE_WEBHOOK_SECRET` salah atau body ter-modifikasi proxy |
| Bayar sukses tapi status trial | Webhook tidak sampai — cek URL HTTPS & event subscribed |
| Email tidak masuk | Cek spam, SPF, atau gunakan SMTP |
| SQL error plan_code | Jalankan `schema_migration_v5_billing.sql` |

---

## Keamanan

- Simpan `STRIPE_SECRET_KEY` dan `STRIPE_WEBHOOK_SECRET` hanya di server
- Webhook memverifikasi signature Stripe (anti spoof)
- Jangan expose secret key di frontend — hanya `pk_` jika nanti pakai Elements

---

## Kontak

Dukungan billing: set `MAIL_SUPPORT` di environment variables.
