---
name: template-authoring
description: Membuat atau mengubah template undangan Momenikah: struktur folder, manifest, palet, ornamen berlapis, animasi, bobot, dan lint. Gunakan untuk tugas F5 dan perubahan di resources/templates.
---
# Skill: template-authoring
Acuan penuh: `docs/TEMPLATE_SPEC.md`.
## Langkah membuat template
1. `php artisan template:make {slug}` (slug kebab-case, unik). Jangan menyalin folder template lain secara manual.
2. `manifest.json`: nama, tier, `event_types`, tag, urutan `sections`, 2 sampai 4 `palettes`, `motion`, `budget`.
3. `theme.css`: hanya token `--t-*` dan gaya khas. Palet lain menimpa token, bukan menyalin CSS.
4. `index.blade.php`: susun komponen `_shared`. Override section hanya bila tampilannya memang berbeda.
5. Ornamen: pakai `<x-ornament>` (SVG `currentColor`) untuk satu warna, WebP transparan untuk cat air. Satu ornamen sudut dipakai empat pojok dengan `scale: -1 1` (properti `scale` terpisah dari `transform`, jadi tidak bentrok dengan animasi `sway`).
6. Pastikan tampil menarik: lapisan bg, pola samar, bingkai, sudut, konten, pembatas, elemen melayang (lihat slot di spesifikasi). Dua atau tiga ornamen per section, ruang kosong secukupnya.
7. Gerak: reveal gulir dari `core.js`, ambient `sway` atau kilau seperlunya. Efek GSAP hanya di `theme.js` dengan import dinamis.
8. `php artisan template:lint {slug}`, periksa `/preview/{slug}` di 360, 768, 1280 px, semua palet, mode kurangi gerakan.
9. Buat `thumb.webp` (600×1200) dan `preview.webp`.
## Larangan
Eloquent atau query di template, CDN, font tambahan, warna di luar blok token, ornamen yang menutupi tombol, animasi `top/left/width/height`, aset tanpa lisensi tertulis.
## Selesai bila
Lint hijau, axe tanpa pelanggaran kritis, anggaran bobot terpenuhi, semua palet kontras 4,5 : 1, pemilik menyetujui tampilan, template muncul di katalog tanpa mengedit file lain.
