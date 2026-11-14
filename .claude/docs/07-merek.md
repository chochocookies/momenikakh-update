# 07 — Merek

Status: **nada, palet, dan huruf disetujui pemilik** (ADR-013). Logo di `brand/` adalah usulan baru yang menunggu persetujuan akhir.

## Inti
Momenikah = *momen* + *nikah*. Hangat, elegan, romantis, tenang, premium tanpa jauh. Suara: Indonesia, "kamu/kalian", kalimat pendek, tanpa kata hype.

## Token
| Token | Nilai | Pakai |
|---|---|---|
| rose-600 | `#BE185D` | aksi utama (kontras 5,9 : 1 di ivory) |
| rose-100 | `#FCE7F3` | lencana, hover |
| ink | `#2B1B22` | teks |
| muted | `#6B5A62` | teks sekunder |
| ivory | `#FFF8F3` | permukaan |
| gold | `#B8893B` | aksen premium/cincin |
Huruf: **Cormorant Garamond** (judul, wordmark) + **Plus Jakarta Sans** (UI, tombol). Skala 12/14/16/18/20/24/30/36/48/60; jarak kelipatan 4; radius 8/12/999.

## Logo
Dua cincin saling mengait (mawar + emas) dengan hati kecil di irisan. Cincin atas-bawah bergantian (interlock). Wordmark Cormorant Garamond 600, sudah berupa path (tanpa ketergantungan font).
| File | Pakai |
|---|---|
| `brand/logo/logo-horizontal.svg` | navbar, email, dokumen (latar terang) |
| `brand/logo/logo-horizontal-white.svg` | latar gelap/mawar |
| `brand/logo/logo-stacked*.svg` | sampul, kartu |
| `brand/logo/logo-mark.svg`, `logo-mark-mono.svg` | avatar, watermark, satu warna (`currentColor`) |
Ruang bebas = tinggi cincin ÷ 2 di semua sisi. Lebar minimum: horizontal 120 px, mark 24 px. Jangan memutar, menambah bayangan, atau mengganti warna cincin selain varian yang ada.

## Favicon dan ikon
`brand/favicon/`: `favicon.svg` (utama), `favicon.ico` (16/32/48), `favicon-16/32/48.png`, `apple-touch-icon.png` (180), `android-chrome-192/512.png`, `maskable-512.png`, `site.webmanifest`. Favicon memakai cincin saja (tanpa hati) agar terbaca di 16 px.
Salin ke `public/` dan pasang:
```html
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
<meta name="theme-color" content="#BE185D">
```
Catatan: `site.webmanifest` merujuk `/android-chrome-*.png` dan `/maskable-512.png` di akar `public/`.

## Sosial
`brand/social/og-default.png` 1200×630 (sumber `og-default.svg`).

## Gerak merek
Satu momen utama di landing: dua cincin menggambar diri lalu saling mengait (stroke draw + geser), diikuti hati muncul. Selebihnya tenang. Hanya `transform`/`opacity`; hormati `prefers-reduced-motion`.

## Aset ilustrasi
Pesan set orisinal dari ilustrator (hak cipta dialihkan, berkas sumber berlapis). Hindari paket yang melarang pemakaian di aplikasi tempat pengguna menyesuaikan template (periksa lisensi tertulis; Envato Elements berisiko). Setiap aset dicatat di `assets/LICENSE.md` per template (DEC-05).
