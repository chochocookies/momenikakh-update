# 10 — Changelog

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
