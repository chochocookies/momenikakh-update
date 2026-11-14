# 04 — Sistem template

Tujuan: menambah template dengan sedikit file, tanpa menduplikasi kode, dan tanpa merusak undangan yang sudah ada.

## Anatomi
Folder `resources/templates/<slug>/` berisi `manifest.php`, `index.blade.php`, `theme.css`, `theme.js`, `sections/` (opsional), `assets/`, `preview.webp`, `thumb.webp`.

```php
// manifest.php
return [
  'slug' => 'classic-elegant', 'name' => 'Classic Elegant', 'version' => '1.0.0',
  'events' => ['wedding','walimah','engagement'],          // jenis acara yang didukung
  'tier' => 'free',                                         // free | premium
  'sections' => ['cover','opening','people','events','story','gallery','gift','rsvp','guestbook','closing'],
  'supports' => ['music','countdown','map','gift','rsvp','guestbook','story','gallery'],
  'palette' => ['bg' => '#FBF5EC', 'ink' => '#4A1F2B', 'accent' => '#B8893B'],
  'motion' => 'gentle',                                     // none | gentle | rich
  'asset_budget_kb' => 400,
];
```

## Kontrak data
`InvitationViewModel` adalah satu-satunya input template: `event` (jenis, label, salinan), `people[]` (peran), `schedule[]`, `story[]`, `gallery[]`, `gifts[]`, `music`, `settings` (bagian aktif, urutan), `guest` (placeholder), `urls`. Tidak ada Eloquent, `request()`, atau `auth()` di template.

## Resolusi tampilan
Namespace `templates::`. Setiap bagian dirender dengan `@includeFirst(["templates::{$slug}.sections.{$name}", "templates::_shared.sections.{$name}"])`. Template hanya menulis bagian yang berbeda; `theme.css` mengatur token `--inv-*` (warna, tipografi, jarak, ornamen).

## CSS dan JS
- `invitation-core.css` memakai token; `theme.css` mendefinisikan token di `[data-theme="<slug>"]`.
- `invitation/core.js`: buka cover, musik (butuh ketukan), hitung mundur, salin rekening + toast, lightbox, isi `?to=`.
- `theme.js`: `export function init({ gsap, ScrollTrigger, root }) { … }` — hanya animasi khas template. Dimuat dinamis lewat `import.meta.glob`, sehingga halaman memuat core + 1 tema.

## Lapisan visual (urutan, dari bawah)
0 warna + tekstur halus · 1 pola samar (5–10%) · 2 bingkai tipis · 3 ornamen sudut (SVG 1 warna, dibalik jadi 4 pojok) · 4 konten + foto berbentuk lengkung · 5 pembatas bagian · 6 elemen melayang (sedikit) · 7 interaksi (amplop, salin, toast). Aturan: 2–3 ornamen per bagian.

## Enam template awal
| Slug | Karakter | Palet | Gerak khas |
|---|---|---|---|
| classic-elegant | formal, serif, ornamen | ivory + burgundy | bingkai ganda memudar masuk |
| floral-garden | lembut, botani | blush + sage | kelopak jatuh |
| modern-minimal | bersih, tipografi tegas | putih + hitam + satu aksen | garis terbentang |
| luxury-gold | gelap, premium | arang + emas | kilau emas menyapu |
| beach-sunset | lapang, pantai | pasir + koral + teal | ombak bergeser |
| rustic-vintage | hangat, tekstur kertas | kraft + cokelat | cap "simpan tanggalnya" |

## Daftar periksa template baru
Manifest valid · renders untuk setiap `events` dengan data contoh · 360/768/1280 px · reduced-motion · kontras ≥ 4,5 · `templates:audit` hijau · `preview.webp` 1200×800 + `thumb.webp` · tidak ada font baru · lisensi aset tercatat di `assets/LICENSE.md`.

## Pembaruan versi
Template berversi. Undangan terbit mencatat `template_version`; perubahan yang merusak = slug baru (mis. `classic-elegant-2`), bukan menimpa.
