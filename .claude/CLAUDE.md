# Momenikah — aturan kerja (selalu aktif)

Platform undangan digital Indonesia: pernikahan, walimatul urs, tunangan, khitanan, aqiqah, ulang tahun. Proyek **baru dari nol**. Satu-satunya sumber konteks adalah folder `.claude/` ini; tidak ada `AI_CONTEXT/`.

## Mulai setiap tugas
1. Baca `.claude/docs/00-produk.md` dan `.claude/docs/09-keputusan.md` sekali per sesi. Baca dokumen lain sesuai tugas (peta di `.claude/README.md`).
2. Cari tugas di `.claude/TODO.md` (tabel, ID unik). Periksa kolom `Dep` dan keputusan terbuka (`DEC-nn`).
3. Pakai skill yang cocok di `.claude/skills/<nama>/SKILL.md`. Untuk pekerjaan merek, landing page, atau template, muat `brand-identity` dan `gsap-motion` lebih dulu.

## Aturan keras
- Jangan baca, cetak, atau tulis `.env`, kunci, token, kata sandi, atau data pribadi asli. Data contoh selalu fiktif.
- Jangan putuskan `DEC-nn` yang masih terbuka. Berhenti dan tanya pemilik. ADR berstatus `Usulan` bukan keputusan; jangan menulisnya sebagai "diputuskan".
- Satu ID tugas per perubahan. Tanpa edit di luar lingkup. Dependensi baru (composer/npm) hanya dengan alasan tertulis di `docs/09-keputusan.md` atau PR.
- Jangan klaim sesuatu berfungsi sebelum dijalankan. Tandai `[x]` hanya bila semua kriteria "Selesai bila" terbukti (tes, tangkapan layar, atau keluaran perintah).
- Migrasi harus aditif dan bisa di-rollback. Jangan jalankan `migrate:fresh`, `db:wipe`, atau SQL destruktif.
- Landing page dan katalog hanya mengklaim fitur yang sudah ada. Tanpa rating, ulasan, jumlah pengguna, atau harga buatan (lihat `brand-identity`).
- Tidak ada JS/CSS/HTML buatan pengguna di undangan publik (ADR-012).

## Arsitektur singkat (rincian: `docs/02-arsitektur.md`, `docs/03-struktur-folder.md`)
Satu aplikasi Laravel 13, tiga permukaan berbasis domain: `momenikah.online` (pemasaran), `app.momenikah.online` (dasbor + `/admin`), `undangan.momenikah.online` (undangan publik, tanpa cookie sesi).
Template = folder mandiri di `resources/templates/<slug>/` + `manifest.php`. Template hanya membaca `InvitationViewModel`, tidak pernah Eloquent. Jenis acara = konfigurasi (`EventTypeRegistry`), bukan fork template.

## Cara kerja
- **Mode Plan** (Shift+Tab dua kali): baca saja. Hasilkan catatan 5 baris (ID tugas, dependensi, risiko, rencana, keputusan yang dibutuhkan), daftar file, daftar tes.
- **Mode Act**: kerjakan hanya rencana yang disetujui. Tes gagal dulu, implementasi, tes hijau, lalu `/finish-task`.
- Pembaruan dokumen satu perubahan dengan kode: status di `TODO.md`, `docs/10-changelog.md`, dan `docs/09-keputusan.md` bila ada keputusan.
- Akhiri sesi dengan `/handoff`.

## Bahasa
Balas dalam bahasa pengguna (Indonesia). Teks antarmuka: Indonesia, sapaan "kamu/kalian", kalimat pendek. Kode, nama kelas, dan komentar teknis: Inggris. Merek ditulis `Momenikah` (satu kata).

## Perintah
`/next-task` `/start-task <ID>` `/finish-task` `/review-diff` `/handoff` `/new-template <slug>` `/new-event-type <kunci>`
