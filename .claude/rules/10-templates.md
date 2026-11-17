---
paths:
  - "resources/templates/**"
  - "app/Services/Templates/**"
---
# Aturan template
- Satu template = satu folder `resources/templates/{slug}/` yang berdiri sendiri. Isi minimal: `manifest.json`, `index.blade.php`, `cover.blade.php`, `theme.css`. Opsional: `theme.js`, `sections/` (hanya override), `assets/`.
- `slug` unik, huruf kecil dan tanda hubung. Jangan ganti slug setelah ada undangan yang memakainya.
- Template hanya menerima `$invitation` (`InvitationViewModel`). Nama tamu ditampilkan lewat slot `<span data-guest>Tamu Undangan</span>` yang diisi `core.js`. Jangan memakai model, query, `auth()`, atau `request()`.
- Semua section yang umum memakai komponen dari `_shared`. Override section hanya bila tampilannya memang berbeda, dan tetap mengikuti kontrak variabel di `docs/TEMPLATE_SPEC.md`.
- Warna, font, dan jarak diambil dari variabel CSS tema (`--t-*`) yang didefinisikan di `theme.css`. Jangan menulis warna heksadesimal di luar blok token.
- Ornamen dekoratif: `alt=""` dan `aria-hidden="true"`, `pointer-events: none`, dan tidak menutupi teks atau tombol. Paling banyak 3 ornamen per section.
- Bobot: CSS tema ≤ 30 KB, JS tema ≤ 20 KB (gzip), aset gambar total ≤ 400 KB per template. Jalankan `php artisan template:lint {slug}` sebelum PR.
- Setiap template wajib punya sample data yang membuat semua section tampil, gambar `thumb.webp` (600×1200) dan `preview.webp`.
