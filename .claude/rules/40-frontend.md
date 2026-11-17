---
paths:
  - "resources/views/**"
  - "resources/css/**"
  - "resources/js/**"
  - "resources/templates/_shared/**"
---
# Aturan frontend
- Tailwind 4 lewat Vite, token di `resources/css/tokens.css`. Dua font saja: Cormorant Garamond (judul) dan Plus Jakarta Sans (antarmuka). Skala huruf: 12/14/16/18/20/24/30/36/48/60.
- Komponen Blade di `resources/views/components` (`x-ui.button`, `x-ui.field`, dan seterusnya). Jangan menulis ulang tombol atau input di halaman.
- Ukuran sentuh ≥ 44 px, teks ≥ 14 px (kecuali ilustrasi), kontras ≥ 4,5 : 1, fokus terlihat, ikon SVG inline (bukan emoji), `lang="id"`.
- Gambar: WebP/AVIF, `width` dan `height` selalu ada, `loading="lazy"` kecuali gambar di layar pertama, `srcset` untuk foto.
- JS: Alpine untuk interaksi kecil, tanpa jQuery. GSAP hanya di entri `landing.js` dan modul efek template yang memang membutuhkannya (import dinamis).
- Animasi: `transform` dan `opacity`, durasi 200–700 ms untuk UI, `gsap.matchMedia()` atau `@media (prefers-reduced-motion)` untuk menonaktifkan. Konten harus terbaca tanpa JavaScript.
- Periksa di 360, 768, 1280 px, mode terang dan gelap bila halaman mendukungnya.
