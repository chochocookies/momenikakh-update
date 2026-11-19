---
name: performance-optimization
description: Mengoptimalkan kecepatan Momenikah: halaman undangan di jaringan seluler, landing GSAP, gambar, font, cache edge, query, dan anggaran bobot. Gunakan untuk TPL-14, OPS-06, MKT-07, QA-03.
---
# Skill: performance-optimization
## Prioritas
1. Ukur dulu (Lighthouse ponsel, WebPageTest, k6). Catat sebelum dan sesudah. Jangan mengoptimasi tanpa angka.
2. **Gambar**: WebP/AVIF, `srcset`, `width`/`height`, lazy kecuali layar pertama, ornamen kecil dan reuse (satu sudut untuk empat pojok).
3. **Font**: woff2 subset latin, `font-display: swap`, preload hanya yang dipakai di layar pertama.
4. **JS/CSS**: Vite code-split per template, tanpa jQuery, GSAP di chunk terpisah, `core.js` ≤ 8 KB gzip.
5. **Cache**: halaman undangan di edge (abaikan `to`), aset immutable, purge saat publish. `Cache::remember` untuk katalog dan sitemap.
6. **Server**: eager loading, indeks, OPcache, antrian untuk kerja berat (konversi gambar, email).
7. **Gerak**: hanya `transform` dan `opacity`, `will-change` seperlunya, kurangi elemen melayang di ponsel.
## Anggaran
CSS tema ≤ 30 KB, JS tema ≤ 20 KB, aset template ≤ 400 KB, JS landing ≤ 90 KB, gambar layar pertama landing ≤ 250 KB. CI gagal bila terlampaui (TPL-14).
