# 10 — Changelog

## 2026-11-20 (tampilan, DSH, dan pendukungnya)
- Tampilan dasbor ditata ulang dengan skill baru (impeccable, mode Operate): shell dengan navigasi aktif dan menu Akun, daftar undangan berbaris (bukan kartu sama besar), halaman undangan sebagai urutan empat langkah (isi data, pratinjau, tamu, terbitkan) dengan statistik, editor dengan navigasi langkah di samping dan pratinjau langsung, halaman masuk/daftar/lupa sandi/verifikasi berbahasa Indonesia memakai komponen dan token. Seleksi teks, `accent-color`, scrollbar, dan angka tabular ikut diberi warna merek. Tabel yang bisa digulir punya `tabindex`, label, dan tidak lagi memperlebar halaman di 360 px.
- Ikon `calendar`, `copy`, `gift`, `image` kehilangan `width`/`height` pada `<rect>` (terhapus bersama atribut `svg`), jadi tergambar separuh. Dipulihkan, dan `IconsTest` kini menolak `<rect>` tanpa ukuran.
- PUB-02: halaman 404 dan 410 yang ramah (undangan kedaluwarsa = 410; draf, arsip, dan slug tak dikenal = 404 yang sama). PUB-08: `X-Robots-Tag: noindex, nofollow` di seluruh permukaan `undangan.` selain meta robots, canonical tanpa query. PUB-09: RSVP (`POST /{slug}/rsvp`) dengan throttle, honeypot, dan Turnstile (aktif bila `TURNSTILE_SECRET_KEY` diisi; tanpa kunci dilewati). PUB-10: ucapan (`POST` dan `GET /{slug}/wishes`, muat bertahap dengan cursor); teks tamu ditulis dengan `textContent` dan diuji di browser terhadap payload XSS. PUB-06: beacon `POST /{slug}/seen` menghitung kunjungan, pengunjung unik per hari (hash bergaram di cache, tanpa cookie), dan pembukaan sampul; bot, pengambil pratinjau tautan, dan pratinjau template tidak dihitung. PUB-11: halaman Jawaban tamu dengan ringkasan RSVP dan moderasi ucapan (sembunyikan, tampilkan, hapus) yang tercatat di audit log.
- URL aksi di halaman undangan (`rsvp`, `wishes`, `opened`) kini berupa path relatif, bukan `APP_URL`, supaya tidak salah host.
- DSH-05: cek kelengkapan (`InvitationReadiness`) dan terbit/jadikan draf dengan tombol bagikan WhatsApp. DSH-06: daftar tamu di depan, impor 200 baris teruji. DSH-07: statistik 30 hari di halaman undangan. DSH-08: ganti template tanpa kehilangan data (diuji dengan dua template). DSH-09: duplikat undangan (draf, tautan baru, berkas disalin). DSH-10: halaman Akun dengan ekspor JSON dan hapus akun (berkas ikut terhapus). DSH-14: daftar "Mulai dari sini" yang bisa ditutup (kolom `users.onboarding_dismissed_at`). DSH-04: pratinjau draf di editor (iframe, dimuat ulang setiap simpan).
- Belum dicentang: DSH-03, DSH-04, DSH-05, DSH-09 (menunggu rantai MED-02, MED-01, DEC-04). Pembayaran, perubahan slug, musik, dan unggah media di editor (DSH-12, DSH-13) belum dikerjakan.

## 2026-11-20
- FND-11: job `audit` di CI (`composer audit --locked`, `npm audit --audit-level=high`), `.github/dependabot.yml`, dan tes lisensi dependensi (`DependencyLicensesTest`, daftar lisensi yang diizinkan). Dependabot aktif di GitHub belum dikonfirmasi.
- FND-12: dokumen dan aturan ganda yang usang dihapus (`docs/00…09-*.md`, lima berkas `rules/`); satu set nama berkas tersisa dan sudah disamakan dengan kode.
- FND-10: `config/momenikah.php` (nama, tagline, sakelar `features.payments`). Kunci baru di `.env.example` (mis. `FEATURE_PAYMENTS`) belum ditulis karena berkas itu tertutup untuk agen; tambahkan sendiri.
- DS-08: komponen `x-ui.alert` dan `x-ui.pagination` (+ styleguide, tes). Pagination dipakai di beranda dashboard.
- DOM-12: audit log (`audit_logs`, `AuditLogger`, `AuditAction`). Publikasi, unpublish, arsip, pulihkan, kedaluwarsa, hapus tamu, impor tamu tercatat. Konteks memakai daftar kunci yang aman (tanpa data pribadi).
- DOM-13: zona waktu per acara (WIB, WITA, WIT; disimpan UTC) dan locale Indonesia (`lang/id`, `Carbon::setLocale`). `config/app.php` mengunci `locale` ke `id` (bukan dari `.env`), sebab `APP_LOCALE=en` sisa instalasi membuat pesan validasi berbahasa Inggris.
- DSH-03 (belum dicentang, menunggu MED-02): editor Livewire `InvitationEditor` untuk langkah dasar, orang, acara+lokasi, cerita, amplop, teks, dan bagian/tampilan. Autosave saat kolom kehilangan fokus (`wire:model.live.blur`), validasi inline dari `EventTypeDefinition::rules()`, langkah disimpan hanya bila seluruhnya valid. Livewire memakai build `csp_safe` supaya CSP `script-src 'self'` tetap tanpa `unsafe-eval`, dan rute update-nya hanya ada di host aplikasi. Tes "tidak ada field hilang" untuk keenam jenis acara. Langkah galeri dan musik menunggu unggahan media di editor (DSH-12, MED-02).
- Perbaikan: halaman masuk, daftar, lupa sandi, dan verifikasi email memanggil layout `layouts.auth` yang belum ada, sehingga menghasilkan error 500. Layout minimal ditambahkan. Isi halaman itu masih bawaan starter kit (bahasa Inggris, warna tidak lewat token) dan perlu dirapikan di tugas tersendiri.

## 2026-11-19 (lanjutan)
- DEC-01 diputuskan: Gratis = undangan sample (pratinjau saja, tidak bisa diterbitkan). Paket Template Rp 50.000, sekali bayar per undangan, tanpa masa kedaluwarsa. Plan `free` dan `paket-template` di `config/entitlements.php` + `PlanSeeder`; `InvitationLifecycle::publish` menolak undangan sample (`PublishRequiresPlan`). DOM-09 selesai.
- PUB-01: permukaan `undangan.` memakai grup middleware `public` (tanpa sesi, cookie, CSRF); rute `/{slug}` hanya menyajikan undangan terbit.
- Landing `/` dipindahkan dari "Momenikah – Landing page dan branding.html" ke Blade + `landing.css` + `landing.js` (token saja, tanpa CDN, tanpa style inline), ditambah bagian harga dari tabel `plans`. MKT-01 masih berjalan: narasi GSAP di `docs/LANDING.md` belum dibuat.
- DSH-01 dan DSH-02: beranda dashboard (hanya undangan milik sendiri) dan wizard buat undangan (jenis acara, template, judul, mulai dengan data contoh atau kosong), halaman undangan dengan harga penerbitan.
## 2026-11-19
- Mesin template (M2): GST-01, GST-02, TPL-01…07, TPL-16, TPL-17, PUB-04, PUB-12 selesai. `TemplateRegistry` (`manifest.json`), `InvitationViewModel` + builder, namespace `templates::` dan `@invitationSection`, 13 partial `_shared`, `invitation-core` (Vite) + `core.js`, `template:make`, `template:lint`, `<x-ornament>`, `TemplateSampleData`, `/preview/{template}`.
- Classic Elegant ada dan lolos `template:lint`, axe, e2e 360/768/1280, dan mode kurangi gerakan. Menunggu persetujuan visual pemilik (TPL-08) dan Lighthouse CI pratinjau (TPL-14).
- Perbaikan: blok `@theme` di `app.css` memuat referensi melingkar sehingga font, ukuran huruf, radius, dan warna template tidak berlaku; dihapus. Token hanya didefinisikan di `tokens.css`.
- `frame-ancestors 'none'` untuk permukaan undangan; hanya frame pratinjau yang boleh dibenamkan oleh halaman pembungkusnya (`PreviewHeaders`).

## 2026-11-14
- Paket `.claude` greenfield dibuat: aturan, skill, perintah, dokumen, TODO, roadmap, aset merek, prototipe landing GSAP.
## Serah terima
(Isi lewat `/handoff`.)
