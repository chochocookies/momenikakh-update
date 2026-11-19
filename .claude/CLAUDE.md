# Momenikah: aturan kerja (selalu aktif)

## Produk
Momenikah adalah platform undangan digital berbahasa Indonesia. Pemilik acara memilih template, mengisi data, lalu membagikan satu tautan lewat WhatsApp. Tamu membuka undangan dari ponsel tanpa aplikasi.
- `momenikah.online`: situs pemasaran + dashboard pembuat undangan + admin.
- `share.momenikah.online`: halaman undangan untuk tamu (domain terpisah, cookie terpisah, `noindex`).
- Tagline: **Satu link untuk semua momen nikahmu.** Nama ditulis `Momenikah` (satu kata) di teks yang dilihat pengguna.
- Jenis acara: pernikahan (dibangun lebih dulu), lalu walimatul urs, khitanan, aqiqah, ulang tahun, tunangan. Semuanya memakai satu mesin (lihat `docs/EVENT_TYPES.md`).
- Proyek ini **greenfield**. Tidak ada kode atau dokumen warisan yang wajib dirujuk.

## Stack ringkas (detail: `docs/TECH_STACK.md`)
PHP 8.3+, Laravel 13, MySQL 8, Blade + Livewire 4 + Alpine, Tailwind CSS 4, Vite, Filament 5 (admin), Pest 4, GSAP 3 (landing dan efek premium), Redis (cache/antrian, bisa menyusul), penyimpanan S3-kompatibel + CDN.

## Peta folder (detail: `docs/ARCHITECTURE.md`)
```
app/Enums, Models, Policies, Services/{Templates,Invitations,Billing,Seo}, EventTypes/
app/Http/Controllers/{Marketing,Dashboard,Share,Webhook}
resources/templates/_shared/   section, ornamen, core.js dipakai semua template
resources/templates/{slug}/    SATU template = SATU folder (manifest.json, index, cover, theme.css, theme.js, assets)
resources/views/{marketing,dashboard,auth,layouts,components}
resources/css/tokens.css       satu-satunya sumber token desain
resources/js/{landing,app}.js
.claude/                       aturan, skill, perintah, dokumen, brand kit (sumber kebenaran)
```

## Aturan emas (jangan dilanggar)
1. Template hanya membaca `InvitationViewModel`. Template tidak boleh memakai Eloquent, query, atau `request()` secara langsung.
2. Output Blade selalu di-escape dengan `{{ }}`. `{!! !!}` hanya untuk SVG lokal tepercaya atau HTML hasil purifier. Nama tamu (`?to=`) tidak dirender di server (supaya halaman bisa di-cache penuh). `core.js` menulisnya ke slot `[data-guest]` dengan `textContent`, tidak pernah dengan `innerHTML`.
3. Tanpa CDN saat runtime (Tailwind, font, jQuery, ikon). Semua lewat npm dan Vite, font di-host sendiri.
4. Tanpa warna, font, atau ukuran hardcode di komponen. Pakai token dari `resources/css/tokens.css`.
5. Animasi hanya `transform` dan `opacity`, dan semuanya menghormati `prefers-reduced-motion`.
6. Skema database hanya berubah lewat migrasi baru. Migrasi yang sudah digabung tidak diedit.
7. Jangan membaca atau menulis `.env`, kunci, atau token. Jangan mencetak rahasia ke log, tes, atau dokumen.
8. Jangan menaruh klaim palsu di UI atau SEO: testimoni, rating, jumlah pengguna, atau harga yang belum diputuskan (`docs/DECISIONS.md`).
9. Keputusan berstatus `Proposed` belum final. Jangan menulisnya sebagai fakta. Tanyakan pemilik.
10. Satu tugas = satu cabang = satu PR kecil. Jangan campur refaktor dengan fitur.

## Alur kerja
1. `/next-task` melihat tugas berikutnya yang siap (semua dependensinya selesai).
2. `/start-task <ID>` membaca tugas, skill, dan aturan terkait, lalu membuat rencana (Plan Mode) sebelum menulis kode.
3. Kerjakan kecil-kecil, tulis tes lebih dulu atau bersamaan.
4. `/review-diff` memeriksa diff terhadap aturan emas. `/finish-task` menjalankan `composer check`, menandai tugas selesai, dan memperbarui `CHANGELOG.md`.
5. `/handoff` menulis ringkasan pekerjaan untuk sesi berikutnya.
Pelacak tugas: `node .claude/scripts/task.mjs next|show|list|status|blocked|decisions|start|done|block|estimate|check`.

## Definition of Done
Kode lolos `composer check` (Pint, Larastan, Pest) dan `npm run build`. Ada tes untuk perilaku baru. UI diperiksa di 360, 768, dan 1280 px dengan mode kurangi gerakan. Tidak ada rahasia di diff. Dokumen yang terdampak diperbarui. Kriteria "Selesai bila" di `.claude/TODO.md` terpenuhi.

## Bahasa
Kode, nama variabel, commit, dan komentar teknis dalam bahasa Inggris. Teks yang dilihat pengguna dalam bahasa Indonesia (kamu/kalian, kalimat pendek, nada hangat dan tenang, tanpa kata hiperbola). Dokumen di `.claude/docs` dalam bahasa Indonesia.

## Dokumen (baca saat relevan, jangan dimuat semua sekaligus)
`docs/PRODUCT.md` `TECH_STACK.md` `ARCHITECTURE.md` `TEMPLATE_SPEC.md` `DATA_MODEL.md` `EVENT_TYPES.md` `SEO.md` `BRAND.md` `LANDING.md` `DECISIONS.md` `ROADMAP.md`. Pelacak tugas: `.claude/TODO.md` (satu-satunya).
