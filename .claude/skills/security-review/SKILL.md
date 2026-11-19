---
name: security-review
description: Tinjauan keamanan Momenikah: XSS, IDOR, unggah, CSRF, pembayaran, header, rate limit, privasi data tamu. Gunakan sebelum merge perubahan sensitif dan untuk QA-05.
---
# Skill: security-review
Acuan: `.claude/rules/30-security.md`.
## Daftar periksa
- **Akses**: setiap rute dashboard memakai `auth` dan Policy. Uji IDOR (pengguna A mengakses milik B).
- **XSS**: Blade `{{ }}`. `{!! !!}` hanya SVG lokal. `?to=` hanya lewat `textContent`. Ucapan dan cerita di-escape atau dipurifikasi.
- **Unggah**: MIME di server, batas ukuran, nama dibuat ulang, tidak menjalankan berkas unggahan, konversi gambar lewat antrian.
- **Input publik**: RSVP, ucapan, login, daftar memakai `throttle` dan Turnstile.
- **Pembayaran**: verifikasi tanda tangan callback, idempoten, nominal dari server.
- **Header**: CSP tanpa `unsafe-inline` untuk skrip, HSTS, `X-Content-Type-Options`, `Referrer-Policy`, `frame-ancestors` (undangan `'none'`, pratinjau `'self'`).
- **Rahasia**: tidak ada di repo, log, atau tes. `APP_DEBUG=false`.
- **Privasi**: data tamu seminimal mungkin, retensi dan penghapusan jelas, tanpa nama tamu di OG atau log.
- **Dependensi**: `composer audit`, `npm audit` bersih atau diterima tertulis.
## Hasil
Daftar temuan dengan tingkat (kritis, tinggi, sedang, rendah), bukti, dan perbaikan. Temuan kritis dan tinggi memblokir rilis.
