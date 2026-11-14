---
paths: ["resources/views/marketing/**", "app/Domain/Seo/**", "routes/web.php", "routes/public.php"]
---
- Semua `<head>` lewat `<x-seo.head :seo="$seo">`. Satu `<h1>`, `canonical` mutlak, `lang="id"`.
- Halaman pemasaran: indeks + JSON-LD yang sesuai. Undangan publik, preview, dasbor, admin: `noindex, nofollow` + `X-Robots-Tag`.
- Judul ≤ 60 karakter, deskripsi 120–160 karakter, unik per halaman. Gambar OG 1200×630.
- Jangan menaruh klaim yang tidak bisa dibuktikan di meta atau JSON-LD (rating, harga palsu).
