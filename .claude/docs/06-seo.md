# 06 — SEO (referensi: linkundangan.com)

Pelajaran dari pesaing: pratinjau template dipisah dan `noindex`; undangan asli di subdomain terpisah; situs pemasaran yang diindeks memuat jenis acara dan fitur. Kita meniru strukturnya, bukan aset atau salinan.

## Peta halaman yang diindeks (`momenikah.online`)
| URL | Tujuan | Schema |
|---|---|---|
| `/` | landing | Organization, WebSite, SoftwareApplication |
| `/template` | katalog, filter jenis acara & gaya | BreadcrumbList |
| `/template/{slug}` | detail + iframe preview (dari `undangan.` noindex) | BreadcrumbList |
| `/jenis-acara/{pernikahan,walimatul-urs,tunangan,khitanan,aqiqah,ulang-tahun}` | satu halaman per jenis | FAQPage, BreadcrumbList |
| `/fitur`, `/panduan`, `/panduan/{slug}` | fitur dan konten edukasi | FAQPage / Article |
| `/harga` | hanya setelah DEC-01 | — (tanpa Offer palsu) |
Tidak diindeks: `undangan.*`, `app.*`, `/preview/*`, hasil pencarian internal.

## Kluster kata kunci (hipotesis, validasi di Search Console)
undangan pernikahan digital · undangan digital · website undangan pernikahan · undangan walimatul urs digital · undangan tunangan/lamaran digital · undangan khitanan digital · undangan aqiqah digital · undangan ulang tahun digital · contoh kata-kata undangan. Jangan mengklaim volume pencarian sebelum ada data.

## Pola meta
`<title>`: `{Judul halaman} | Momenikah` (≤ 60 karakter). Deskripsi 120–160 karakter, kalimat aktif. Canonical mutlak. `hreflang="id-ID"`. OG/Twitter: `summary_large_image`, 1200×630 (`brand/social/og-default.png`; halaman jenis acara punya OG sendiri). `theme-color #BE185D`.

## Undangan publik
`<meta name="robots" content="noindex,nofollow">` + `X-Robots-Tag`. Judul "Undangan {jenis} {nama}". OG image dibuat per undangan (nama + tema) agar pratinjau WhatsApp menarik; jangan memakai foto asli di OG default. Tanpa data pribadi di URL selain slug; slug berakhiran acak.

## Teknis
`sitemap.xml` (hanya pemasaran, dihasilkan `spatie/laravel-sitemap`), `robots.txt` per domain, redirect `www`→apex dan http→https, 404/410 yang benar, `lang="id"`, satu `<h1>`, gambar `alt`, tautan internal dari landing ke tiap jenis acara. Lighthouse CI: SEO ≥ 95; target performa mobile ≥ 85 (target, bukan jaminan).

## Konten
Panduan: "kata-kata undangan {jenis}", "cara membagikan undangan digital lewat WhatsApp", "perbedaan walimatul urs dan resepsi" (verifikasi isi dengan sumber tepercaya sebelum terbit). Setiap artikel memiliki penulis/tanggal dan tautan ke jenis acara terkait.

## Pengukuran
GA4 + Search Console + Bing Webmaster; banner consent sebelum analitik; peristiwa: `start_create`, `publish`, `share_whatsapp`.
