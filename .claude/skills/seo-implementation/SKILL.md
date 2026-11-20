---
name: seo-implementation
description: Menerapkan SEO Momenikah: komponen x-seo, judul dan deskripsi, JSON-LD, sitemap, robots, favicon, OG, dan aturan noindex untuk undangan. Gunakan untuk tugas PUB-07, SEO-01, DS-05, SEO-08, SEO-02.
---
# Skill: seo-implementation
Acuan: `docs/SEO.md`, aturan: `.claude/rules/60-seo.md`.
## Langkah
1. Komponen `<x-seo>` di `app/View/Components/Seo.php`. Props: `title`, `description`, `canonical`, `image`, `type`, `noindex`, `jsonLd`. Semua layout pemasaran memakainya. Tidak ada meta manual di halaman.
2. Judul ≤ 60 karakter dan deskripsi 120 sampai 155 karakter, mengikuti pola di `SEO.md`. Satu `<h1>` per halaman. `lang="id"`, canonical absolut.
3. Open Graph (`og:type`, `og:title`, `og:description`, `og:image` 1200×630, `og:url`, `og:locale=id_ID`, `og:site_name`) dan Twitter `summary_large_image`.
4. Favicon dan manifest dari `.claude/brand/favicon` ke `public/`. `theme-color #BE185D`.
5. JSON-LD: Organization, WebSite, BreadcrumbList. Tanpa AggregateRating, Review, atau Offer sebelum ada data nyata.
6. `sitemap.xml` hanya halaman pemasaran, template, panduan. `robots.txt` melarang `/dashboard`, `/admin`, `/api`, mencantumkan sitemap.
7. Undangan dan pratinjau di `undangan.`: `X-Robots-Tag: noindex, nofollow` dan meta noindex, tanpa memblokir lewat robots.txt supaya pratinjau tautan tetap bekerja. OG undangan tidak memuat nama tamu.
## Verifikasi
Rich Results Test, validator Open Graph (Facebook Sharing Debugger), tempel tautan ke WhatsApp, Lighthouse SEO, cek `view-source` untuk meta ganda.
