# 08 — Roadmap

Ukuran tugas: **S** ≤ ½ hari · **M** 1–2 hari · **L** 3–5 hari kerja satu orang. Tidak ada janji tanggal; hitung kalender dari jam kerja nyata per minggu (asumsi yang harus Anda isi). Tugas bertanda `(owner)` butuh tindakan pemilik, bukan kode.

## Milestone
| M | Nama | Isi (fase TODO) | Keluar bila |
|---|---|---|---|
| M0 | Fondasi | FND, DS-01…06 | repo + CI hijau, staging hidup, token merek tampil di `/styleguide`, domain lokal tiga permukaan |
| M1 | Inti domain | DOM, GST-01/02 | semua tabel + factory + tes round-trip + policy; 6 jenis acara terdaftar |
| M2 | Mesin template | TPL-01…08, TPL-14, TPL-16, PUB-01…04 | Classic Elegant tampil di `undangan.` dari data contoh untuk 3 jenis acara; cache + preview jalan |
| M3 | Pembuat undangan | MED, DSH-01…06, PUB-05…12 | pengguna bisa daftar → buat → isi → publikasi → buka → RSVP/ucapan |
| M4 | Pemasaran | DS-07, MKT, SEO | landing GSAP, katalog, halaman jenis acara terindeks, Lighthouse CI hijau |
| M5 | Template penuh + jenis acara | TPL-09…13, TPL-15, EVT | 6 template, 6 jenis acara, aset ilustrasi berlisensi |
| M6 | Uang dan operasi | BIL, ADM, OPS, QA | pembayaran sandbox→produksi, admin, monitoring, tes beban, beta tertutup |
| M7 | Rilis | LCH | daftar periksa rilis lulus; rilis publik |

## Jalur paralel (pemilik)
- Merek: persetujuan logo (DEC-10) sebelum DS-05.
- Ilustrasi: brief → ilustrator (DEC-05) mulai sejak M2; kirim per tema.
- Konten: kata-kata undangan, FAQ, panduan SEO (periksa isi keagamaan dengan sumber tepercaya).
- Hukum: kebijakan privasi, syarat, UU PDP (MKT-08).
- Bisnis: paket/harga (DEC-01) sebelum M6.

## Urutan kerja yang disarankan
M0 → M1 → M2 (buktikan sistem template dengan satu template dulu) → M3 (pengalaman inti) → M4 paralel dengan M5 → M6 → M7. Jangan membangun template ke-2 sebelum `templates:audit`, tes render per jenis acara, dan tes visual Classic Elegant hijau: di situlah duplikasi biasanya mulai.

## Risiko
| Risiko | Dampak | Mitigasi |
|---|---|---|
| Lisensi aset ilustrasi | gugatan, tarik aset | ilustrator + alih hak; `assets/LICENSE.md` wajib |
| Template berat di jaringan seluler | tamu menutup undangan | anggaran 400 KB, `templates:audit` di CI |
| Data hilang saat simpan | kepercayaan hilang | DTO + tes round-trip (DOM-06) |
| Lonjakan tamu dari grup WhatsApp | server tumbang | cache halaman, CDN, domain terpisah, uji k6 |
| Kebocoran data tamu/RSVP | pelanggaran PDP | Policy, throttle, log tanpa PII, hapus data |
| Gerak berlebihan di ponsel murah | patah-patah | hanya transform/opacity, tingkat gerak `none/gentle/rich`, uji perangkat nyata |
| Lingkup melebar (6 jenis acara × 6 template) | tidak selesai | mulai dari 1 template × 3 jenis; sisanya konfigurasi |
| Ketergantungan satu orang | tersendat | dokumentasi di `.claude`, CI, tugas kecil |
