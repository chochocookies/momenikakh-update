# Roadmap Momenikah

Rujukan rinci tugas, dependensi, dan milestone: `.claude/TODO.md` (satu-satunya pelacak). Angka waktu dihitung oleh `node .claude/scripts/task.mjs estimate` dari ukuran tugas (S = 0,5, M = 1,5, L = 4, XL = 8 hari kerja efektif). Itu **asumsi satu pengembang dengan Claude Code**, bukan janji. Hitung ulang setelah M0 dan setelah dua sprint pertama memakai kecepatan nyata.

## Ringkasan beban kerja (per 19 November 2026)
| M | Nama | Tugas | Selesai | Hari kerja total | Tersisa (hari) | Tersisa (minggu) |
|---|---|---|---|---|---|---|
| M0 | Fondasi | 20 | 15 | 25 | 3,5 | 0,7 |
| M1 | Inti domain | 15 | 12 | 21 | 2,5 | 0,5 |
| M2 | Mesin template | 15 | 3 | 32,5 | 25,5 | 5,1 |
| M3 | Pembuat undangan | 36 | 0 | 49 | 49 | 9,8 |
| M4 | Pemasaran | 17 | 1 | 22 | 20,5 | 4,1 |
| M5 | Template penuh + jenis acara | 15 | 0 | 34 | 34 | 6,8 |
| M6 | Uang dan operasi | 25 | 0 | 41 | 41 | 8,2 |
| M7 | Rilis | 3 | 0 | 2 | 2 | 0,4 |
| | **Total** | 146 | 31 | **226,5** | **178** | **35,6** |

Backlog pasca-rilis (`LCH-03`) tidak dihitung. Perbarui tabel ini dari `task.mjs estimate` setiap akhir sprint.

## Milestone
Definisi dan kriteria keluar tiap milestone ada di `TODO.md` (bagian Milestone). Jangan menduplikasinya di sini.

## Jalur paralel
| Jalur | Isi | Mulai |
|---|---|---|
| A. Platform | FND, DOM, MED, sisi server PUB, BIL | Minggu 1 |
| B. Antarmuka | DS, TPL, DSH, MKT | Setelah DS-01 |
| C. Aset visual | TPL-15 (kontrak ilustrator, set ornamen enam tema), MED-06 (musik berlisensi) | **Segera setelah DEC-05 dan DEC-06**, karena waktu tunggunya paling panjang |
| D. Konten dan hukum | Artikel panduan (MKT-06), teks bawaan per jenis acara, kebijakan privasi dan syarat (MKT-08), UU PDP (DOM-14) | Paralel sejak M2 |

## Urutan yang disarankan
1. M0 selesai. Mulai jalur C paralel.
2. M1, lalu M2: buktikan mesin template dengan **satu template penuh** (TPL-08) sebelum membuat lima lainnya. Di sinilah duplikasi biasanya mulai.
3. M3: pengguna uji pertama sudah bisa membuat undangan.
4. M4 (pemasaran) berjalan bersama M5 (template lain dan jenis acara). Landing (MKT-01) dikerjakan setelah template dan logo final siap supaya gambar dan demo nyata.
5. M6, lalu M7. Jangan memotong QA-05 (keamanan) dan OPS-08 (rilis percobaan).

## Ritme kerja
Sprint dua minggu. Awal sprint: `task.mjs next 5`, `task.mjs blocked`, pilih tugas, `/start-task`. Akhir sprint: `task.mjs status` dan `task.mjs estimate`, tinjau kecepatan, perbarui asumsi ukuran dan tabel di atas. Satu tugas satu PR kecil. `main` selalu dapat dirilis ke staging.

## Gerbang keputusan
Daftar lengkap dan status ada di `docs/DECISIONS.md` (bagian `DEC-nn`); lihat dengan `task.mjs decisions`.
| Sebelum tugas | Keputusan |
|---|---|
| PUB-01, FND-12 | DEC-03: host undangan publik (`undangan.` atau `share.`) |
| OPS-05 | DEC-02: hosting, email, anggaran |
| MED-01 | DEC-04: penyimpanan objek + CDN |
| TPL-15 | DEC-05: sumber aset ilustrasi |
| MED-05, MED-06 | DEC-06: katalog musik |
| DOM-09, BIL-03, BIL-06, MKT-05 | DEC-01: paket dan harga |
| EVT-01…05 | DEC-11: urutan jenis acara |
| OPS-07 | DEC-12: ada tidaknya pengguna lama |
| DOM-14 | DEC-13: masa simpan data tamu |
| LCH-02 | DEC-10: persetujuan logo dan nama merek |

## Risiko dan penanganan
| Risiko | Dampak | Penanganan |
|---|---|---|
| Aset ilustrasi terlambat atau berlisensi bermasalah | Template tampak polos, tuntutan | Mulai TPL-15 sejak awal; ilustrator dengan alih hak; `assets/LICENSE.md` wajib; ornamen SVG sederhana buatan sendiri sebagai cadangan |
| Template berat di jaringan seluler | Tamu menutup undangan | Anggaran 400 KB di manifest, `template:lint` di CI (TPL-14) |
| Data hilang saat simpan | Kepercayaan hilang | DTO + tes round-trip (DOM-06) dan tes kontrak (QA-06) |
| Lonjakan tamu dari grup WhatsApp | Server tumbang | Cache halaman (PUB-03), edge (OPS-06), domain terpisah, uji k6 (QA-03) |
| Kebocoran data tamu/RSVP | Pelanggaran UU PDP | Policy, throttle, log tanpa PII, retensi (DOM-14), audit (QA-05), tanpa data tamu di OG |
| Gerak berlebihan di ponsel murah | Patah-patah | Hanya `transform`/`opacity`, tingkat gerak `none/gentle/rich`, uji perangkat nyata |
| Lingkup melebar (6 jenis acara × 6 template) | Tidak pernah rilis | Mulai dari 1 template × 3 jenis; sisanya konfigurasi (EVT dikerjakan setelah M2) |
| Landing GSAP berat | LCP dan SEO turun | Teks hero di HTML, GSAP di chunk terpisah, anggaran di `docs/LANDING.md` (MKT-07) |
| Persetujuan akun Duitku lambat | Pembayaran tertahan | Ajukan akun sejak M0; mode uji; siapkan alternatif (Midtrans, Xendit) di balik `PaymentGateway` |
| Teks keagamaan atau adat keliru | Reputasi | Tinjauan ahli sebelum rilis; teks selalu dapat diubah pengguna (EVT-01) |
| Perubahan versi Laravel dan Filament | Utang teknis | Dependabot (FND-11), kunci versi, perbarui per kuartal |
| Satu orang pengembang | Titik tunggal kegagalan | Dokumentasi di `.claude`, tes otomatis, runbook (OPS-01) |

## Kriteria rilis publik
Semua tugas M0–M6 selesai. Audit keamanan tanpa temuan tinggi terbuka (QA-05). Alur inti lolos tes otomatis dan browser test (QA-01, QA-02). Pemulihan cadangan pernah dilatih (OPS-01). Kebijakan privasi dan syarat terbit (MKT-08). Uji beban memenuhi target (QA-03). Beta tertutup selesai tanpa masalah penghalang (LCH-01). Landing tidak memuat klaim yang tidak benar (DEC D-14). Daftar periksa rilis (LCH-02) lulus.
