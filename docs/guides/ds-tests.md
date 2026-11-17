# Tes kesesuaian DS-03 sampai DS-07

| Tugas | Selesai bila | Bukti otomatis |
|---|---|---|
| DS-01/02 (token, font) | token tampil, 0 permintaan ke fonts.googleapis.com | `TokensTest`: warna persis, kontras AA, namespace Tailwind, `@font-face` + berkas ada, tanpa CDN |
| DS-03 komponen | semua tampil di `/styleguide` pada 360/768/1280 | `ComponentsTest`, `StyleguideTest` (kelengkapan, id unik, label, ARIA modal/tab), `components.spec.ts` (modal, tabs, toast, 44 px, reflow) |
| DS-04 ikon | tanpa emoji sebagai ikon | `IconsTest`: tanpa emoji, ikon ada, SVG bersih, aman dari path traversal |
| DS-05 brand + `<x-seo.head>` | favicon tampil, manifest valid | `SeoHeadTest`: tag lengkap, noindex, escape, ukuran gambar, manifest dan ikon, warna tema |
| DS-06 layout | tiap permukaan memakai layout sendiri | `LayoutsTest`: kerangka dokumen, skip link, `data-surface`, noindex, tanpa chrome di publik, rute nyata |
| DS-07 aksesibilitas | axe 0 pelanggaran serius | `AccessibilityBaselineTest`, `HygieneTest`, `a11y.spec.ts` (axe terang dan gelap), fokus terlihat, reduced motion |

## Menjalankan
```bash
php artisan test --filter=DesignSystem
npm run build && E2E_BASE_URL=http://momenikah.test npx playwright test
```

## Asumsi yang perlu Anda cocokkan (satu tempat tiap kali)
- `tests/Support/DesignSystem.php`: lokasi `resources/css/tokens.css` dan `public/fonts`.
- `tests/Support/Surfaces.php`: domain tiap permukaan (dibaca dari `config/surfaces.php` bila ada).
- `StyleguideTest.php`: konstanta `STYLEGUIDE_SURFACE` dan `STYLEGUIDE_PATH`.
- Nama komponen: `x-ui.button|field|input|select|textarea|modal|tabs|card|badge|empty-state|icon`, `x-seo.head`, `x-layouts.marketing|app|public`. Bila Anda mengganti nama, ubah di tes.
- Pengecualian yang sah dicatat di `HygieneTest.php` (`HYGIENE_ALLOW`), bukan dengan melonggarkan aturan.

## Kegagalan yang diharapkan pada pemakaian pertama
1. `TokensTest` tiga tes terakhir bagian namespace dan gradien gagal bila `tokens.css` masih memakai `--font-family-*`, `--font-size-*`, `--line-height-*`, `--letter-spacing-*`, atau gradien di `--color-*`. Itu temuan nyata (lihat `tokens-fixes.md`), bukan galat tes.
2. `AccessibilityBaselineTest` gagal bila belum ada aturan `:focus-visible` dan blok `prefers-reduced-motion` di CSS.
3. `HygieneTest` menunjuk berkas yang masih memakai `style="..."`, skrip inline, atau warna di luar token. Perbaiki berkasnya.
4. `SeoHeadTest` gagal pada berkas brand yang belum disalin ke `public/` (langkah DS-05).

## Yang tidak bisa dibuktikan otomatis
Keterbacaan visual, nada bahasa, dan kenyamanan keyboard secara menyeluruh. Lakukan satu putaran manual: seluruh `/styleguide` hanya dengan keyboard, perbesaran 200 persen, dan satu pembaca layar (VoiceOver atau NVDA) pada modal dan form galat. Axe menangkap sekitar sebagian masalah, bukan seluruhnya.
