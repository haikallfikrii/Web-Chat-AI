# Strategi SEO ChatLM (chatlm.tech)

Panduan praktis untuk ranking organik, blog konten, dan Google Search Console.

---

## 1. Tujuan & positioning

**Produk:** Widget chat AI embed untuk website (multi-provider, branding, billing).

**Audiens utama:**
- Pemilik website / toko online / landing page
- Agency & freelancer yang pasang widget untuk klien
- Founder SaaS yang butuh support otomatis

**Value proposition di SERP:** Setup cepat (satu baris script), tanpa coding berat, multi bahasa, harga transparan.

---

## 2. Cluster keyword (prioritas)

Gunakan 1 halaman utama per cluster + artikel blog pendukung.

### Cluster A — Intent transaksional (prioritas tinggi)

| Keyword (EN) | Variasi ID | Halaman target |
|--------------|------------|----------------|
| AI chat widget for website | widget chat AI untuk website | `/` (landing) |
| embed AI chat on website | pasang chat AI di website | `/` + blog tutorial |
| website chatbot no code | chatbot website tanpa coding | `/register.php` CTA |
| ChatGPT widget for website | widget ChatGPT untuk website | blog + landing |
| AI customer support widget | widget support pelanggan AI | blog use-case |

### Cluster B — Komersial / perbandingan

| Keyword | Halaman |
|---------|---------|
| AI chat widget pricing | `/pricing.php` |
| Intercom alternative / Drift alternative | Blog comparison |
| free AI chat widget | `/pricing.php` (paket Free) |

### Cluster C — Edukasi / top of funnel

| Topik blog | Keyword contoh |
|------------|----------------|
| Cara pasang widget | how to add AI chat to WordPress / Shopify / Hostinger |
| Provider AI | OpenAI vs Gemini vs DeepSeek for website chat |
| Prompt & persona | best system prompt for website chatbot |
| Keamanan | is AI chat widget safe for customer data |
| Konversi | reduce support tickets with AI chat |

### Cluster D — Geo / bahasa (hreflang)

Target halaman yang sama dengan `?lang=`:
- EN: global
- ID: widget chat AI, chatbot website Indonesia
- ES/FR/PT/JA: terjemahan judul & meta lokal

---

## 3. Struktur situs (arsitektur)

```
/                    → Landing (pillar)
/pricing.php         → Komersial
/login.php           → noindex (opsional)
/register.php        → noindex (opsional)
/blog/               → Index artikel (buat folder)
/blog/{slug}.php     → Artikel
/docs/               → Dokumentasi embed (opsional, kuat untuk long-tail)
/sitemap.xml         → Semua URL publik
/robots.txt          → Allow /, disallow /dashboard, /api
```

**Halaman yang jangan di-index:** `dashboard.php`, `billing.php`, `checkout.php`, `api/*`, `health.php`.

---

## 4. Blog — kalender konten (12 minggu pertama)

| Minggu | Judul (contoh EN) | Slug | Keyword utama |
|--------|-------------------|------|---------------|
| 1 | How to Add an AI Chat Widget in 5 Minutes | add-ai-chat-widget-5-minutes | embed AI chat |
| 2 | ChatLM vs Intercom: Which Fits Small Sites? | chatlm-vs-intercom | intercom alternative |
| 3 | Best AI Providers for Your Website Chatbot | best-ai-providers-website-chat | openrouter openai gemini |
| 4 | WordPress: Install ChatLM Without a Plugin | chatlm-wordpress-embed | wordpress ai chat |
| 5 | 10 System Prompts for E-commerce Support | ecommerce-chatbot-prompts | chatbot prompts |
| 6 | Shopify & Custom Sites: One Script Tag | shopify-ai-chat-widget | shopify chatbot |
| 7 | Free Plan vs Pro: Watermark & Branding | free-vs-pro-ai-chat-widget | free ai chat widget |
| 8 | GDPR & Privacy: What Your Widget Stores | ai-chat-widget-privacy | chat widget gdpr |
| 9 | Reduce Support Tickets 40% with AI Chat | reduce-support-tickets-ai | customer support automation |
| 10 | Telegram Alerts When Visitors Chat | telegram-chat-notifications | telegram website chat |
| 11 | Hostinger Deploy Guide for ChatLM | deploy-chatlm-hostinger | hostinger php chat |
| 12 | Case Study: SaaS Landing Page + ChatLM | saas-landing-page-chat-case-study | saas chat widget |

**Format artikel:** 1.200–2.000 kata, H1 unik, H2/H3, screenshot, CTA ke register, internal link ke `/pricing.php`.

**Bahasa:** Publish EN dulu; versi ID bisa artikel ke-2 atau section terpisah `/blog/id/`.

---

## 5. On-page SEO (checklist per halaman)

### Landing `/`
- [ ] `<title>`: ChatLM — AI Chat Widget for Any Website | Free Trial
- [ ] `<meta name="description">` 150–160 karakter, ada CTA
- [ ] Satu `<h1>` (hero)
- [ ] Canonical: `https://chatlm.tech/`
- [ ] Open Graph + Twitter Card (og:image logo)
- [ ] JSON-LD `SoftwareApplication` atau `WebApplication`

### Pricing `/pricing.php`
- [ ] Title: Plans & Pricing — ChatLM
- [ ] Description menyebut Free / Starter / Pro
- [ ] FAQ schema (opsional): watermark, billing, cancel

### Blog post
- [ ] Title + meta unik
- [ ] `article:published_time`, author
- [ ] 2–3 internal links
- [ ] Alt text pada gambar

### Multi-bahasa
- [ ] `hreflang` untuk en, id, es, fr, pt, ja pada halaman yang punya `?lang=`
- [ ] `<html lang="...">` sudah dinamis ✓

---

## 6. Technical SEO

### robots.txt (contoh)

```
User-agent: *
Allow: /
Disallow: /dashboard.php
Disallow: /billing.php
Disallow: /checkout.php
Disallow: /api/
Disallow: /health.php

Sitemap: https://chatlm.tech/sitemap.xml
```

### sitemap.xml
- Homepage, pricing, setiap URL blog
- Update otomatis saat artikel baru (cron atau generate PHP)

### Performa (Core Web Vitals)
- Lazy-load gambar blog
- Minify CSS/JS production
- Cache static di Hostinger

### HTTPS
- Pastikan SSL aktif; redirect HTTP → HTTPS

---

## 7. Google Search Console — langkah setup

1. Buka [Google Search Console](https://search.google.com/search-console)
2. **Add property** → pilih **Domain** `chatlm.tech` (disarankan) atau **URL prefix** `https://chatlm.tech`
3. **Verifikasi domain:**
   - DNS TXT record di Hostinger (paling stabil), atau
   - File HTML di root, atau
   - Google Analytics / Tag Manager jika sudah terhubung
4. Setelah verified:
   - **Sitemaps** → submit `https://chatlm.tech/sitemap.xml`
   - **Settings** → International targeting (jika fokus ID, set target opsional)
   - **Settings** → Enable email alerts untuk coverage errors
5. **URL Inspection** → test `/` dan `/pricing.php` → Request indexing
6. Pantau 2–4 minggu:
   - **Performance** → queries, CTR, position
   - **Pages** → indexed vs not indexed
   - **Core Web Vitals**

### Bing Webmaster Tools (opsional)
- Import dari GSC atau verifikasi terpisah — traffic Bing untuk B2B kadang bagus.

---

## 8. Google Analytics 4 (pelengkap GSC)

1. Buat property GA4 untuk `chatlm.tech`
2. Pasang tag di `index.php`, `pricing.php`, blog template
3. Events: `sign_up`, `begin_checkout`, `purchase` (Stripe success page)
4. Hubungkan GA4 ↔ GSC di admin Google

---

## 9. Link building (off-page ringan)

| Taktik | Contoh |
|--------|--------|
| Product Hunt / BetaList | Launch ChatLM |
| GitHub README | Link ke docs + demo |
| Guest post | "AI tools for small business" di blog niche |
| Directory | SaaS directories (free tier) |
| Komunitas | Facebook grup UMKM, forum Hostinger |

Hindari pembelian backlink spam.

---

## 10. KPI bulanan

| Metrik | Target bulan 1–3 |
|--------|------------------|
| Impressions (GSC) | Naik 20% MoM |
| Average position (cluster A) | Top 30 → Top 15 |
| Organic clicks | 50+ / bulan |
| Blog posts published | 2–4 / bulan |
| Sign-up dari organic | Track UTM `?utm_source=google&utm_medium=organic` |

---

## 11. Quick wins (minggu ini)

1. Submit `sitemap.xml` + `robots.txt` di production
2. Tulis 1 artikel pillar: *How to Add AI Chat Widget in 5 Minutes*
3. Perbaiki meta title/description landing & pricing (EN + ID)
4. Tambah FAQ section di landing (schema FAQPage)
5. Pastikan widget demo hidup di homepage (social proof + dwell time)

---

## 12. File implementasi berikutnya (dev)

| File | Fungsi |
|------|--------|
| `includes/seo.php` | Helper meta, canonical, hreflang |
| `sitemap.php` | XML dinamis |
| `robots.txt` | Static di root |
| `blog/index.php` | Listing artikel |
| `blog/post.php` | Template artikel |

---

*Terakhir diperbarui: strategi awal ChatLM — sesuaikan angka KPI setelah 30 hari data GSC.*
