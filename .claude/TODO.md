# TODO — Momenikah (greenfield)

Status: `[ ]` belum · `[~]` dikerjakan · `[x]` selesai (bukti ada) · `[!]` menunggu keputusan `DEC-nn` · `(owner)` tindakan pemilik. Ukuran S/M/L: lihat `docs/08-roadmap.md`. Satu ID = satu perubahan. Kolom **Selesai bila** adalah kriteria yang harus dibuktikan.

## FND — Fondasi (M0)
| St | ID | Tugas | Dep | Selesai bila | Uk |
|---|---|---|---|---|---|
| [x] | FND-01 | Buat repo `momenikah`, proteksi cabang `main`, `.gitignore`, `.editorconfig`, salin `.claude/` | – | repo ada; PR wajib review | S |
| [x] | FND-02 | Pasang Laravel 13 (PHP 8.3+), MySQL + Redis via Docker Compose | FND-01 | `php artisan about` benar; `composer test` hijau | M |
| [x] | FND-03 | Pint, Larastan (naik bertahap), Pest, GitHub Actions (lint, stan, test, build) | FND-02 | PR merah bila salah satu gagal | M |
| [x] | FND-04 | Struktur `app/Domain/*` + Pest arch tests (Domain ↛ Http/Livewire/Filament) | FND-02 | arch test hijau dan gagal bila dilanggar | M |
| [x] | FND-05 | Tiga domain lokal (`momenikah.test`, `app.`, `undangan.`), `config/surfaces.php`, `routes/{marketing,app,public}.php` | FND-02 | ketiganya menampilkan halaman uji berbeda | S |
| [x] | FND-06 | Staging + skrip deploy tanpa downtime + cadangan DB harian + Sentry | FND-03, DEC-02 | deploy via tag; pemulihan cadangan diuji | L |
| [x] | FND-07 | Autentikasi (daftar, masuk, reset, verifikasi email) dengan starter kit Livewire resmi + throttle | FND-02 | tes auth hijau; throttle login terbukti | M |
| [x] | FND-08 | Middleware header keamanan + CSP per permukaan | FND-05 | tidak ada temuan merah di scanner header (staging) | M |
| [x] | FND-09 | `THIRD_PARTY.md`: lisensi GSAP (cek gsap.com), font OFL, paket, versi terpasang | FND-02 | file ada; GSAP dinyatakan boleh untuk produk komersial atau diganti | S |

## DS — Design system (M0/M4)
| St | ID | Tugas | Dep | Selesai bila | Uk |
|---|---|---|---|---|---|
| [x] | DS-01 | Token merek ke Tailwind 4 `@theme` (`tokens.css`): warna, huruf, skala, radius | FND-02 | `/styleguide` menampilkan token | M |
| [x] | DS-02 | Font self-host (Cormorant Garamond, Plus Jakarta Sans), subset latin, `font-display: swap` | DS-01 | 0 permintaan ke fonts.googleapis.com | S |
| [x] | DS-03 | Komponen Blade: button, field, input, select, textarea, card, badge, modal, toast, tabs, empty-state | DS-01 | semua tampil di `/styleguide` pada 360/768/1280 | L |
| [x] | DS-04 | Satu set ikon SVG inline | DS-01 | tanpa emoji sebagai ikon | S |
| [x] | DS-05 | Pasang `brand/` ke `public/` + `<x-seo.head>` (favicon, manifest, theme-color) | DS-01, DEC-10 | favicon tampil; manifest valid | S |
| [x] | DS-06 | Tiga layout (pemasaran, aplikasi, publik) | DS-03 | tiap permukaan memakai layout sendiri | M |
| [x] | DS-07 | Baseline aksesibilitas: fokus, skip link, axe di Playwright | DS-03 | axe 0 pelanggaran serius di `/styleguide` | M |

## DOM — Domain (M1)
| St | ID | Tugas | Dep | Selesai bila | Uk |
|---|---|---|---|---|---|
| [x] | DOM-01 | Enum `EventType` + `EventTypeRegistry` + `config/event-types.php` (6 jenis, `docs/05`) | FND-04 | tes registry untuk 6 jenis | M |
| [x] | DOM-02 | Migrasi users, invitations, invitation_people, invitation_events, invitation_story_items, invitation_gifts | DOM-01 | migrate + rollback bersih | M |
| [x] | DOM-03 | Migrasi guests, rsvps, guest_messages, invitation_daily_stats | DOM-02 | idem | M |
| [x] | DOM-04 | Migrasi plans, subscriptions, payments | DOM-02 | idem | M |
| [x] | DOM-05 | Model, factory, `$fillable`, casts, state factory per jenis acara | DOM-02 | factory tiap jenis menghasilkan data valid | M |
| [x] | DOM-06 | DTO `InvitationData` + Form Request per langkah editor + tes round-trip form↔model↔view-model | DOM-05 | semua field lulus round-trip | L |
| [x] | DOM-07 | Generator slug (nama + akhiran acak 4 karakter), daftar kata dilarang (`preview`, `admin`, …) | DOM-05 | tes unik + tolak reserved | S |
| [x] | DOM-08 | Policy kepemilikan + scope `ownedBy` | DOM-05 | tes IDOR gagal akses | M |
| [ ] | DOM-09 | Siklus status draft→published→expired/archived + scheduler kedaluwarsa | DOM-05, DEC-01 | tes transisi dan kedaluwarsa | M |
| [x] | DOM-10 | `hasActivePlan()` + `Entitlements::can()` | DOM-04, DEC-01 | tes per paket | M |
| [x] | DOM-11 | Seeder demo fiktif untuk tiap jenis acara | DOM-05 | `db:seed --class=DemoSeeder` jalan | S |

## GST — Tamu
| St | ID | Tugas | Dep | Selesai bila | Uk |
|---|---|---|---|---|---|
| [ ] | GST-01 | Impor tamu CSV (validasi, batas baris) + tambah manual | DOM-03 | tes impor valid/invalid | M |
| [ ] | GST-02 | Kode unik per tamu + tautan `?g=` dan `?to=` + bagikan WhatsApp | GST-01 | tautan membuka undangan dengan nama benar | M |
| [ ] | GST-03 | Ekspor RSVP/ucapan CSV | PUB-09 | berkas sesuai data; tanpa injeksi rumus | S |

## MED — Media
| St | ID | Tugas | Dep | Selesai bila | Uk |
|---|---|---|---|---|---|
| [ ] | MED-01 | Spatie Media Library + disk S3-kompatibel + CDN | FND-02, DEC-04 | unggah masuk bucket; URL lewat CDN | M |
| [ ] | MED-02 | Konversi queue: WebP 480/960/1600, strip EXIF, thumbnail | MED-01 | file tiga ukuran; tanpa EXIF | M |
| [ ] | MED-03 | Validasi unggahan (mime asli, ukuran, dimensi) | MED-01 | tes file palsu ditolak | M |
| [ ] | MED-04 | Pembersih berkas yatim terjadwal | MED-01 | tes: yatim terhapus, terpakai aman | S |
| [ ] | MED-05 | Audio: unggah mp3/ogg terbatas + (opsional) katalog berlisensi | MED-01, DEC-06 | musik diputar setelah ketukan di iOS & Android | M |

## TPL — Mesin template (M2/M5)
| St | ID | Tugas | Dep | Selesai bila | Uk |
|---|---|---|---|---|---|
| [ ] | TPL-01 | `TemplateRegistry` (scan manifest, validasi, cache `templates:cache`) | DOM-01 | manifest salah ditolak dengan pesan jelas | M |
| [ ] | TPL-02 | `InvitationViewModel` + builder (kontrak tunggal) | DOM-06 | arch test: template tak memanggil Eloquent | L |
| [ ] | TPL-03 | Namespace `templates::` + resolusi bagian (`@includeFirst` template → `_shared`) | TPL-01 | override per bagian terbukti | M |
| [ ] | TPL-04 | Partial `_shared`: cover, opening, people, events, story, gallery, gift, rsvp, guestbook, countdown, map, music, closing | TPL-03, DS-01 | tiap partial tampil dari data contoh 6 jenis | L |
| [ ] | TPL-05 | Vite: entri `invitation-core` + glob `theme.{css,js}` + chunk dinamis | FND-02 | halaman undangan hanya memuat core + 1 tema | M |
| [ ] | TPL-06 | `invitation/core.js`: buka cover, musik, hitung mundur, salin+toast, lightbox, isi `?to=` aman | TPL-05 | tes browser; reduced-motion; XSS tes | L |
| [ ] | TPL-07 | `php artisan make:template {slug}` | TPL-03 | template baru jalan < 5 menit | M |
| [ ] | TPL-08 | Template 1: Classic Elegant (acuan) | TPL-04, TPL-06 | daftar periksa template lulus (`docs/04`) | L |
| [ ] | TPL-09 | Template 2: Floral Garden | TPL-08 | idem | L |
| [ ] | TPL-10 | Template 3: Modern Minimal | TPL-08 | idem | M |
| [ ] | TPL-11 | Template 4: Luxury Gold | TPL-08 | idem | L |
| [ ] | TPL-12 | Template 5: Beach Sunset | TPL-08 | idem | L |
| [ ] | TPL-13 | Template 6: Rustic Vintage | TPL-08 | idem | L |
| [ ] | TPL-14 | `templates:audit` (ukuran, font, dependensi) + masuk CI | TPL-05 | CI gagal bila melewati anggaran | M |
| [ ] | TPL-15 (owner) | Set ilustrasi orisinal per tema (ilustrator; alih hak; berkas sumber) | DEC-05 | file di `assets/` + `LICENSE.md` | L |
| [ ] | TPL-16 | `TemplateSampleData` per jenis acara (fiktif) | DOM-11 | preview tiap template × jenis tampil | M |

## PUB — Undangan publik (M2/M3)
| St | ID | Tugas | Dep | Selesai bila | Uk |
|---|---|---|---|---|---|
| [ ] | PUB-01 | Rute `undangan.` `/{slug}`, tanpa sesi/cookie | FND-05, DOM-07, DEC-03 | respons tanpa `Set-Cookie` | M |
| [ ] | PUB-02 | Controller undangan + halaman 404/410 ramah | PUB-01, TPL-03 | tes 200/404/410 | M |
| [ ] | PUB-03 | Cache halaman penuh Redis + invalidasi saat update | PUB-02 | hit cache terbukti; edit membuang cache | M |
| [ ] | PUB-04 | `/preview/{template}` (pembungkus noindex + iframe frame, mode ponsel/desktop) | TPL-03 | header `X-Robots-Tag: noindex`; `frame-ancestors` benar | M |
| [ ] | PUB-05 | Alias slug lama → 301 | PUB-02 | tes redirect | S |
| [ ] | PUB-06 | Statistik harian unik; abaikan bot dan preview | PUB-02 | tes hitung sekali per pengunjung/hari | M |
| [ ] | PUB-07 | OG image per undangan (queue) + fallback `og-default` | MED-01 | pratinjau WhatsApp tampil benar | L |
| [ ] | PUB-08 | Meta undangan: noindex, judul, og:*, theme-color | PUB-02 | tes meta | S |
| [ ] | PUB-09 | Endpoint RSVP (throttle, honeypot, Turnstile) | DOM-03 | tes spam/valid | M |
| [ ] | PUB-10 | Buku tamu: kirim + muat bertahap (cursor) | DOM-03 | tes paginasi + throttle | M |
| [ ] | PUB-11 | Moderasi ucapan (sembunyikan/hapus) di dasbor | DSH-01, PUB-10 | tes policy | S |
| [ ] | PUB-12 | Nama tamu dari `?to=`/`?g=` aman | GST-02, TPL-06 | tes XSS payload tampil sebagai teks | S |

## DSH — Dasbor pembuat (M3)
| St | ID | Tugas | Dep | Selesai bila | Uk |
|---|---|---|---|---|---|
| [ ] | DSH-01 | Shell dasbor: navigasi, daftar undangan, empty-state | DS-06, DOM-08 | pengguna hanya melihat miliknya | M |
| [ ] | DSH-02 | Wizard buat: pilih jenis acara → pilih template (kartu + preview) | DOM-01, TPL-16 | undangan draf terbentuk | M |
| [ ] | DSH-03 | Editor Livewire per langkah (dasar, orang, acara+lokasi, cerita, galeri, amplop, musik, bagian) dengan autosave dan validasi inline | DOM-06, MED-02 | tes: tidak ada field hilang | L |
| [ ] | DSH-04 | Pratinjau langsung di editor (iframe draf) | PUB-04, DSH-03 | perubahan terlihat < 2 dtk | M |
| [ ] | DSH-05 | Publikasi: cek kelengkapan, slug, bagikan WhatsApp | DSH-03, DOM-09 | undangan terbit dan terbuka | M |
| [ ] | DSH-06 | Manajemen tamu (impor, tautan, status buka) | GST-02 | tampil dan bisa disalin | M |
| [ ] | DSH-07 | Statistik: tampilan, RSVP, ucapan | PUB-06 | angka sesuai data uji | M |
| [ ] | DSH-08 | Ganti template tanpa kehilangan data | TPL-02 | tes: data identik sebelum/sesudah | M |
| [ ] | DSH-09 | Duplikasi undangan | DSH-03 | salinan mandiri | S |
| [ ] | DSH-10 | Pengaturan akun + ekspor/hapus data (UU PDP) | FND-07 | hapus menghapus media & tamu | M |

## BIL — Pembayaran (M6)
| St | ID | Tugas | Dep | Selesai bila | Uk |
|---|---|---|---|---|---|
| [ ] | BIL-01 | `PaymentGateway` + adapter Duitku (sandbox) | DOM-04 | transaksi sandbox terbuat | M |
| [ ] | BIL-02 | Checkout + webhook (signature, idempoten, jumlah dari server) | BIL-01 | tes 3 skenario serangan | L |
| [ ] | BIL-03 | Penegakan entitlement (foto, template premium, masa aktif) | DEC-01, DOM-10 | tes per paket | M |
| [ ] | BIL-04 | Riwayat pembayaran + email bukti | BIL-02 | email terkirim di staging | M |
| [ ] | BIL-05 | Tes pembayaran (callback palsu, duplikat, jumlah beda) | BIL-02 | semua ditolak/aman | M |

## MKT — Pemasaran dan landing (M4)
| St | ID | Tugas | Dep | Selesai bila | Uk |
|---|---|---|---|---|---|
| [ ] | MKT-01 | Landing GSAP: intro cincin-mengait, headline, ponsel amplop, kisah scroll 3 langkah, pemilih gaya (dari `landing/index.html`) | DS-06, FND-09 | 60 fps di ponsel menengah; reduced-motion benar | L |
| [ ] | MKT-02 | Bagian + halaman `/jenis-acara/{slug}` (6) | MKT-01, DOM-01 | 6 halaman unik | M |
| [ ] | MKT-03 | Katalog `/template` (filter jenis & gaya) + detail `/template/{slug}` | TPL-08, PUB-04 | filter jalan; iframe preview tampil | M |
| [ ] | MKT-04 | Fitur + FAQ | MKT-01 | FAQPage valid | S |
| [ ] | MKT-05 | Halaman harga | DEC-01 | angka dari `plans` (tanpa harga fiktif) | S |
| [ ] | MKT-06 | Blog/panduan (Filament + Markdown) | ADM-01 | 3 artikel awal terbit | M |
| [ ] | MKT-07 | Anggaran performa landing: GSAP+ScrollTrigger ≤ 120 KB gzip, init malas, QA reduced-motion | MKT-01 | Lighthouse CI lulus | M |
| [ ] | MKT-08 | Footer, kebijakan privasi, syarat, kontak | – | halaman tayang | M |

## SEO
| St | ID | Tugas | Dep | Selesai bila | Uk |
|---|---|---|---|---|---|
| [ ] | SEO-01 | `Seo` value object + `<x-seo.head>` | DS-06 | tiap halaman punya title/desc/canonical/OG unik | M |
| [ ] | SEO-02 | JSON-LD: Organization, WebSite, SoftwareApplication, FAQPage, BreadcrumbList | SEO-01 | Rich Results Test valid | M |
| [ ] | SEO-03 | `sitemap.xml` + `robots.txt` per domain | SEO-01 | hanya pemasaran dalam sitemap | S |
| [ ] | SEO-04 | Meta dan konten per jenis acara + riset kata kunci di Search Console | MKT-02 | judul/deskripsi unik per halaman | M |
| [ ] | SEO-05 | Search Console, Bing Webmaster, GA4 + banner consent | MKT-01 | properti terverifikasi | S |
| [ ] | SEO-06 | Aturan canonical/redirect (www, https, slash) | FND-05 | tes redirect | S |
| [ ] | SEO-07 | Lighthouse CI di GitHub Actions | MKT-01 | SEO ≥ 95 lulus; ambang performa tercatat | M |

## EVT — Jenis acara (M2–M5)
| St | ID | Tugas | Dep | Selesai bila | Uk |
|---|---|---|---|---|---|
| [ ] | EVT-01 | Walimatul Urs: label, salinan, ayat/doa opsional (diaktifkan pengguna), dress code, catatan tempat | DOM-01, TPL-04 | undangan walimah terbit dari editor | M |
| [ ] | EVT-02 | Tunangan/Lamaran: dua keluarga, acara lamaran, rundown | DOM-01, TPL-04 | idem | M |
| [ ] | EVT-03 | Khitanan: data anak, orang tua, doa, satu acara | DOM-01, TPL-04 | idem | M |
| [ ] | EVT-04 | Aqiqah: data bayi (lahir, arti nama, berat/panjang opsional), orang tua, doa | DOM-01, TPL-04 | idem | M |
| [ ] | EVT-05 | Ulang Tahun: usia/tonggak, tema, galeri perjalanan, daftar keinginan opsional | DOM-01, TPL-04 | idem | M |
| [ ] | EVT-06 | Tandai `events` di tiap manifest + data contoh per jenis | TPL-08…13 | katalog menyaring dengan benar | M |
| [ ] | EVT-07 | OG + SEO per jenis acara | EVT-01…05, SEO-04 | OG tiap jenis ada | S |

## ADM — Admin Filament (M6)
| St | ID | Tugas | Dep | Selesai bila | Uk |
|---|---|---|---|---|---|
| [ ] | ADM-01 | Panel `/admin` hanya admin + 2FA | FND-07 | non-admin 403 | M |
| [ ] | ADM-02 | Resource: pengguna, undangan (baca), template, paket, pembayaran | ADM-01, DOM-04 | CRUD sesuai policy | L |
| [ ] | ADM-03 | Moderasi + laporan penyalahgunaan | ADM-02 | undangan bisa disuspend | M |
| [ ] | ADM-04 | Dasbor metrik admin | ADM-02, PUB-06 | angka cocok data | M |

## QA / OPS
| St | ID | Tugas | Dep | Selesai bila | Uk |
|---|---|---|---|---|---|
| [ ] | QA-01 | Tes fitur alur inti (daftar → buat → publikasi → buka → RSVP) | DSH-05 | hijau di CI | M |
| [ ] | QA-02 | Playwright e2e + visual 360/768/1280 per template | TPL-08 | baseline tersimpan | L |
| [ ] | QA-03 | Uji beban k6 halaman undangan (lonjakan) | PUB-03 | p95 tercatat, tanpa error | M |
| [ ] | QA-04 | Uji perangkat nyata (Android murah, iOS Safari; autoplay musik) | TPL-06 | catatan hasil | M |
| [ ] | QA-05 | Peninjauan keamanan (OWASP, `composer audit`, `npm audit`) | M3 | tak ada temuan tinggi terbuka | M |
| [ ] | OPS-01 | Latihan backup & restore | FND-06 | pulih dalam waktu tercatat | M |
| [ ] | OPS-02 | Monitoring uptime + alarm Sentry/Pulse | FND-06 | alarm uji terkirim | S |
| [ ] | OPS-03 | Supervisor (queue), scheduler, health route | FND-06 | pekerjaan berjalan setelah reboot | S |
| [ ] | OPS-04 | Aturan CDN/cache (aset immutable) | MED-01 | header cache benar | S |

## LCH — Rilis
| St | ID | Tugas | Dep | Selesai bila | Uk |
|---|---|---|---|---|---|
| [ ] | LCH-01 (owner) | Beta tertutup (undangan nyata kecil) + umpan balik | M6 | daftar masalah terpilah | M |
| [ ] | LCH-02 (owner) | Daftar periksa rilis: DEC terjawab, logo disetujui, privasi/syarat, cadangan, monitoring | LCH-01 | semua tercentang | S |
| [ ] | LCH-03 | Backlog pasca-rilis: domain kustom, reseller, tiket QR, notifikasi WhatsApp, realtime, bahasa lain | LCH-02 | tiap item punya ADR sebelum dikerjakan | – |
