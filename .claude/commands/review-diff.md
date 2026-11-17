---
description: Tinjau diff saat ini terhadap aturan emas Momenikah
---
Jalankan `git diff` (dan `git diff --staged`). Periksa terhadap `.claude/CLAUDE.md` dan `.claude/rules/*`:
- Template memakai Eloquent, query, atau `request()`? Ada `{!! !!}` yang tidak aman? Nama tamu masuk `innerHTML`?
- Ada CDN, font tambahan, warna atau ukuran hardcode, animasi selain `transform` dan `opacity`, atau animasi tanpa penangan reduced-motion?
- Rute dashboard tanpa Policy? Input publik tanpa throttle dan Turnstile? Unggah tanpa validasi server?
- Migrasi lama diedit? `migrate:fresh` atau data nyata dipakai? Rahasia di diff?
- Klaim palsu (testimoni, rating, jumlah pengguna, harga)? Teks pengguna tidak dalam bahasa Indonesia?
- Tes untuk perilaku baru ada? Dokumen terdampak diperbarui?
Keluarkan daftar temuan (kritis, tinggi, sedang, rendah) dengan berkas dan baris, plus perbaikan yang disarankan. Jangan mengubah kode kecuali diminta.
