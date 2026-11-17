---
paths:
  - "resources/views/marketing/**"
  - "resources/views/layouts/marketing.blade.php"
  - "resources/views/partials/seo/**"
  - "app/Http/Controllers/Marketing/**"
  - "app/Services/Seo/**"
  - "public/robots.txt"
---
# Aturan SEO
- Setiap halaman pemasaran memakai komponen `<x-seo>` (title, description, canonical, OG, Twitter, JSON-LD). Jangan menulis meta secara manual di halaman.
- Satu `<h1>` per halaman, judul dan deskripsi unik, canonical absolut, `lang="id"`. Panjang judul ≤ 60 karakter, deskripsi 120–155 karakter.
- Halaman undangan (`share.`) dan pratinjau selalu `noindex, nofollow` lewat header `X-Robots-Tag` dan meta. Jangan memblokir domain `share.` dengan `robots.txt` supaya pratinjau tautan di WhatsApp dan media sosial tetap bekerja.
- Sitemap hanya berisi halaman pemasaran dan template, tidak berisi undangan, dashboard, atau admin.
- JSON-LD hanya untuk fakta yang benar. Jangan membuat `AggregateRating`, `Review`, atau harga yang belum ada datanya.
- Gambar OG 1200×630 (sumber: `.claude/brand/social/og-default.png` dan konversi `og` dari foto sampul).
