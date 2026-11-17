# Keputusan (ADR ringkas)

Status: **Accepted** (sudah diputuskan pemilik), **Proposed** (usulan, belum final, jangan dianggap fakta), **Open** (butuh keputusan pemilik).

| ID | Keputusan | Status | Alasan atau catatan | Dampak |
|---|---|---|---|---|
| D-01 | Membangun ulang dari nol (greenfield), bukan memperbaiki versi lama | Accepted | Diputuskan pemilik: UI lama tidak konsisten dan banyak yang perlu diperbaiki | Seluruh dokumen berdiri sendiri di `.claude` |
| D-02 | Model paket, harga, dan ada atau tidaknya paket gratis | Open | Belum ada data. Menentukan klaim "gratis" di SEO dan landing | F6-01, F7-07 |
| D-03 | Satu panel admin: Filament 5 | Proposed | Menghindari dua admin seperti proyek lama | F6-06 |
| D-04 | Undangan tamu di host terpisah `share.momenikah.online` | Proposed | Isolasi cookie, cache, dan keamanan; pola yang sama dipakai pesaing | F0-11, F3-01 |
| D-05 | Nama tamu dirender di klien dari `?to=` | Proposed | Memungkinkan cache penuh di edge | F2-09, F3-02, F3-13 |
| D-06 | Identitas brand (logo, warna, nada) | Proposed | Nada dan warna disetujui pemilik. Logo dan ikon masih rancangan pertama | Semua UI |
| D-07 | Penyedia pembayaran: Duitku | Proposed | Metode lokal lengkap. Cek ketersediaan akun dan biaya sebelum final | F6-02 |
| D-08 | Autentikasi dari starter kit resmi Laravel | Proposed | Tidak menulis sendiri | F1-01 |
| D-09 | Halaman undangan publik tanpa Livewire | Proposed | Ringan dan dapat di-cache | F2-09 |
| D-10 | Template = satu folder dengan manifest dan palet | Proposed | Menambah template tanpa menyentuh kode lain | F2-05, F5-xx |
| D-11 | Aset ilustrasi dipesan dari ilustrator dengan hak cipta penuh. Hindari pasar dengan larangan aplikasi on-demand (mis. Envato Elements) | Proposed | Lisensi dan keunikan visual | F5-01 |
| D-12 | Hosting: VPS + Cloudflare, penyimpanan S3-kompatibel | Open | Anggaran dan keahlian operasi belum diketahui | F0-07, F0-11 |
| D-13 | Urutan jenis acara setelah pernikahan | Open | Usulan: walimatul urs, tunangan, aqiqah, khitanan, ulang tahun | F9-xx |
| D-14 | Testimoni, rating, dan jumlah pengguna hanya dari data nyata | Accepted | Kejujuran dan risiko hukum | Semua teks pemasaran |
| D-15 | Apakah ada pengguna dan undangan di momenikah.online lama yang harus dimigrasi | Open | Menentukan F8-08 dan strategi redirect `/u/` | F8-08 |
| D-16 | Masa simpan data tamu setelah undangan kedaluwarsa | Open | Terkait UU PDP, perlu tinjauan ahli | F8-07 |
| D-17 | GSAP hanya di landing dan efek premium template | Proposed | Menjaga undangan tetap ringan | F7-02 |
