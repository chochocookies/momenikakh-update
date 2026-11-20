# Keputusan (ADR ringkas)

Status: **Accepted** (sudah diputuskan pemilik), **Proposed** (usulan, belum final, jangan dianggap fakta), **Open** (butuh keputusan pemilik).

| ID | Keputusan | Status | Alasan atau catatan | Dampak |
|---|---|---|---|---|
| D-01 | Membangun ulang dari nol (greenfield), bukan memperbaiki versi lama | Accepted | Diputuskan pemilik: UI lama tidak konsisten dan banyak yang perlu diperbaiki | Seluruh dokumen berdiri sendiri di `.claude` |
| D-02 | Model paket, harga, dan ada atau tidaknya paket gratis | Accepted (lihat DEC-01, 2026-11-19) | Belum ada data. Menentukan klaim "gratis" di SEO dan landing | DEC-01, MKT-05 |
| D-03 | Satu panel admin: Filament 5 | Proposed | Menghindari dua admin seperti proyek lama | ADM-02 |
| D-04 | Undangan tamu di host terpisah `undangan.momenikah.online` | Accepted (DEC-03, 2026-11-19) | Isolasi cookie, cache, dan keamanan; pola yang sama dipakai pesaing | OPS-05, PUB-01 |
| D-05 | Nama tamu dirender di klien dari `?to=` | Proposed | Memungkinkan cache penuh di edge | TPL-06, PUB-12, OPS-06 |
| D-06 | Identitas brand (logo, warna, nada) | Proposed | Nada dan warna disetujui pemilik. Logo dan ikon masih rancangan pertama | Semua UI |
| D-07 | Penyedia pembayaran: Duitku | Proposed | Metode lokal lengkap. Cek ketersediaan akun dan biaya sebelum final | BIL-02 |
| D-08 | Autentikasi dari starter kit resmi Laravel | Proposed | Tidak menulis sendiri | FND-07 |
| D-09 | Halaman undangan publik tanpa Livewire | Proposed | Ringan dan dapat di-cache | TPL-06 |
| D-10 | Template = satu folder dengan manifest dan palet | Proposed | Menambah template tanpa menyentuh kode lain | TPL-01, TPL-08…13 |
| D-11 | Aset ilustrasi dipesan dari ilustrator dengan hak cipta penuh. Hindari pasar dengan larangan aplikasi on-demand (mis. Envato Elements) | Proposed | Lisensi dan keunikan visual | TPL-15 |
| D-12 | Hosting: VPS + Cloudflare, penyimpanan S3-kompatibel | Open | Anggaran dan keahlian operasi belum diketahui | FND-06, OPS-05 |
| D-13 | Urutan jenis acara setelah pernikahan | Open | Usulan: walimatul urs, tunangan, aqiqah, khitanan, ulang tahun | EVT-01…05 |
| D-14 | Testimoni, rating, dan jumlah pengguna hanya dari data nyata | Accepted | Kejujuran dan risiko hukum | Semua teks pemasaran |
| D-15 | Apakah ada pengguna dan undangan di momenikah.online lama yang harus dimigrasi | Open | Menentukan OPS-07 dan strategi redirect `/u/` | OPS-07 |
| D-16 | Masa simpan data tamu setelah undangan kedaluwarsa | Open | Terkait UU PDP, perlu tinjauan ahli | DOM-14 |
| D-17 | GSAP hanya di landing dan efek premium template | Proposed | Menjaga undangan tetap ringan | MKT-01 |

## Keputusan terbuka yang memblokir tugas (`DEC-nn`)
Dibaca oleh `node .claude/scripts/task.mjs` (`decisions`, `blocked`, `next`). Ubah status menjadi `Decided` (atau `Accepted`/`Rejected`) bila pemilik sudah memutuskan, lalu catat ADR-nya di tabel atas. Tugas di `TODO.md` yang bergantung pada DEC belum siap sampai DEC-nya diputuskan.

| ID | Pertanyaan | Status | Memblokir | ADR terkait |
|---|---|---|---|---|
| DEC-01 | Nama paket, batas (foto, template premium, masa aktif), harga, dan ada tidaknya paket gratis | Accepted (2026-11-19: Gratis = undangan sample, hanya pratinjau, tidak bisa dipublikasi. Paket Template Rp 50.000, sekali bayar per undangan, tanpa masa kedaluwarsa. Batas foto dan tamu belum ditentukan, jadi tanpa batas) | DOM-09, BIL-03, BIL-06, MKT-05 | D-02 |
| DEC-02 | Hosting produksi (VPS mana), email transaksional, anggaran bulanan | Open | OPS-05 | D-12 |
| DEC-03 | Host undangan publik final dan skema slug. Kandidat: `undangan.` (dipakai FND-05, `config/surfaces.php`) atau `share.` (D-04, CLAUDE.md, ARCHITECTURE.md) | Accepted (2026-11-19: host `undangan.`; slug nama + akhiran acak 4 karakter, DOM-07) | PUB-01, FND-12 | D-04 |
| DEC-04 | Vendor penyimpanan objek + CDN (mis. Cloudflare R2) | Open | MED-01 | D-12 |
| DEC-05 | Sumber aset ilustrasi (ilustrator, lisensi, anggaran) | Open | TPL-15 | D-11 |
| DEC-06 | Katalog musik: unggah pengguna saja, atau pustaka berlisensi | Open | MED-05, MED-06 | - |
| DEC-07 | Bahasa: hanya Indonesia di v1? | Open | (sebagian TPL, EVT) | - |
| DEC-08 | Jadwal domain kustom dan reseller | Open | LCH-03 | - |
| DEC-09 | Notifikasi WhatsApp: tautan manual saja atau API resmi | Open | LCH-03 | - |
| DEC-10 | Persetujuan logo dan penamaan merek final | Open | LCH-02 | D-06 |
| DEC-11 | Urutan jenis acara setelah pernikahan (usulan D-13: walimatul urs, tunangan, aqiqah, khitanan, ulang tahun) | Open | EVT-01…05 | D-13 |
| DEC-12 | Apakah ada pengguna dan undangan di `momenikah.online` lama yang harus dimigrasi | Open | OPS-07 | D-15 |
| DEC-13 | Masa simpan data tamu setelah undangan kedaluwarsa (UU PDP, perlu tinjauan ahli) | Open | DOM-14 | D-16 |
