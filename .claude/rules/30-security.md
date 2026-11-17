---
paths:
  - "app/Http/**"
  - "app/Policies/**"
  - "routes/**"
  - "config/**"
  - "app/Services/Billing/**"
---
# Aturan keamanan
- Setiap rute dashboard memakai `auth` + Policy (kepemilikan). Tidak ada akses lewat `id` tanpa `authorize()`.
- Validasi dengan Form Request. Aturan unggah: MIME diperiksa di server, ukuran dibatasi, nama file dibuat ulang, simpan di disk privat atau S3 dengan konversi, jangan percaya ekstensi.
- Endpoint publik (RSVP, ucapan, login, daftar) memakai `throttle` dan Cloudflare Turnstile. Tanpa captcha buatan sendiri.
- Pembayaran: verifikasi tanda tangan callback, idempoten (nomor order unik), jangan percaya jumlah dari klien.
- Header: CSP ketat (tanpa `unsafe-inline` untuk skrip), HSTS, `X-Content-Type-Options`, `Referrer-Policy`. Halaman undangan: `frame-ancestors 'none'`, kecuali rute pratinjau (`'self'`).
- Tidak ada rute debug, `phpinfo`, atau unggah uji di produksi. `APP_DEBUG=false`.
- HTML dari pengguna (ucapan, cerita) di-escape. Bila perlu format, gunakan purifier dengan daftar tag kecil.
- Log tidak boleh memuat kata sandi, token, nomor rekening penuh, atau isi `.env`.
