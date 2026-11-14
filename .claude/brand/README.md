# brand/

Sumber aset merek. Salin ke repo (`public/`) pada tugas DS-05. Status logo: usulan, menunggu persetujuan (DEC-10).

- `logo/` — SVG (path, tanpa ketergantungan font): horizontal, putih, bertumpuk, mark, mark satu warna. `logo/png/` — PNG transparan (1600 px lebar; mark 512/1024).
- `favicon/` — `favicon.svg/.ico`, `favicon-16/32/48.png`, `apple-touch-icon.png`, `android-chrome-192/512.png`, `maskable-512.png`, `safari-pinned-tab.svg`, `site.webmanifest`, `browserconfig.xml`. Letakkan semuanya di akar `public/`.
- `social/` — `og-default.png`, `og-{pernikahan,walimatul-urs,tunangan,khitanan,aqiqah,ulang-tahun}.png` (1200×630), `avatar-400.png`.
- `seo/` — `head.blade.php`, `robots-*.txt`, `sitemap.example.xml`, `jsonld-organization-website.json`, README pemetaan.
- `gen.py`, `gen2.py` — pembuat ulang aset (Python: cairosvg, fonttools, pillow; font `@fontsource/cormorant-garamond`). Jalankan dari folder yang berisi `node_modules/@fontsource/cormorant-garamond`.
