# TODO — Momenikah (greenfield)

Satu-satunya pelacak tugas. Tidak ada TODO lain di `.claude/` (`docs/TODO.md` bergaya F0–F10 sudah digabung ke sini; pemetaan ID lama ada di Lampiran).

**Status:** `[ ]` belum · `[~]` dikerjakan · `[x]` selesai (bukti ada) · `[!]` terblokir · `(owner)` tindakan pemilik, bukan kode.
**Kolom:** St, ID, Tugas, Dep (dependensi), Selesai bila (kriteria yang harus dibuktikan), Uk (ukuran), M (milestone).
**Ukuran (hari kerja efektif, asumsi satu pengembang + Claude Code, hitung ulang setelah M0):** S = 0,5 · M = 1,5 · L = 4 · XL = 8.
**Dep:** `DEC-nn` = keputusan di `docs/DECISIONS.md`. Tugas belum siap bila DEC-nya belum diputuskan. `A…B` = rentang ID (mis. `TPL-08…13`). Satu ID = satu perubahan = satu cabang.
**Pelacak:** `node .claude/scripts/task.mjs status | next [n] | show ID | list [PREFIX|Mn] | blocked | decisions | start ID | done ID | block ID | estimate | check`.

## Milestone
| M | Nama | Isi | Keluar bila |
|---|---|---|---|
| M0 | Fondasi | FND, DS-01…06, OPS-05 | repo + CI hijau, staging hidup, token merek tampil di `/styleguide`, tiga permukaan lokal |
| M1 | Inti domain | DOM-01…13, GST-01/02 | semua tabel + factory + tes round-trip + policy; 6 jenis acara terdaftar |
| M2 | Mesin template | TPL-01…08, 14, 16, 17; PUB-01…04 | Classic Elegant tampil di permukaan undangan dari data contoh untuk 3 jenis acara; cache + preview jalan |
| M3 | Pembuat undangan | MED, DSH, PUB-05…17, GST-03, FND-13, QA-06, OPS-06 | pengguna bisa daftar → buat → isi → publikasi → buka → RSVP/ucapan |
| M4 | Pemasaran | DS-07, MKT, SEO | landing GSAP, katalog, halaman jenis acara terindeks, Lighthouse CI hijau |
| M5 | Template penuh + jenis acara | TPL-09…13, 15, 18; MED-06; EVT | 6 template, 6 jenis acara, aset ilustrasi berlisensi |
| M6 | Uang dan operasi | BIL, ADM, QA, OPS-01…04, 07, 08; DOM-14 | pembayaran sandbox→produksi, admin, monitoring, tes beban, rilis percobaan |
| M7 | Rilis | LCH | daftar periksa rilis lulus; rilis publik |

Urutan: M0 → M1 → M2 (buktikan mesin template dengan satu template) → M3 → M4 paralel M5 → M6 → M7. Jangan membangun template ke-2 sebelum `template:lint`, tes render per jenis acara, dan tes visual Classic Elegant hijau. Mulai TPL-15 (ilustrator) dan MED-06 (musik) sedini mungkin karena waktu tunggunya paling panjang.

## Daftar Lulus Template (DLT)
Manifest valid · render untuk tiap `event_types` dengan data contoh · 360/768/1280 px · mode kurangi gerakan · kontras ≥ 4,5:1 di semua palet · `template:lint` hijau (bobot ≤ anggaran manifest) · axe tanpa pelanggaran kritis · `preview.webp` 1200×800 + `thumb.webp` · tanpa font baru · lisensi aset di `assets/LICENSE.md` · disetujui pemilik secara visual.

Status `[x]` dibawa dari TODO sebelumnya; bukti tiap tugas tidak diverifikasi saat penggabungan.

## FND — Fondasi (M0)
| St | ID | Tugas | Dep | Selesai bila | Uk | M |
|---|---|---|---|---|---|---|
| [x] | FND-01 | Buat repo `momenikah`, proteksi cabang `main`, `.gitignore`, `.editorconfig`, salin `.claude/` | – | repo ada; PR wajib review | S | M0 |
| [x] | FND-02 | Pasang Laravel 13 (PHP 8.3+), MySQL + Redis via Docker Compose | FND-01 | `php artisan about` benar; `composer test` hijau | M | M0 |
| [x] | FND-03 | Pint, Larastan (naik bertahap), Pest, GitHub Actions (lint, stan, test, build) | FND-02 | PR merah bila salah satu gagal | M | M0 |
| [x] | FND-04 | Struktur `app/Domain/*` + Pest arch tests (Domain ↛ Http/Livewire/Filament) | FND-02 | arch test hijau dan gagal bila dilanggar | M | M0 |
| [x] | FND-05 | Tiga domain lokal (`momenikah.test`, `app.`, `undangan.`), `config/surfaces.php`, `routes/{marketing,app,public}.php` | FND-02 | ketiganya menampilkan halaman uji berbeda | S | M0 |
| [x] | FND-06 | Staging + skrip deploy tanpa downtime + cadangan DB harian + Sentry | FND-03, DEC-02 | deploy via tag; pemulihan cadangan diuji | L | M0 |
| [x] | FND-07 | Autentikasi (daftar, masuk, reset, verifikasi email) dengan starter kit Livewire resmi + throttle | FND-02 | tes auth hijau; throttle login terbukti | M | M0 |
| [x] | FND-08 | Middleware header keamanan + CSP per permukaan | FND-05 | tidak ada temuan merah di scanner header (staging) | M | M0 |
| [x] | FND-09 | `THIRD_PARTY.md`: lisensi GSAP (cek gsap.com), font OFL, paket, versi terpasang | FND-02 | file ada; GSAP dinyatakan boleh untuk produk komersial atau diganti | S | M0 |
| [ ] | FND-10 | `.env.example` lengkap + `config/momenikah.php` (domain publik, batas paket, saklar fitur) | FND-02 | aplikasi boot dari `.env.example`; tanpa rahasia di repo | S | M0 |
| [ ] | FND-11 | Dependabot/Renovate + `composer audit` dan `npm audit` di CI + pemeriksaan lisensi dependensi | FND-03 | PR pembaruan otomatis aktif; temuan audit membuat CI merah; lisensi tercatat di `THIRD_PARTY.md` | S | M0 |
| [ ] | FND-12 | Selaraskan `.claude` (CLAUDE.md, skill, aturan, dokumen) dengan keputusan DEC dan kode/versi yang benar-benar terpasang | FND-09, DEC-03 | tidak ada isi `.claude` yang bertentangan dengan kode nyata (host publik, struktur folder, nama perintah, versi) | S | M0 |
| [ ] | FND-13 | Turnstile + throttle di login, daftar, dan reset sandi | FND-07, PUB-09 | percobaan berlebih ditolak; tes throttle + Turnstile hijau | S | M3 |

## DS — Design system (M0/M4)
| St | ID | Tugas | Dep | Selesai bila | Uk | M |
|---|---|---|---|---|---|---|
| [x] | DS-01 | Token merek ke Tailwind 4 `@theme` (`tokens.css`): warna, huruf, skala, radius | FND-02 | `/styleguide` menampilkan token | M | M0 |
| [x] | DS-02 | Font self-host (Cormorant Garamond, Plus Jakarta Sans), subset latin, `font-display: swap` | DS-01 | 0 permintaan ke fonts.googleapis.com | S | M0 |
| [x] | DS-03 | Komponen Blade: button, field, input, select, textarea, card, badge, modal, toast, tabs, empty-state | DS-01 | semua tampil di `/styleguide` pada 360/768/1280 | L | M0 |
| [x] | DS-04 | Satu set ikon SVG inline | DS-01 | tanpa emoji sebagai ikon | S | M0 |
| [x] | DS-05 | Pasang `brand/` ke `public/` + `<x-seo.head>` (favicon, manifest, theme-color) | DS-01, DEC-10 | favicon tampil; manifest valid | S | M0 |
| [x] | DS-06 | Tiga layout (pemasaran, aplikasi, publik) | DS-03 | tiap permukaan memakai layout sendiri | M | M0 |
| [x] | DS-07 | Baseline aksesibilitas: fokus, skip link, axe di Playwright | DS-03 | axe 0 pelanggaran serius di `/styleguide` | M | M4 |
| [ ] | DS-08 | Komponen tambahan: alert, pagination; ukuran sentuh ≥ 44 px; kontras lulus | DS-03 | tampil di `/styleguide` pada 360/768/1280; axe 0 pelanggaran serius | S | M0 |

## DOM — Domain (M1)
| St | ID | Tugas | Dep | Selesai bila | Uk | M |
|---|---|---|---|---|---|---|
| [x] | DOM-01 | Enum `EventType` + `EventTypeRegistry` + `config/event-types.php` (6 jenis, `docs/05`) | FND-04 | tes registry untuk 6 jenis | M | M1 |
| [x] | DOM-02 | Migrasi users, invitations, invitation_people, invitation_events, invitation_story_items, invitation_gifts | DOM-01 | migrate + rollback bersih | M | M1 |
| [x] | DOM-03 | Migrasi guests, rsvps, guest_messages, invitation_daily_stats | DOM-02 | idem | M | M1 |
| [x] | DOM-04 | Migrasi plans, subscriptions, payments | DOM-02 | idem | M | M1 |
| [x] | DOM-05 | Model, factory, `$fillable`, casts, state factory per jenis acara | DOM-02 | factory tiap jenis menghasilkan data valid | M | M1 |
| [x] | DOM-06 | DTO `InvitationData` + Form Request per langkah editor + tes round-trip form↔model↔view-model | DOM-05 | semua field lulus round-trip | L | M1 |
| [x] | DOM-07 | Generator slug (nama + akhiran acak 4 karakter), daftar kata dilarang (`preview`, `admin`, …) | DOM-05 | tes unik + tolak reserved | S | M1 |
| [x] | DOM-08 | Policy kepemilikan + scope `ownedBy` | DOM-05 | tes IDOR gagal akses | M | M1 |
| [x] | DOM-09 | Siklus status draft→published→expired/archived + scheduler kedaluwarsa | DOM-05, DEC-01 | tes transisi sah dan ilegal (ilegal ditolak) + tes kedaluwarsa terjadwal | M | M1 |
| [x] | DOM-10 | `hasActivePlan()` + `Entitlements::can()` | DOM-04, DEC-01 | tes per paket | M | M1 |
| [x] | DOM-11 | Seeder demo fiktif untuk tiap jenis acara | DOM-05 | `db:seed --class=DemoSeeder` jalan | S | M1 |
| [ ] | DOM-12 | Audit log aksi penting (publish, hapus, moderasi, bayar) | DOM-05 | aksi tercatat dengan pengguna dan waktu; tanpa PII berlebih | S | M1 |
| [ ] | DOM-13 | Locale `id`, Carbon `id`, zona waktu acara (WIB/WITA/WIT) di `invitation_events.timezone` | DOM-02 | tanggal tampil berbahasa Indonesia; tes lintas zona waktu | S | M1 |
| [ ] | DOM-14 | Retensi data tamu: hapus/anonimkan otomatis setelah masa tenggang | DOM-09, DEC-13 | tes: data tamu hilang setelah masa tenggang; undangan lain aman | M | M6 |

## GST — Tamu
| St | ID | Tugas | Dep | Selesai bila | Uk | M |
|---|---|---|---|---|---|---|
| [x] | GST-01 | Impor tamu CSV (validasi, batas baris) + tambah manual | DOM-03 | tes impor valid/invalid | M | M1 |
| [x] | GST-02 | Kode unik per tamu + tautan `?g=` dan `?to=` + bagikan WhatsApp | GST-01 | tautan membuka undangan dengan nama benar | M | M1 |
| [ ] | GST-03 | Ekspor RSVP/ucapan CSV | PUB-09 | berkas sesuai data; tanpa injeksi rumus | S | M3 |

## MED — Media
| St | ID | Tugas | Dep | Selesai bila | Uk | M |
|---|---|---|---|---|---|---|
| [ ] | MED-01 | Spatie Media Library + disk S3-kompatibel + CDN | FND-02, DEC-04 | unggah masuk bucket; URL lewat CDN | M | M3 |
| [ ] | MED-02 | Konversi queue: WebP 480/960/1600, strip EXIF, thumbnail | MED-01 | file tiga ukuran; tanpa EXIF | M | M3 |
| [ ] | MED-03 | Validasi unggahan (mime asli, ukuran, dimensi) | MED-01 | tes file palsu ditolak | M | M3 |
| [ ] | MED-04 | Pembersih berkas yatim terjadwal | MED-01 | tes: yatim terhapus, terpakai aman | S | M3 |
| [ ] | MED-05 | Audio: unggah mp3/ogg terbatas + (opsional) katalog berlisensi | MED-01, DEC-06 | musik diputar setelah ketukan di iOS & Android; kontrol musik terlihat di semua bagian, berlabel, ≥ 44 px | M | M3 |
| [ ] | MED-06 (owner) | Pustaka musik 10 lagu berlisensi | DEC-06 | bukti lisensi tertulis tersimpan; lagu tersedia di pilihan editor | M | M5 |

## TPL — Mesin template (M2/M5)
| St | ID | Tugas | Dep | Selesai bila | Uk | M |
|---|---|---|---|---|---|---|
| [x] | TPL-01 | `TemplateRegistry` (scan manifest, validasi, cache `templates:cache`) | DOM-01 | manifest salah ditolak dengan pesan jelas | M | M2 |
| [x] | TPL-02 | `InvitationViewModel` + builder (kontrak tunggal) | DOM-06 | arch test: template tak memanggil Eloquent | L | M2 |
| [x] | TPL-03 | Namespace `templates::` + resolusi bagian (`@includeFirst` template → `_shared`) | TPL-01 | override per bagian terbukti | M | M2 |
| [x] | TPL-04 | Partial `_shared`: cover, opening, people, events, story, gallery, gift, rsvp, guestbook, countdown, map, music, closing | TPL-03, DS-01 | tiap partial tampil dari data contoh 6 jenis | L | M2 |
| [x] | TPL-05 | Vite: entri `invitation-core` + glob `theme.{css,js}` + chunk dinamis | FND-02 | halaman undangan hanya memuat core + 1 tema | M | M2 |
| [x] | TPL-06 | `invitation/core.js`: buka cover (bisa lewat keyboard), musik hanya setelah ketukan, hitung mundur sadar zona waktu (tak negatif setelah acara), salin + toast, lightbox, isi `?to=` aman | TPL-05 | tes browser; reduced-motion; tes XSS; musik tak berbunyi sebelum klik; ≤ 8 KB gzip; tanpa jQuery | L | M2 |
| [x] | TPL-07 | `php artisan template:make {slug}` (kerangka dari `docs/TEMPLATE_SPEC.md`) | TPL-03 | template baru jalan < 5 menit | M | M2 |
| [~] | TPL-08 | Template 1: Classic Elegant (acuan) | TPL-04, TPL-06, TPL-17 | Daftar Lulus Template (DLT) terpenuhi | L | M2 |
| [ ] | TPL-09 | Template 2: Floral Garden | TPL-08 | DLT terpenuhi | L | M5 |
| [ ] | TPL-10 | Template 3: Modern Minimal | TPL-08 | DLT terpenuhi | M | M5 |
| [ ] | TPL-11 | Template 4: Luxury Gold | TPL-08 | DLT terpenuhi | L | M5 |
| [ ] | TPL-12 | Template 5: Beach Sunset | TPL-08 | DLT terpenuhi | L | M5 |
| [ ] | TPL-13 | Template 6: Rustic Vintage | TPL-08 | DLT terpenuhi | L | M5 |
| [~] | TPL-14 | `php artisan template:lint` (manifest, bobot, font, dependensi, token wajib, kontras palet, `alt` ornamen) + Lighthouse CI pratinjau, masuk CI | TPL-05 | CI gagal bila bobot, LCP, atau CLS melewati anggaran manifest | M | M2 |
| [ ] | TPL-15 (owner) | Set ilustrasi orisinal per tema (ilustrator; alih hak; berkas sumber) | DEC-05 | kontrak tertulis; aset SVG + berkas sumber berlapis di `assets/` + `LICENSE.md` (hak cipta dialihkan) | L | M5 |
| [x] | TPL-16 | `TemplateSampleData` per jenis acara (fiktif) | DOM-11 | preview tiap template × jenis tampil | M | M2 |
| [x] | TPL-17 | Sistem ornamen: `<x-ornament>`, pustaka SVG `currentColor`, slot + z-index tetap (bg, pattern, frame, corner, content, divider, float, ui) | TPL-03, DS-04 | satu SVG diwarnai tiga tema lewat token; lapisan dekoratif `pointer-events: none` | M | M2 |
| [ ] | TPL-18 | Uji panduan: buat template ke-7 hanya dari `docs/TEMPLATE_SPEC.md` | TPL-07, TPL-13 | selesai < 1 hari kerja; panduan diperbaiki dari temuan | M | M5 |

## PUB — Undangan publik (M2/M3)
| St | ID | Tugas | Dep | Selesai bila | Uk | M |
|---|---|---|---|---|---|---|
| [x] | PUB-01 | Rute `undangan.` `/{slug}`, tanpa sesi/cookie | FND-05, DOM-07, DEC-03 | respons tanpa `Set-Cookie` | M | M2 |
| [ ] | PUB-02 | Controller undangan + halaman 404/410 ramah | PUB-01, TPL-03 | tes 200/404/410 | M | M2 |
| [ ] | PUB-03 | Cache halaman penuh Redis + invalidasi saat update | PUB-02 | hit cache terbukti; edit membuang cache | M | M2 |
| [x] | PUB-04 | `/preview/{template}` (pembungkus noindex + iframe frame, mode ponsel/desktop) | TPL-03 | header `X-Robots-Tag: noindex`; `frame-ancestors` benar | M | M2 |
| [ ] | PUB-05 | Alias slug lama → 301 | PUB-02 | tes redirect | S | M3 |
| [ ] | PUB-06 | Statistik harian unik; abaikan bot dan preview | PUB-02 | tes hitung sekali per pengunjung/hari; bot dan preview tidak terhitung | M | M3 |
| [ ] | PUB-07 | OG image per undangan (queue) + fallback `og-default` | MED-01 | pratinjau WhatsApp tampil benar; tanpa nama tamu di OG | L | M3 |
| [ ] | PUB-08 | Meta undangan: noindex, judul, og:*, theme-color | PUB-02 | tes meta; noindex ganda (meta + `X-Robots-Tag`) | S | M3 |
| [ ] | PUB-09 | Endpoint RSVP (throttle, honeypot, Turnstile) | DOM-03 | tes spam/valid | M | M3 |
| [ ] | PUB-10 | Buku tamu: kirim + muat bertahap (cursor) | DOM-03 | tes paginasi + throttle; HTML ucapan di-escape | M | M3 |
| [ ] | PUB-11 | Moderasi ucapan dan RSVP (sembunyikan/hapus) di dasbor | DSH-01, PUB-10, DOM-12 | tes policy; aksi tercatat di audit log | S | M3 |
| [x] | PUB-12 | Nama tamu dari `?to=`/`?g=` aman | GST-02, TPL-06 | tes XSS: payload tampil sebagai teks; dipotong 60 karakter; tanpa `?to=` tampil "Tamu Undangan" | S | M3 |
| [ ] | PUB-13 | Tombol "Tambah ke Kalender" (`.ics`) per acara + tautan peta | PUB-02, DOM-13 | `.ics` valid di Google dan Apple Kalender; zona waktu benar; tautan peta terbuka | M | M3 |
| [ ] | PUB-14 | Amplop digital: rekening, e-wallet, alamat, QR, salin + toast | TPL-04, TPL-06 | salin bekerja di iOS dan Android; QR dimuat lazy | M | M3 |
| [ ] | PUB-15 | Galeri foto + lightbox lazy | TPL-04, TPL-06 | lightbox bisa lewat keyboard; gambar lazy; CLS < 0,1 | M | M3 |
| [ ] | PUB-16 | Ping "sudah buka" per tamu lewat `?g=` | PUB-02, GST-02 | `guests.opened_at` terisi sekali; bot diabaikan | S | M3 |
| [ ] | PUB-17 | Aksesibilitas undangan: fokus, kontras, label, `lang="id"`, reduced-motion | TPL-08 | axe tanpa pelanggaran kritis; mode kurangi gerakan diuji manual | M | M3 |

## DSH — Dasbor pembuat (M3)
| St | ID | Tugas | Dep | Selesai bila | Uk | M |
|---|---|---|---|---|---|---|
| [x] | DSH-01 | Shell dasbor: navigasi, daftar undangan, empty-state | DS-06, DOM-08 | pengguna hanya melihat miliknya | M | M3 |
| [x] | DSH-02 | Wizard buat: pilih jenis acara → pilih template (kartu + preview) | DOM-01, TPL-16 | undangan draf terbentuk | M | M3 |
| [ ] | DSH-03 | Editor Livewire per langkah (dasar, orang, acara+lokasi, cerita, galeri, amplop, musik, bagian) dengan autosave dan validasi inline | DOM-06, MED-02 | tes: tidak ada field hilang | L | M3 |
| [ ] | DSH-04 | Pratinjau langsung di editor (iframe draf) | PUB-04, DSH-03 | perubahan terlihat < 2 dtk | M | M3 |
| [ ] | DSH-05 | Publikasi dan unpublish: cek kelengkapan, slug, bagikan WhatsApp | DSH-03, DOM-09 | publish gagal bila data wajib kosong; undangan terbit dan terbuka; unpublish kembali ke draf | M | M3 |
| [ ] | DSH-06 | Manajemen tamu (impor, tautan, status buka) | GST-02 | impor 200 baris berhasil; tautan `?to=` ter-URL-encode; tampil dan bisa disalin | M | M3 |
| [ ] | DSH-07 | Statistik: tampilan, RSVP, ucapan | PUB-06 | angka sesuai data uji | M | M3 |
| [ ] | DSH-08 | Ganti template tanpa kehilangan data | TPL-02 | tes: data identik sebelum/sesudah | M | M3 |
| [ ] | DSH-09 | Duplikasi undangan | DSH-03 | salinan mandiri; media ikut tersalin | S | M3 |
| [ ] | DSH-10 | Pengaturan akun + ekspor/hapus data (UU PDP) | FND-07 | hapus menghapus media & tamu | M | M3 |
| [ ] | DSH-11 | Notifikasi email pemilik: RSVP baru, publish berhasil, masa aktif hampir habis | PUB-09, OPS-03 | email bermerek terkirim di staging | M | M3 |
| [ ] | DSH-12 | Unggah media di editor: seret-lepas, progres, urutan, batas ukuran | MED-03, DSH-03 | foto bisa diurutkan; berkas terlalu besar ditolak dengan pesan jelas | M | M3 |
| [ ] | DSH-13 | Pengaturan undangan: slug (cek unik langsung), judul, musik, palet, tingkat gerak, bahasa | DSH-03, DOM-07 | slug unik dicek langsung; palet berubah di pratinjau | M | M3 |
| [ ] | DSH-14 | Onboarding pengguna baru: daftar tugas awal + tips | DSH-01 | tampil untuk pengguna baru; bisa ditutup | S | M3 |

## BIL — Pembayaran (M6)
| St | ID | Tugas | Dep | Selesai bila | Uk | M |
|---|---|---|---|---|---|---|
| [ ] | BIL-01 | `PaymentGateway` + adapter Duitku (sandbox) | DOM-04 | transaksi sandbox terbuat | M | M6 |
| [ ] | BIL-02 | Checkout + webhook (signature, idempoten, jumlah dari server) | BIL-01 | tes 3 skenario serangan | L | M6 |
| [ ] | BIL-03 | Penegakan entitlement lewat satu pintu (`Entitlements`): foto, template premium, masa aktif | DEC-01, DOM-10 | tes per paket; tak ada pemeriksaan paket tersebar di controller | M | M6 |
| [ ] | BIL-04 | Riwayat pembayaran + email bukti | BIL-02 | email terkirim di staging | M | M6 |
| [ ] | BIL-05 | Tes pembayaran (callback palsu, duplikat, jumlah beda) | BIL-02 | semua ditolak/aman | M | M6 |
| [ ] | BIL-06 | Seeder paket (`plans`) sesuai DEC-01 | DEC-01, DOM-04 | tabel `plans` terisi lewat seeder; tanpa harga fiktif | S | M6 |
| [ ] | BIL-07 | Aktivasi paket setelah bayar: masa aktif, kedaluwarsa terjadwal | BIL-02, DOM-09 | undangan berbayar aktif setelah bayar dan kedaluwarsa tepat waktu | M | M6 |
| [ ] | BIL-08 | Voucher dan kode promo | BIL-02 | voucher kedaluwarsa atau melebihi batas ditolak | M | M6 |
| [ ] | BIL-09 | Refund dan sengketa manual (admin menandai, tercatat) | BIL-02, ADM-02 | hasil tercatat di audit log | M | M6 |

## MKT — Pemasaran dan landing (M4)
| St | ID | Tugas | Dep | Selesai bila | Uk | M |
|---|---|---|---|---|---|---|
| [~] | MKT-01 | Landing GSAP sesuai `docs/LANDING.md`: intro cincin-mengait, headline, ponsel amplop, kisah scroll 3 langkah, pemilih gaya (dari `landing/index.html`) | DS-06, FND-09 | 60 fps di ponsel menengah; reduced-motion benar; teks hero terbaca tanpa JS | L | M4 |
| [ ] | MKT-02 | Bagian + halaman `/jenis-acara/{slug}` (6) | MKT-01, DOM-01 | 6 halaman unik | M | M4 |
| [ ] | MKT-03 | Katalog `/template` (filter jenis & gaya) + detail `/template/{slug}` | TPL-08, PUB-04 | filter jalan; iframe preview tampil | M | M4 |
| [ ] | MKT-04 | Fitur + FAQ | MKT-01 | FAQPage valid | S | M4 |
| [ ] | MKT-05 | Halaman harga | DEC-01 | angka dari `plans` (tanpa harga fiktif) | S | M4 |
| [ ] | MKT-06 | Blog/panduan (Filament + Markdown) | ADM-01 | 3 artikel awal terbit (target 10 sebelum LCH-01); penulis + tanggal; masuk sitemap | M | M4 |
| [ ] | MKT-07 | Anggaran performa landing: GSAP+ScrollTrigger ≤ 120 KB gzip, init malas, QA reduced-motion | MKT-01 | Lighthouse CI lulus | M | M4 |
| [ ] | MKT-08 | Footer, kebijakan privasi, syarat, kontak (draf ditinjau ahli hukum) | – | halaman tayang; draf legal sudah ditinjau | M | M4 |

## SEO
| St | ID | Tugas | Dep | Selesai bila | Uk | M |
|---|---|---|---|---|---|---|
| [ ] | SEO-01 | `Seo` value object + `<x-seo.head>` | DS-06 | tiap halaman punya title/desc/canonical/OG unik | M | M4 |
| [ ] | SEO-02 | JSON-LD: Organization, WebSite, SoftwareApplication, FAQPage, BreadcrumbList | SEO-01 | Rich Results Test valid; tanpa AggregateRating atau Review | M | M4 |
| [ ] | SEO-03 | `sitemap.xml` + `robots.txt` per domain | SEO-01 | hanya pemasaran dalam sitemap | S | M4 |
| [ ] | SEO-04 | Meta dan konten per jenis acara + riset kata kunci di Search Console | MKT-02 | judul/deskripsi unik per halaman | M | M4 |
| [ ] | SEO-05 | Search Console, Bing Webmaster, GA4 + banner consent + peristiwa `start_create`, `publish`, `share_whatsapp` | MKT-01 | properti terverifikasi; pelacak aktif hanya setelah persetujuan | S | M4 |
| [ ] | SEO-06 | Aturan canonical/redirect (www, https, slash) | FND-05 | tes redirect | S | M4 |
| [ ] | SEO-07 | Lighthouse CI di GitHub Actions | MKT-01 | SEO ≥ 95 lulus; ambang performa tercatat | M | M4 |
| [ ] | SEO-08 | OG khusus tiap template di katalog | MKT-03, SEO-01 | tautan template menampilkan gambar yang tepat di WhatsApp | S | M4 |

## EVT — Jenis acara (M2–M5)
| St | ID | Tugas | Dep | Selesai bila | Uk | M |
|---|---|---|---|---|---|---|
| [ ] | EVT-01 | Walimatul Urs: label, salinan, ayat/doa opsional (diaktifkan pengguna), dress code, catatan tempat | DOM-01, TPL-04, DEC-11 | undangan walimah terbit dari editor; teks Arab/doa ditinjau ahli; tes kontrak (QA-06) lulus | M | M5 |
| [ ] | EVT-02 | Tunangan/Lamaran: dua keluarga, acara lamaran, rundown | DOM-01, TPL-04, DEC-11 | undangan tunangan/lamaran terbit dari editor; tes kontrak (QA-06) lulus | M | M5 |
| [ ] | EVT-03 | Khitanan: data anak, orang tua, doa, satu acara | DOM-01, TPL-04, DEC-11 | undangan khitanan terbit dari editor; data anak minimal; tes kontrak (QA-06) lulus | M | M5 |
| [ ] | EVT-04 | Aqiqah: data bayi (lahir, arti nama, berat/panjang opsional), orang tua, doa | DOM-01, TPL-04, DEC-11 | undangan aqiqah terbit dari editor; label peran sesuai jenis; tes kontrak (QA-06) lulus | M | M5 |
| [ ] | EVT-05 | Ulang Tahun: usia/tonggak, tema, galeri perjalanan, daftar keinginan opsional | DOM-01, TPL-04, DEC-11 | undangan ulang tahun terbit dari editor; tes kontrak (QA-06) lulus | M | M5 |
| [ ] | EVT-06 | Tandai `events` di tiap manifest + data contoh per jenis | TPL-08…13 | katalog menyaring dengan benar | M | M5 |
| [ ] | EVT-07 | OG + SEO per jenis acara | EVT-01…05, SEO-04 | OG tiap jenis ada | S | M5 |

## ADM — Admin Filament (M6)
| St | ID | Tugas | Dep | Selesai bila | Uk | M |
|---|---|---|---|---|---|---|
| [ ] | ADM-01 | Panel `/admin` hanya admin + 2FA | FND-07 | non-admin 403 | M | M6 |
| [ ] | ADM-02 | Resource: pengguna, undangan (baca), template, paket, pembayaran | ADM-01, DOM-04 | CRUD sesuai policy | L | M6 |
| [ ] | ADM-03 | Moderasi + laporan penyalahgunaan | ADM-02 | undangan bisa disuspend | M | M6 |
| [ ] | ADM-04 | Dasbor metrik admin | ADM-02, PUB-06 | angka cocok data | M | M6 |

## QA / OPS
| St | ID | Tugas | Dep | Selesai bila | Uk | M |
|---|---|---|---|---|---|---|
| [ ] | QA-01 | Tes fitur alur inti (daftar → buat → publikasi → buka → RSVP → ucapan → bayar) | DSH-05 | hijau di CI | M | M6 |
| [ ] | QA-02 | Playwright e2e + visual 360/768/1280 per template | TPL-08 | baseline tersimpan | L | M6 |
| [ ] | QA-03 | Uji beban k6 halaman undangan (lonjakan) | PUB-03 | p95 tercatat, tanpa error | M | M6 |
| [ ] | QA-04 | Uji perangkat nyata (Android murah, iOS Safari; autoplay musik) | TPL-06 | catatan hasil | M | M6 |
| [ ] | QA-05 | Peninjauan keamanan (OWASP, `composer audit`, `npm audit`) | DSH-05, PUB-09, BIL-02 | tak ada temuan tinggi terbuka | M | M6 |
| [ ] | QA-06 | Tes kontrak field: definisi jenis acara ↔ form ↔ model ↔ view-model ↔ template | DOM-06, TPL-04 | tes gagal bila ada field yang hilang di salah satu lapisan | M | M3 |
| [ ] | OPS-01 | Latihan backup & restore + runbook insiden | FND-06 | pulih dalam waktu tercatat; runbook tersimpan | M | M6 |
| [ ] | OPS-02 | Monitoring uptime + alarm Sentry/Pulse | FND-06 | alarm uji terkirim | S | M6 |
| [ ] | OPS-03 | Supervisor (queue), scheduler, health route | FND-06 | pekerjaan berjalan setelah reboot | S | M6 |
| [ ] | OPS-04 | Aturan CDN/cache (aset immutable) | MED-01 | header cache benar | S | M6 |
| [ ] | OPS-05 | Cloudflare: DNS tiga permukaan, aturan cache (abaikan parameter `to`), WAF dasar, Turnstile (kunci staging) | FND-06, DEC-02 | tiap permukaan melayani halaman uji; aturan cache terdokumentasi; Turnstile aktif di staging | M | M0 |
| [ ] | OPS-06 | Cache edge halaman undangan + purge lewat API Cloudflare saat publish, edit, kedaluwarsa | PUB-03, OPS-05 | cache hit melayani halaman; perubahan tampil setelah purge dalam hitungan detik | M | M3 |
| [ ] | OPS-07 | Migrasi data + redirect 301 dari `momenikah.online` lama (`/u/{slug}`), bila DEC-12 menyatakan ada pengguna | PUB-05, DEC-12 | uji pada salinan data; semua tautan lama terbuka lewat redirect | M | M6 |
| [ ] | OPS-08 | Rilis percobaan di staging: smoke test, rollback dilatih, cutover DNS | QA-05, QA-02, OPS-01, OPS-02 | rilis percobaan berhasil; rollback dilatih | M | M6 |

## LCH — Rilis
| St | ID | Tugas | Dep | Selesai bila | Uk | M |
|---|---|---|---|---|---|---|
| [ ] | LCH-01 (owner) | Beta tertutup 10–20 pengguna (undangan nyata kecil) + formulir umpan balik | QA-01, QA-05, OPS-08, BIL-05, ADM-03 | daftar masalah terpilah; masalah prioritas tinggi diperbaiki sebelum LCH-02 | M | M7 |
| [ ] | LCH-02 (owner) | Daftar periksa rilis: DEC terjawab, logo disetujui, privasi/syarat, cadangan, monitoring | LCH-01 | semua tercentang | S | M7 |
| [ ] | LCH-03 | Backlog pasca-rilis: domain kustom, reseller/hapus brand, tiket QR + check-in, notifikasi WhatsApp resmi, realtime, bahasa lain, livestream (embed), CSS/JS kustom ber-sandbox, bantuan penulisan teks, undangan bersandi, riwayat versi draf | LCH-02 | tiap item punya ADR sebelum dikerjakan (undangan bersandi dan CSS/JS kustom bertentangan dengan aturan tanpa sesi/skrip pengguna) | – | M7 |

## Lampiran: pemetaan ID lama (`docs/TODO.md`, F0–F10) → ID sekarang
| Lama | Sekarang | Lama | Sekarang |
|---|---|---|---|
| F0-01 | DEC-01…13 | F4-01 | DSH-01 |
| F0-02 | FND-01 | F4-02 | DSH-02 |
| F0-03 | FND-02, FND-03 | F4-03 | DSH-03 |
| F0-04 | DS-01 | F4-04 | DSH-04 |
| F0-05 | FND-10 | F4-05 | DSH-12 |
| F0-06 | FND-03, FND-11 | F4-06 | DSH-13 |
| F0-07 | FND-06 | F4-07 | GST-01, GST-02, DSH-06 |
| F0-08 | FND-06, OPS-02 | F4-08 | DSH-07, GST-03 |
| F0-09 | FND-11 | F4-09 | PUB-11 |
| F0-10 | FND-12 | F4-10 | DSH-05, DSH-09 |
| F0-11 | OPS-05 | F4-11 | DSH-08 |
| F0-12 | SEO-05 | F4-12 | LCH-03 (backlog) |
| F1-01 | FND-07 | F4-13 | DSH-14 |
| F1-02 | FND-13, PUB-09 | F4-14 | DSH-11 |
| F1-03 | DOM-02…04 | F5-01 | TPL-15 |
| F1-04 | DOM-01 | F5-02…07 | TPL-08…13 |
| F1-05 | DOM-05, DOM-08 | F5-08 | MED-06 |
| F1-06 | MED-01…03 | F5-09 | TPL-18 |
| F1-07 | DOM-07 | F6-01 | DEC-01, BIL-06 |
| F1-08 | TPL-02, TPL-16 | F6-02 | BIL-01, BIL-02 |
| F1-09 | DOM-09 | F6-03 | BIL-07 |
| F1-10 | MED-04, OPS-03 | F6-04 | BIL-03 |
| F1-11 | DOM-11 | F6-05 | BIL-08 |
| F1-12 | DOM-12 | F6-06 | ADM-01, ADM-02 |
| F1-13 | DOM-13 | F6-07 | BIL-04 |
| F2-01 | DS-01 | F6-08 | BIL-09 |
| F2-02 | DS-02 | F6-09 | LCH-03 (backlog) |
| F2-03 | DS-03, DS-08 | F7-01 | DS-06, MKT-08 |
| F2-04 | DS-06 | F7-02 | MKT-01 |
| F2-05 | TPL-01, TPL-03 | F7-03 | MKT-03 |
| F2-06 | TPL-05 | F7-04 | MKT-02 |
| F2-07 | TPL-04 | F7-05 | SEO-01…03 |
| F2-08 | TPL-17 | F7-06 | DS-05 |
| F2-09 | TPL-06 | F7-07 | MKT-04, MKT-05 |
| F2-10 | PUB-04 | F7-08 | MKT-06 |
| F2-11 | TPL-16 | F7-09 | SEO-05 |
| F2-12 | TPL-07, TPL-14 | F7-10 | SEO-08 |
| F2-13 | DS-01 (`/styleguide`) | F7-11 | MKT-07 |
| F2-14 | TPL-14 | F7-12 | SEO-02 |
| F3-01 | PUB-01, PUB-02 | F8-01 | QA-01 |
| F3-02 | PUB-12 | F8-02 | QA-06 |
| F3-03, F3-04 | TPL-06 | F8-03 | QA-05 |
| F3-05 | PUB-13 | F8-04 | QA-02 |
| F3-06 | PUB-09 | F8-05 | QA-03 |
| F3-07 | PUB-10 | F8-06 | OPS-01 |
| F3-08 | PUB-14 | F8-07 | DOM-14, MKT-08, DSH-10 |
| F3-09 | PUB-15 | F8-08 | OPS-07 |
| F3-10 | MED-05 | F8-09 | OPS-08 |
| F3-11 | PUB-06, PUB-16 | F8-10, F8-11 | LCH-01 |
| F3-12 | PUB-07, PUB-08 | F9-01…05 | EVT-01…05 |
| F3-13 | OPS-06 | F9-06 | EVT-07, SEO-04 |
| F3-14 | PUB-17 | F10-01…06 | LCH-03 (backlog) |
| F3-15 | LCH-03 (backlog) | F3-16 | PUB-02 |
