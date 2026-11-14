---
name: seo-implementation
description: Implement SEO (Seo value object, meta, JSON-LD, sitemap, robots, canonical, OG) for marketing pages and noindex rules for invitations. Use for SEO tasks and new public pages.
---
# seo-implementation
Baca `docs/06-seo.md` dan `rules/60-seo.md`. Semua `<head>` lewat `<x-seo.head>`. Marketing: indeks + JSON-LD sesuai; undangan/preview/dasbor/admin: `noindex,nofollow` + `X-Robots-Tag`. Title ≤ 60, deskripsi 120–160, canonical mutlak, `lang="id"`, OG 1200×630. Tidak ada klaim yang tak bisa dibuktikan. Validasi dengan Rich Results Test dan Lighthouse CI.
