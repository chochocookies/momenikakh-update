# Roadmap Momenikah

Rujukan rinci: `TODO.md` (122 tugas, 11 fase). Angka waktu di bawah dihitung otomatis oleh `node .claude/scripts/task.mjs estimate` dari ukuran tugas (S = 0,5, M = 1,5, L = 4, XL = 8 hari kerja efektif). Itu **asumsi satu pengembang dengan Claude Code**, bukan janji. Hitung ulang setelah Fase 0 dan setelah dua sprint pertama, memakai kecepatan nyata.

## Ringkasan beban kerja (tidak termasuk backlog)
| Fase | Nama | Tugas | Hari kerja | Minggu | Kumulatif (minggu) |
|---|---|---|---|---|---|
| F0 | Fondasi dan keputusan | 12 | 11 | 2,2 | 2,2 |
| F1 | Platform inti | 13 | 17 | 3,4 | 5,6 |
| F2 | Design system dan mesin template | 14 | 28 | 5,6 | 11,2 |
| F3 | Undangan publik | 16 | 26 | 5,2 | 16,4 |
| F4 | Dashboard pembuat undangan | 14 | 29 | 5,8 | 22,2 |
| F5 | Katalog template batch 1 | 9 | 31 | 6,2 | 28,4 |
| F6 | Paket, pembayaran, admin | 8 | 16 | 3,2 | 31,6 |
| F7 | Situs pemasaran, landing GSAP, SEO | 12 | 27,5 | 5,5 | 37,1 |
| F8 | Kualitas dan peluncuran | 11 | 21,5 | 4,3 | 41,4 |
| F9, F10 | Jenis acara baru dan peningkatan | 12 | backlog | n/a | n/a |
| | **Total** | 109 + 13 backlog (termasuk F6-09) | **207** | **41,4** | |

Per prioritas: P0 = 56 tugas (125 hari), P1 = 41 tugas (67,5 hari), P2 = 12 tugas (14,5 hari). **Jalur minimum menuju rilis** (semua P0 beserta dependensinya) = 65 tugas, 140 hari kerja (±28 minggu). Pekerjaan paralel (ilustrator, konten, hukum) memperpendek kalender, bukan beban pengembang.

## Jalur paralel
| Jalur | Isi | Mulai |
|---|---|---|
| A. Platform | F0, F1, sisi server F3, F6 | Minggu 1 |
| B. Antarmuka | F2, F4, F7 | Setelah F0-04 |
| C. Aset visual | F5-01 (kontrak ilustrator, set ornamen enam tema), F5-08 (musik berlisensi) | **Segera setelah F0-01** karena waktu tunggu paling panjang |
| D. Konten dan hukum | Artikel panduan, teks bawaan per jenis acara, kebijakan privasi, syarat | Paralel sejak F2 |

## Tonggak
| Tonggak | Kriteria | Fase |
|---|---|---|
| M0 Siap bangun | Keputusan terbuka tertutup, repo, CI, staging, DNS | F0 |
| M1 Fondasi hidup | Masuk, buat undangan kosong, template contoh tampil di pratinjau | F1, F2 |
| M2 Undangan pertama terbuka | Satu undangan published terbuka di `share.` dengan `?to=`, RSVP, ucapan, galeri, template classic-elegant | F3, F5-02 |
| M3 Pembuat lengkap | Wizard, pratinjau langsung, daftar tamu, statistik | F4 |
| M4 Bisa dijual | Enam template, paket, pembayaran, admin | F5, F6 |
| M5 Dikenal | Landing GSAP, katalog, SEO teknis, artikel awal | F7 |
| M6 Rilis | Audit keamanan, beban, beta tertutup, cutover | F8 |
| M7+ | Jenis acara baru lalu peningkatan lanjutan | F9, F10 |

## Urutan yang disarankan (menghindari "semua jadi di akhir")
1. **F0** selesai, lalu **F5-01** dimulai (paralel).
2. **F1 + F2** bersama sampai M1.
3. Langsung buat **satu template penuh (F5-02)** dan **F3**. M2 memberi bukti nyata bahwa arsitektur template bekerja sebelum membuat lima template lain.
4. **F4** (dashboard). Pada titik ini pengguna uji pertama bisa membuat undangan.
5. **F5-03 sampai F5-07** (template lain) dan **F6** berjalan bergantian.
6. **F7** (landing dan SEO) dikerjakan ketika template dan logo final siap supaya gambar dan demo di landing nyata.
7. **F8** sebelum membuka untuk umum. Jangan memotong F8-03 (keamanan) dan F8-09 (checklist rilis).

## Ritme kerja
Sprint dua minggu. Awal sprint: `node .claude/scripts/task.mjs next 5`, pilih tugas, `/start-task`. Akhir sprint: `task.mjs status` dan `task.mjs estimate`, tinjau kecepatan, perbarui asumsi ukuran. Satu tugas satu PR kecil. `main` selalu dapat dirilis ke staging.

## Gerbang keputusan
| Sebelum tugas | Keputusan yang dibutuhkan |
|---|---|
| F5-01 | Gaya ilustrasi dan anggaran ilustrator (D-11) |
| F6-01, F7-07 | Paket dan harga (D-02) |
| F0-07, F0-11 | Hosting dan edge (D-12) |
| F8-08 | Ada tidaknya pengguna lama (D-15) |
| F8-07 | Masa simpan data tamu (D-16) |
| F9-xx | Urutan jenis acara (D-13) |

## Risiko dan penanganan
| Risiko | Dampak | Penanganan |
|---|---|---|
| Aset ilustrasi terlambat | Template tampak polos, F5 dan F7 tertunda | Mulai F5-01 sejak awal; sediakan ornamen SVG sederhana buatan sendiri sebagai cadangan |
| Kelebihan cakupan (semua jenis acara sekaligus) | MVP tidak pernah rilis | Pernikahan dulu; jenis acara lain hanya sebagai backlog (F9) |
| Persetujuan akun Duitku lambat | F6 tertahan | Ajukan akun saat F0; siapkan mode uji dan alternatif (Midtrans, Xendit) |
| Performa buruk karena ornamen dan animasi | Undangan lambat di jaringan seluler | Anggaran bobot di manifest dan CI (F2-12, F2-14) |
| Landing GSAP berat | LCP dan SEO turun | Teks hero di HTML, GSAP di chunk terpisah, anggaran di LANDING.md |
| Teks keagamaan atau adat keliru | Reputasi | Tinjauan oleh ahli sebelum rilis, teks selalu dapat diubah pengguna |
| Satu orang pengembang | Titik tunggal kegagalan | Dokumentasi di `.claude`, tes otomatis, runbook (F8-06) |
| Perubahan versi Laravel dan Filament | Utang teknis | Dependabot (F0-09), kunci versi, perbarui per kuartal |
| Pelanggaran lisensi aset | Tuntutan | Hak cipta penuh dari ilustrator, simpan bukti lisensi musik dan font |
| Data tamu bocor | Pelanggaran privasi | Audit F8-03, retensi F8-07, tanpa data tamu di OG dan log |

## Kriteria rilis publik
Semua P0 selesai. Audit keamanan tanpa temuan kritis atau tinggi yang terbuka. Alur inti lolos tes otomatis dan browser test. Pemulihan cadangan pernah dilatih. Kebijakan privasi dan syarat terbit. Uji beban memenuhi target yang disepakati. Beta tertutup selesai tanpa masalah penghalang. Landing tidak memuat klaim yang tidak benar.
