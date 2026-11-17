# TODO Momenikah

Pelacak tugas. Perbarui lewat `node .claude/scripts/task.mjs start|done|block ID`. Jangan mengedit status secara manual kecuali perlu.

**Prioritas:** P0 wajib untuk rilis, P1 penting, P2 bagus untuk dimiliki, P3 nanti.
**Ukuran (perkiraan hari kerja efektif, satu pengembang dengan Claude Code):** S = 0,5, M = 1,5, L = 4, XL = 8. Angka ini asumsi, sesuaikan setelah Fase 0.
**Status:** todo, doing, done, blocked, backlog (tidak ikut `next` dan `estimate`).
Tugas siap dikerjakan bila semua dependensinya `done`. Gunakan `node .claude/scripts/task.mjs next`.

## F0. Fondasi dan keputusan
Repo, perkakas, CI, lingkungan, keputusan terbuka. Tidak ada fitur di fase ini.

| ID | Tugas | Pri | Ukuran | Bergantung pada | Status | Selesai bila |
|---|---|---|---|---|---|---|
| F0-01 | Putuskan keputusan terbuka di DECISIONS.md bersama pemilik (D-02, D-12, D-13, D-15, D-16) | P0 | S | - | todo | Setiap ADR berstatus Accepted atau Rejected dengan tanggal; CLAUDE.md diperbarui bila ada yang berubah |
| F0-02 | Buat repo Git, cabang main terlindungi, cabang dev, templat PR, pasang folder .claude | P0 | S | - | todo | Repo berisi .claude; main hanya berubah lewat PR; templat PR memuat checklist Definition of Done |
| F0-03 | Bootstrap Laravel 13 (PHP 8.3+), Pest 4, Pint, Larastan, skrip composer check | P0 | M | F0-02 | todo | composer check hijau di lokal; versi paket yang terpasang dicatat di TECH_STACK.md |
| F0-04 | Pasang Vite, Tailwind 4, Alpine; satu entri CSS dan satu entri JS | P0 | M | F0-03 | todo | npm run build sukses tanpa CDN; halaman uji memakai kelas Tailwind |
| F0-05 | Lengkapi .env.example dan config/momenikah.php (SHARE_DOMAIN, batas paket, saklar fitur) | P0 | S | F0-03 | todo | Aplikasi boot memakai .env.example; tidak ada rahasia di repo |
| F0-06 | CI GitHub Actions: Pint, Larastan, Pest, npm build, composer audit, npm audit | P0 | M | F0-03,F0-04 | todo | PR diblokir bila ada langkah gagal; waktu CI tercatat |
| F0-07 | Siapkan server staging, HTTPS, domain staging, dan cadangan DB harian | P0 | M | F0-05 | todo | Staging dapat diakses; satu uji pemulihan cadangan berhasil |
| F0-08 | Pasang pelacak galat (Sentry atau Flare), log harian, endpoint /up | P1 | S | F0-07 | todo | Galat uji muncul di dasbor; /up mengembalikan 200 |
| F0-09 | Atur Dependabot atau Renovate dan pemeriksaan lisensi dependensi | P1 | S | F0-06 | todo | PR pembaruan otomatis aktif; lisensi dependensi tercatat |
| F0-10 | Sesuaikan CLAUDE.md dan skill dengan hasil F0-01 serta versi paket terpasang | P1 | S | F0-01,F0-03 | todo | Tidak ada isi .claude yang bertentangan dengan keputusan atau versi nyata |
| F0-11 | Atur DNS dan Cloudflare: domain utama, share., aturan cache (abaikan parameter to), WAF dasar, Turnstile | P1 | M | F0-07 | todo | share. melayani halaman uji; aturan cache terdokumentasi; Turnstile aktif di staging |
| F0-12 | Verifikasi Google Search Console dan Bing Webmaster, buat properti GA4 | P2 | S | F0-11 | todo | Kedua domain terverifikasi; GA4 belum memasang pelacak sebelum ada persetujuan (F7-09) |

## F1. Platform inti
Auth, skema, registry jenis acara, media, kebijakan akses.

| ID | Tugas | Pri | Ukuran | Bergantung pada | Status | Selesai bila |
|---|---|---|---|---|---|---|
| F1-01 | Pasang autentikasi dari starter kit resmi Laravel (daftar, masuk, reset sandi, verifikasi email, 2FA opsional) | P0 | M | F0-04 | todo | Alur auth lolos feature test; email terkirim di staging |
| F1-02 | Rate limiting dan Turnstile untuk login, daftar, RSVP, dan ucapan | P0 | M | F1-01,F0-11 | todo | Percobaan berlebih ditolak; tes throttle hijau |
| F1-03 | Migrasi dan factory inti: users, plans, invitations, parties, events, sections, guests, rsvps, wishes, gift_accounts | P0 | M | F0-03 | todo | migrate dan seed berjalan; skema sesuai DATA_MODEL.md |
| F1-04 | Enum, EventTypeDefinition, EventTypeRegistry, implementasi Wedding | P0 | L | F1-03 | todo | Jenis acara kedua (stub di tes) hanya butuh satu class tanpa migrasi |
| F1-05 | Model, relasi, cast, dan Policy kepemilikan | P0 | M | F1-03 | todo | Tes IDOR: pengguna A tidak bisa melihat atau mengubah milik B |
| F1-06 | Media: medialibrary, koleksi, konversi WebP (thumb, md, og 1200x630), validasi, antrian | P0 | M | F1-03 | todo | Unggahan palsu ditolak; konversi berjalan lewat antrian |
| F1-07 | SlugGenerator dengan akhiran acak dan daftar reserved | P0 | S | F1-03 | todo | Tes keunikan dan kata terlarang lolos |
| F1-08 | InvitationViewModel read-only dan data contoh per jenis acara | P1 | M | F1-04,F1-06 | todo | Menjadi satu-satunya sumber data template; tes kontrak awal hijau |
| F1-09 | Status undangan dan transisinya (draft, published, expired, archived) | P1 | S | F1-05 | todo | Transisi ilegal ditolak oleh tes |
| F1-10 | Antrian database, scheduler, job kompres dan pembersih media yatim | P1 | M | F1-06 | todo | Worker berjalan di staging; media yatim terhapus terjadwal |
| F1-11 | Seeder: admin, paket contoh, undangan demo per jenis acara | P1 | S | F1-04 | todo | migrate --seed menghasilkan data demo lengkap |
| F1-12 | Audit log aksi penting (publish, hapus, bayar) | P2 | S | F1-05 | todo | Aksi tercatat dengan pengguna dan waktu |
| F1-13 | Locale id, Carbon id, zona waktu acara (WIB, WITA, WIT) | P1 | S | F1-03 | todo | Tanggal tampil dalam bahasa Indonesia; tes zona waktu lolos |

## F2. Design system dan mesin template
Token, komponen, registry template, section bersama, ornamen, pratinjau.

| ID | Tugas | Pri | Ukuran | Bergantung pada | Status | Selesai bila |
|---|---|---|---|---|---|---|
| F2-01 | Token desain tokens.css dari BRAND.md | P0 | M | F0-04 | todo | Tidak ada warna atau font hardcode di komponen; pemilik menyetujui tampilan token |
| F2-02 | Self-host font woff2 (subset latin), font-display swap, preload | P0 | S | F2-01 | todo | Tidak ada permintaan ke fonts.googleapis.com; CLS dari font mendekati nol |
| F2-03 | Pustaka komponen Blade UI: button, field, select, textarea, card, badge, modal, toast, tabs, alert, empty-state, pagination | P0 | L | F2-01 | todo | /styleguide menampilkan semuanya; ukuran sentuh 44 px; kontras lolos |
| F2-04 | Layout marketing, app, auth, invitation | P0 | M | F2-03 | todo | Setiap layout memakai satu set token; layout invitation tanpa chrome |
| F2-05 | Mesin template: TemplateRegistry, TemplateManifest (validasi skema), TemplateRenderer | P0 | L | F1-08,F0-04 | todo | Template contoh termuat dari manifest; manifest rusak memberi galat jelas |
| F2-06 | Vite multi-entri otomatis dari resources/templates/*/theme.css dan theme.js | P0 | M | F2-05 | todo | Build menghasilkan berkas per template; halaman hanya memuat berkas template terpilih |
| F2-07 | Section bersama: cover, opening, person, couple, story, events, countdown, gallery, gift, rsvp, wishes, map, music, closing | P0 | L | F2-05,F2-03 | todo | Setiap section punya data contoh, tes render, dan aksesibilitas dasar |
| F2-08 | Sistem ornamen: x-ornament, pustaka SVG currentColor, utilitas slot dan z-index | P0 | M | F2-05 | todo | Satu SVG dapat diwarnai tiga tema berbeda lewat token |
| F2-09 | core.js: buka sampul, musik, reveal gulir, salin, hitung mundur, nama tamu dari ?to=, reduced-motion | P1 | L | F2-07 | todo | Maks 8 KB gzip; tanpa jQuery; ?to= tidak bisa menyuntik HTML |
| F2-10 | Pratinjau template: pembungkus, frame, mode ponsel dan desktop, noindex, frame-ancestors self | P1 | M | F2-05 | todo | /preview/{slug} tampil di dua mode; header sesuai |
| F2-11 | TemplateSampleData per jenis acara | P1 | S | F1-08 | todo | Semua section terisi di pratinjau |
| F2-12 | Perintah template:make dan template:lint | P1 | M | F2-05 | todo | Lint memeriksa manifest, bobot, token wajib, kontras palet, dan alt ornamen |
| F2-13 | Halaman styleguide internal dan galeri ornamen | P2 | S | F2-03,F2-08 | todo | Hanya admin yang dapat membuka |
| F2-14 | Anggaran performa Lighthouse CI untuk pratinjau template | P1 | M | F2-10 | todo | CI gagal bila LCP, CLS, atau bobot melewati anggaran manifest |

## F3. Undangan publik
Pengalaman tamu di host share.

| ID | Tugas | Pri | Ukuran | Bergantung pada | Status | Selesai bila |
|---|---|---|---|---|---|---|
| F3-01 | Rute host share, PublicInvitationController, 404 untuk draft, header noindex | P0 | M | F2-06,F1-09 | todo | Undangan published terbuka di share.; draft 404; header X-Robots-Tag ada |
| F3-02 | Nama tamu dari ?to= di sisi klien dengan textContent dan batas 60 karakter | P0 | S | F2-09,F3-01 | todo | Tes XSS: payload skrip tampil sebagai teks; tanpa ?to= tampil Tamu Undangan |
| F3-03 | Sampul amplop, tombol buka, musik mulai setelah interaksi | P0 | M | F2-09 | todo | Musik tidak berbunyi sebelum klik; sampul dapat dioperasikan keyboard |
| F3-04 | Hitung mundur sadar zona waktu | P0 | S | F2-09 | todo | Tes lintas zona waktu lolos; tidak negatif setelah acara |
| F3-05 | Section acara: banyak kegiatan, tombol Tambah ke kalender (.ics), peta (tautan dan embed opsional) | P0 | M | F2-07 | todo | File .ics valid di Google dan Apple Kalender; tautan peta terbuka |
| F3-06 | RSVP: form, validasi, throttle, Turnstile, simpan, notifikasi pemilik | P0 | L | F2-07,F1-02 | todo | RSVP tersimpan; spam ditolak; pemilik menerima notifikasi |
| F3-07 | Ucapan: kirim, moderasi, muat bertahap, anti-spam | P0 | L | F2-07,F1-02 | todo | Ucapan tampil sesuai status moderasi; HTML di-escape |
| F3-08 | Amplop digital: rekening, e-wallet, alamat, QR, salin dengan toast | P0 | M | F2-07 | todo | Salin bekerja di iOS dan Android; QR dimuat lazy |
| F3-09 | Galeri foto dengan lightbox lazy | P0 | M | F2-07 | todo | Lightbox dapat dioperasikan keyboard; gambar lazy; tanpa CLS |
| F3-10 | Musik latar: unggah atau pilih dari pustaka, kontrol selalu terlihat 44 px | P1 | M | F3-03,F1-06 | todo | Kontrol musik terlihat di semua section dan berlabel |
| F3-11 | Hitungan kunjungan sekali per tamu (cookie), abaikan bot, ping sudah-buka dengan token g | P1 | M | F3-01 | todo | Muat ulang tidak menambah hitungan; bot tidak terhitung |
| F3-12 | SEO undangan: OG dari foto sampul, canonical, noindex ganda, uji pratinjau WhatsApp | P1 | M | F3-01,F1-06 | todo | Tautan tampil benar di WhatsApp dan Facebook Debugger; tanpa nama tamu di OG |
| F3-13 | Cache penuh halaman undangan di edge dan purge saat publish atau edit | P1 | M | F3-01,F0-11 | todo | Cache hit melayani halaman; perubahan tampil setelah purge dalam hitungan detik |
| F3-14 | Aksesibilitas undangan: fokus, kontras, label, lang, reduced-motion | P1 | M | F3-03 | todo | axe tanpa pelanggaran kritis; mode kurangi gerakan diuji manual |
| F3-15 | Sandi opsional untuk undangan privat | P2 | M | F3-01 | todo | Tanpa sandi tidak bisa dibuka; sandi di-hash |
| F3-16 | Halaman 404 dan kedaluwarsa yang ramah | P2 | S | F3-01 | todo | Pesan sopan dan tautan ke beranda |

## F4. Dashboard pembuat undangan
Wizard, pratinjau langsung, tamu, statistik.

| ID | Tugas | Pri | Ukuran | Bergantung pada | Status | Selesai bila |
|---|---|---|---|---|---|---|
| F4-01 | Beranda dashboard: daftar undangan, status, aksi cepat | P0 | M | F2-04,F1-05 | todo | Hanya undangan milik sendiri; keadaan kosong mengajak membuat undangan |
| F4-02 | Wizard buat undangan: jenis acara, template, data, publish | P0 | L | F4-01,F2-10 | todo | Alur lengkap tanpa bantuan; tiap langkah tersimpan |
| F4-03 | Form dinamis dari EventTypeDefinition (Livewire) dengan autosave | P0 | L | F4-02,F1-04 | todo | Field berubah ketika definisi berubah tanpa mengedit controller |
| F4-04 | Pratinjau langsung (iframe) sinkron dengan form | P0 | L | F4-03,F2-10 | todo | Perubahan terlihat dalam kurang dari satu detik pada koneksi normal |
| F4-05 | Unggah media: seret dan lepas, progres, urutan, batas ukuran | P0 | M | F1-06,F4-02 | todo | Foto dapat diurutkan; berkas terlalu besar ditolak dengan pesan jelas |
| F4-06 | Pengaturan undangan: slug, judul, musik, palet, animasi, bahasa | P0 | M | F4-03 | todo | Slug unik dicek langsung; palet berubah di pratinjau |
| F4-07 | Daftar tamu: tambah, impor CSV, grup, tautan per tamu, tombol WhatsApp, status sudah buka | P0 | L | F4-02,F3-11 | todo | Impor 200 baris berhasil; tautan ?to= terbentuk benar dan ter-URL-encode |
| F4-08 | Statistik: kunjungan, ringkasan RSVP, ucapan, ekspor CSV | P1 | M | F3-11,F3-06 | todo | Angka cocok dengan data uji; CSV terbuka di Excel |
| F4-09 | Moderasi ucapan dan RSVP | P1 | M | F3-06,F3-07 | todo | Sembunyikan dan hapus bekerja serta tercatat di audit log |
| F4-10 | Publish dan unpublish dengan checklist kelengkapan, duplikasi undangan | P1 | M | F4-03,F1-09 | todo | Publish gagal bila data wajib kosong; duplikasi ikut menyalin media |
| F4-11 | Ganti template tanpa kehilangan data | P1 | S | F4-03 | todo | Tes: data tetap utuh setelah ganti ke template lain |
| F4-12 | Riwayat versi draf sederhana | P2 | M | F4-03 | todo | Pengguna dapat memulihkan versi sebelumnya |
| F4-13 | Onboarding: daftar tugas awal dan tips | P2 | S | F4-01 | todo | Tampil untuk pengguna baru dan dapat ditutup |
| F4-14 | Notifikasi email: RSVP baru, publish berhasil, masa aktif hampir habis | P1 | M | F3-06,F1-10 | todo | Email terkirim dengan templat bermerek di staging |

## F5. Katalog template batch 1
Enam template dan aset ornamen.

| ID | Tugas | Pri | Ukuran | Bergantung pada | Status | Selesai bila |
|---|---|---|---|---|---|---|
| F5-01 | Brief dan kontrak ilustrator: 6 tema x set ornamen, hak cipta penuh, berkas sumber | P0 | L | F0-01 | todo | Kontrak tertulis; aset diterima dalam SVG dan berkas berlapis beserta lisensi |
| F5-02 | Template classic-elegant (arsitektur acuan) lengkap semua section dan palet | P0 | L | F2-07,F2-08,F2-12,F5-01 | todo | Lolos template:lint, axe tanpa pelanggaran kritis, anggaran bobot manifest, semua palet kontras 4,5:1, dan disetujui pemilik secara visual |
| F5-03 | Template floral-garden | P0 | L | F5-02 | todo | Lolos template:lint, axe tanpa pelanggaran kritis, anggaran bobot manifest, semua palet kontras 4,5:1, dan disetujui pemilik secara visual |
| F5-04 | Template luxury-gold | P0 | L | F5-02 | todo | Lolos template:lint, axe tanpa pelanggaran kritis, anggaran bobot manifest, semua palet kontras 4,5:1, dan disetujui pemilik secara visual |
| F5-05 | Template modern-minimal | P1 | L | F5-02 | todo | Lolos template:lint, axe tanpa pelanggaran kritis, anggaran bobot manifest, semua palet kontras 4,5:1, dan disetujui pemilik secara visual |
| F5-06 | Template beach-sunset | P1 | L | F5-02 | todo | Lolos template:lint, axe tanpa pelanggaran kritis, anggaran bobot manifest, semua palet kontras 4,5:1, dan disetujui pemilik secara visual |
| F5-07 | Template rustic-vintage | P1 | L | F5-02 | todo | Lolos template:lint, axe tanpa pelanggaran kritis, anggaran bobot manifest, semua palet kontras 4,5:1, dan disetujui pemilik secara visual |
| F5-08 | Pustaka musik 10 lagu dengan lisensi jelas | P1 | M | F0-01 | todo | Bukti lisensi tertulis disimpan; lagu tersedia di unggahan |
| F5-09 | Uji panduan: buat template ke-7 hanya dari TEMPLATE_SPEC.md | P1 | M | F5-07 | todo | Selesai kurang dari satu hari kerja; panduan diperbaiki dari temuan |

## F6. Paket, pembayaran, admin
Monetisasi dan operasional.

| ID | Tugas | Pri | Ukuran | Bergantung pada | Status | Selesai bila |
|---|---|---|---|---|---|---|
| F6-01 | Putuskan paket dan harga lalu isi tabel plans (D-02) | P0 | S | F0-01 | todo | Keputusan tertulis; tabel plans terisi lewat seeder |
| F6-02 | Order dan pembayaran Duitku: buat invoice, verifikasi tanda tangan callback, idempoten | P0 | L | F6-01,F1-05 | todo | Tes callback ganda tidak membuat pembayaran ganda; tanda tangan salah ditolak |
| F6-03 | Aktivasi paket, masa aktif, kedaluwarsa terjadwal, hasActivePlan | P0 | M | F6-02 | todo | Undangan berbayar aktif setelah bayar dan kedaluwarsa tepat waktu |
| F6-04 | PlanGate: satu tempat untuk membatasi fitur per paket | P0 | M | F6-01 | todo | Tidak ada pemeriksaan paket tersebar di controller; tes batas foto dan template premium |
| F6-05 | Voucher dan kode promo | P1 | M | F6-02 | todo | Voucher kedaluwarsa dan melebihi batas ditolak |
| F6-06 | Admin Filament 5: pengguna, undangan, template, paket, order, moderasi | P1 | L | F1-05,F6-01 | todo | Admin dapat mengelola semuanya; 2FA aktif untuk admin |
| F6-07 | Kuitansi dan email pembayaran | P1 | M | F6-02 | todo | Kuitansi PDF atau HTML terkirim setelah bayar |
| F6-08 | Alur refund dan sengketa manual | P2 | M | F6-02 | todo | Admin dapat menandai refund dan hasilnya tercatat |
| F6-09 | Reseller dan hapus brand (setelah pemilik memutuskan) | P3 | L | F6-03 | backlog | Ditentukan setelah keputusan produk |

## F7. Situs pemasaran, landing GSAP, SEO
Wajah publik Momenikah.

| ID | Tugas | Pri | Ukuran | Bergantung pada | Status | Selesai bila |
|---|---|---|---|---|---|---|
| F7-01 | Layout pemasaran, navigasi, footer, halaman legal (draf, tinjau ahli hukum) | P0 | M | F2-04 | todo | Semua halaman memakai komponen dan token; draf legal siap ditinjau |
| F7-02 | Landing page GSAP sesuai LANDING.md | P0 | XL | F7-01,F5-02,F2-01 | todo | Narasi gulir 0-6 berjalan; Lighthouse ponsel baik; mode kurangi gerakan benar; teks hero terbaca tanpa JS |
| F7-03 | Katalog /template dan detail /template/{slug} dengan filter jenis acara, gaya, warna | P0 | L | F2-05,F5-02 | todo | Filter bekerja; tiap template punya tombol coba dan buat |
| F7-04 | Halaman kategori per jenis acara (pernikahan dulu, lainnya menyusul) | P0 | M | F7-01 | todo | Konten unik per halaman; tautan ke template relevan |
| F7-05 | SEO teknis: komponen x-seo, canonical, OG, Twitter, JSON-LD, sitemap.xml, robots.txt | P0 | M | F7-01 | todo | Rich Results Test dan validator OG lolos; sitemap hanya berisi halaman pemasaran |
| F7-06 | Pasang favicon, ikon, manifest, dan gambar OG dari .claude/brand ke public | P1 | S | F7-01 | todo | Favicon muncul di Chrome, Safari, dan layar utama ponsel; manifest valid |
| F7-07 | Halaman Fitur, Harga, FAQ, Kontak | P1 | M | F7-01,F6-01 | todo | Harga sesuai tabel plans; tanpa klaim yang belum benar |
| F7-08 | Blog dan panduan (10 artikel awal) dengan CMS sederhana di Filament | P1 | L | F6-06,F7-05 | todo | Artikel terbit, masuk sitemap, dan punya data terstruktur dasar |
| F7-09 | GA4 dengan banner persetujuan dan peristiwa konversi | P1 | M | F7-01,F0-12 | todo | Pelacak aktif hanya setelah persetujuan; peristiwa daftar dan publish tercatat |
| F7-10 | Gambar OG khusus untuk tiap template di katalog | P2 | M | F7-03,F7-05 | todo | Tautan template menampilkan gambar yang tepat di WhatsApp |
| F7-11 | Optimasi performa pemasaran: preload font, CSS kritis, chunk GSAP lazy | P2 | M | F7-02 | todo | Anggaran JS dan CSS di LANDING.md terpenuhi |
| F7-12 | JSON-LD Organization, WebSite, BreadcrumbList tanpa klaim ulasan | P2 | S | F7-05 | todo | Validator tanpa galat; tidak ada AggregateRating atau Review |

## F8. Kualitas dan peluncuran
Tes, keamanan, beban, migrasi, rilis.

| ID | Tugas | Pri | Ukuran | Bergantung pada | Status | Selesai bila |
|---|---|---|---|---|---|---|
| F8-01 | Feature test alur inti end-to-end (daftar, buat, publish, buka, RSVP, ucapan, bayar) | P0 | L | F4-10,F6-03 | todo | Seluruh alur hijau di CI |
| F8-02 | Tes kontrak field: definisi, form, model, view-model, template | P0 | M | F4-03,F2-07 | todo | Tes gagal bila ada field yang hilang di salah satu lapisan |
| F8-03 | Audit keamanan OWASP: XSS, IDOR, unggah, CSRF, CSP, HSTS | P0 | M | F8-01 | todo | Laporan temuan dan semua temuan kritis serta tinggi sudah diperbaiki |
| F8-04 | Browser test undangan di 360 dan 1280 px | P0 | M | F3-14 | todo | Skenario buka sampul, galeri, RSVP, dan salin rekening hijau |
| F8-05 | Uji beban halaman undangan dengan k6 (simulasi lonjakan WhatsApp) | P1 | M | F3-13 | todo | Target latensi yang disepakati di awal tercapai; hasil dicatat |
| F8-06 | Latihan backup dan restore, runbook insiden | P1 | M | F0-07 | todo | Pemulihan dilatih dari awal sampai akhir; runbook tersimpan |
| F8-07 | Kebijakan privasi, retensi data, penghapusan data tamu (tinjau ahli hukum) | P1 | M | F7-01 | todo | Kebijakan terbit; tombol hapus data berfungsi; masa simpan sesuai D-16 |
| F8-08 | Migrasi data dan redirect 301 dari momenikah.online lama (bila D-15 menyatakan ada pengguna) | P1 | M | F3-01 | todo | Uji pada salinan data; semua tautan /u/ lama terbuka lewat redirect |
| F8-09 | Checklist rilis: smoke test, rollback, pemantauan, cutover DNS | P0 | M | F8-03,F8-04 | todo | Rilis percobaan di staging berhasil dan rollback dilatih |
| F8-10 | Beta tertutup 10 sampai 20 pengguna dan formulir umpan balik | P1 | M | F8-09 | todo | Umpan balik terkumpul dan diprioritaskan |
| F8-11 | Perbaikan dan optimasi pascabeta | P2 | L | F8-10 | todo | Temuan beta prioritas tinggi selesai |

## F9. Jenis acara baru (backlog, fitur saja)
Spesifikasi di EVENT_TYPES.md. Dipromosikan menjadi todo setelah pernikahan stabil dan D-13 diputuskan.

| ID | Tugas | Pri | Ukuran | Bergantung pada | Status | Selesai bila |
|---|---|---|---|---|---|---|
| F9-01 | Walimatul Urs: definisi, teks bawaan, section, 2 template | P2 | L | F5-09 | backlog | Fitur sesuai EVENT_TYPES.md; teks Arab ditinjau; tes kontrak lolos |
| F9-02 | Tunangan: definisi, teks bawaan, section, 2 template | P2 | L | F5-09 | backlog | Fitur sesuai EVENT_TYPES.md; tes kontrak lolos |
| F9-03 | Aqiqah: definisi, teks bawaan, section, 2 template | P2 | L | F5-09 | backlog | Fitur sesuai EVENT_TYPES.md; tes kontrak lolos |
| F9-04 | Khitanan: definisi, teks bawaan, section, 2 template | P2 | L | F5-09 | backlog | Fitur sesuai EVENT_TYPES.md; tes kontrak lolos |
| F9-05 | Ulang Tahun: definisi, teks bawaan, section, 2 template | P2 | L | F5-09 | backlog | Fitur sesuai EVENT_TYPES.md; tes kontrak lolos |
| F9-06 | Halaman kategori SEO dan konten untuk jenis acara baru | P2 | M | F9-01 | backlog | Satu halaman unik per jenis acara masuk sitemap |

## F10. Peningkatan lanjutan (backlog)
Fitur lintas jenis acara setelah peluncuran.

| ID | Tugas | Pri | Ukuran | Bergantung pada | Status | Selesai bila |
|---|---|---|---|---|---|---|
| F10-01 | QR tiket tamu dan check-in | P3 | L | F8-11 | backlog | Pemindaian mencatat kehadiran; data tamu aman |
| F10-02 | Livestream (embed YouTube) pada section acara | P3 | S | F8-11 | backlog | Embed tampil hanya saat diaktifkan pemilik |
| F10-03 | Kustom domain per undangan | P3 | XL | F8-11 | backlog | Domain pengguna melayani undangan dengan HTTPS otomatis |
| F10-04 | Pengiriman WhatsApp resmi untuk banyak tamu | P3 | L | F8-11 | backlog | Pengiriman terkirim sesuai kebijakan penyedia dan persetujuan tamu |
| F10-05 | Kustom CSS dan JS premium di host share dengan sandbox | P3 | L | F8-11 | backlog | Kode pengguna tidak dapat mengakses dashboard atau data lain |
| F10-06 | Bantuan penulisan teks undangan | P3 | M | F8-11 | backlog | Pengguna dapat menerima atau menolak saran; data tidak disimpan di luar kebutuhan |
