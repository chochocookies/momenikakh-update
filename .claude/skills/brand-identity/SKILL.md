---
name: brand-identity
description: Sistem brand Momenikah: logo, favicon, warna, huruf, nada bahasa, dan cara membuat ulang kit brand. Gunakan untuk semua pekerjaan UI, copy, logo, favicon, dan gambar sosial.
---
# Skill: brand-identity
Acuan: `docs/BRAND.md`. Status: Proposed (D-06), nada dan warna sudah disetujui pemilik.
## Inti
Nama `Momenikah`, huruf **n** berwarna rose (dipakai bersama "momen" dan "nikah"). Tagline: Satu link untuk semua momen nikahmu. Lambang: dua cincin bertaut dengan hati.
## Token
rose `#BE185D`, rose-light `#F472B6`, rose-soft `#FCE7F3`, ink `#2B1B22`, muted `#6B5A62`, ivory `#FFF8F3`, gold `#B8893B` (premium saja). Huruf: Cormorant Garamond (judul), Plus Jakarta Sans (UI).
## Berkas
`.claude/brand/logo/*` (SVG dan PNG), `favicon/*` (ico, svg, png, maskable, `site.webmanifest`), `social/og-default.*`. Salin ke `public/` lewat tugas F7-06. Jangan membuat logo baru atau memodifikasi path wordmark secara manual.
## Mengubah kit brand
Ubah `TOKENS` di `.claude/brand/build-brand.py` lalu jalankan `pip install fonttools cairosvg pillow` dan `python3 .claude/brand/build-brand.py`. Semua berkas dibuat ulang. Peninjauan visual oleh pemilik wajib sebelum dipakai.
## Aturan
Ruang bebas minimal setinggi huruf M, lebar minimum 120 px (horizontal) dan 24 px (lambang). Jangan memutar, meregangkan, memberi bayangan, atau mengganti warna. Teks: hangat, jelas, romantis tanpa berlebihan. Dilarang klaim "terbaik", "ribuan pengguna", rating, atau testimoni tanpa data nyata.
