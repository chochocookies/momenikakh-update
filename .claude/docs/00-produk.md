# 00 — Produk

**Janji:** "Satu link untuk semua momen nikahmu." Pembuat undangan digital yang indah, ringan dibuka dari WhatsApp, tanpa unduh aplikasi.

## Pengguna
- **Pembuat undangan** (pengantin, orang tua, panitia): membuat, mengisi, memublikasikan, membagikan, memantau RSVP.
- **Tamu**: membuka tautan di ponsel, melihat acara, mengisi RSVP/ucapan, menyalin rekening, membuka peta.
- **Admin Momenikah**: moderasi, paket, pembayaran, template.

## Jenis acara (enam, satu mesin)
Pernikahan, Walimatul Urs, Tunangan, Khitanan, Aqiqah, Ulang Tahun. Rincian fitur: `05-jenis-acara.md`.

## Cakupan v1 (MVP rilis publik)
Akun; wizard buat undangan; editor per langkah; 6 template × semua jenis acara yang didukung manifest; pratinjau; publikasi; tautan per tamu `?to=`/`?g=`; RSVP; buku tamu; amplop digital; peta; musik; hitung mundur; galeri; statistik dasar; paket + pembayaran Duitku; admin Filament; situs pemasaran beranimasi (GSAP) dengan SEO.

## Di luar v1 (backlog, lihat TODO fase LCH)
Domain kustom, reseller/white-label, tiket masuk QR, notifikasi WhatsApp otomatis, pengunjung langsung (realtime), bahasa selain Indonesia, kustom CSS/JS pengguna (butuh ADR baru).

## Prinsip
1. Cepat dibuka di ponsel dan jaringan seluler: anggaran aset ketat.
2. Indah tanpa ramai: 2–3 ornamen per bagian, ruang kosong, gerak halus.
3. Satu sistem: satu design system, satu kontrak data template, satu mesin jenis acara.
4. Jujur: tidak ada klaim palsu di halaman publik.
5. Aman: undangan memuat data pribadi; `noindex`, domain terpisah, tanpa kode buatan pengguna.

## Ukuran keberhasilan (target internal, bukan klaim publik)
Pembuat selesai membuat undangan < 15 menit; LCP undangan ≤ 2,5 dtk di 4G ponsel menengah; CLS < 0,1; 0 data hilang saat simpan (tes round-trip); tidak ada kerentanan P0/P1 terbuka saat rilis.
