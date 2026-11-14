---
name: performance-optimization
description: Keep invitation pages and the landing page fast (budgets, cache, images, JS). Use for slow pages, heavy templates, and release checks.
---
# performance-optimization
Anggaran: undangan ≤ 400 KB awal (tanpa galeri/musik); gambar dekorasi ≤ 400 KB per template; landing JS ≤ 120 KB gzip; LCP ≤ 2,5 dtk di 4G ponsel menengah; CLS < 0,1.
Taktik: cache halaman Redis; aset berhash + immutable di CDN; WebP + `srcset`; `loading="lazy"` kecuali layar pertama; font self-host subset; musik dimuat setelah interaksi; GSAP dimuat hanya di halaman yang memakainya; animasi `transform/opacity`; hindari layout thrash. Ukur dengan Lighthouse CI dan `templates:audit`; ukur sebelum dan sesudah.
