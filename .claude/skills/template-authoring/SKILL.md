---
name: template-authoring
description: Create or change an invitation template (manifest, sections, theme.css/js, assets, budgets). Use for any file under resources/templates.
---
# template-authoring
1. `php artisan make:template <slug>`; isi `manifest.php` (events, tier, sections, supports, palette, motion).
2. Override hanya bagian yang berbeda dari `_shared`. Warna/jarak lewat token `--inv-*` di `theme.css`; animasi di `theme.js` (`init({gsap, ScrollTrigger, root})`).
3. Lapisan visual berurutan (lihat `docs/04`); 2–3 ornamen per bagian; SVG satu warna + `currentColor`; WebP untuk cat air; ornamen sudut dibalik untuk empat pojok.
4. Aset: ≤ 400 KB total, tidak ada font baru; lisensi di `assets/LICENSE.md` (dilarang aset berlisensi yang melarang aplikasi kustomisasi pengguna).
5. Data hanya dari `$invitation`; nama tamu lewat core JS.
6. Verifikasi: render untuk setiap jenis acara di manifest, 3 lebar layar, reduced-motion, kontras, `templates:audit`.
Kesalahan umum: menganimasikan `top/left`; ornamen menutupi tombol (`pointer-events:none`); teks di atas ornamen ramai.
