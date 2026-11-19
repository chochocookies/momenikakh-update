# SEO (referensi: linkundangan.com, disesuaikan untuk Momenikah)

## Prinsip
Situs pemasaran ramah mesin pencari. Undangan tamu tidak diindeks. Tidak ada klaim palsu. Sinyal teknis benar sejak hari pertama.

## Pelajaran dari linkundangan.com
Judul beranda memuat kata kunci utama dan manfaat ("buat undangan digital online, instan, mudah, cepat, gratis"). Ada sitemap XML dan artikel panduan. Pratinjau template bertanda `noindex, noarchive, nofollow`. Undangan tamu di subdomain terpisah. Ada tag `theme-color` dan manifest. Kita mengikuti pola teknisnya, tetapi menulis konten sendiri dan hanya mengklaim yang benar (kata "gratis" hanya jika paketnya memang ada, D-02).

## Struktur URL
| URL | Isi | Indeks |
|---|---|---|
| `/` | Beranda (landing GSAP) | ya |
| `/template` | Katalog dengan filter jenis acara, gaya, warna | ya |
| `/template/{slug}` | Detail template: gambar, palet, tombol coba dan buat | ya |
| `/undangan-pernikahan-digital` | Kategori pernikahan | ya |
| `/undangan-walimatul-urs`, `/undangan-khitanan`, `/undangan-aqiqah`, `/undangan-ulang-tahun`, `/undangan-tunangan` | Kategori per jenis acara | ya, setelah fitur tersedia |
| `/fitur`, `/harga`, `/faq`, `/kontak` | Informasi | ya (`/harga` setelah D-02) |
| `/panduan` dan `/panduan/{slug}` | Artikel cara membuat dan membagikan undangan | ya |
| `/syarat-ketentuan`, `/kebijakan-privasi` | Legal | ya |
| `/dashboard/*`, `/admin/*`, `/login`, `/daftar` | Aplikasi | tidak (`noindex`) |
| `share.…/{slug}`, `share.…/preview/*` | Undangan dan pratinjau | tidak (header `X-Robots-Tag`) |

## Pola judul dan deskripsi
| Halaman | Title (≤ 60) | Description (120–155) |
|---|---|---|
| Beranda | Momenikah: Undangan Pernikahan Digital | Buat undangan digital yang indah dan bagikan lewat satu link WhatsApp. Pilih template, isi data, pantau RSVP dan ucapan. |
| Katalog | Template Undangan Digital Pernikahan, Khitanan, Aqiqah | Pilih dari berbagai template undangan digital. Filter menurut jenis acara, gaya, dan warna. Coba langsung sebelum memakai. |
| Detail template | {Nama} – Template Undangan {Jenis} \| Momenikah | Lihat contoh template {Nama}: {mood}. Fitur: {tiga fitur}. Coba gratis lalu buat undanganmu. |
| Kategori | Undangan {Jenis} Digital \| Momenikah | Undangan {jenis} digital dengan {fitur khas}. Bagikan lewat WhatsApp dan pantau RSVP. |
| Panduan | {Judul artikel} \| Panduan Momenikah | Ringkasan yang menjawab pertanyaan artikel. |
Catatan: kata "gratis" hanya dipakai bila paket gratis benar-benar ada.

## Komponen `<x-seo>`
Menerima `title`, `description`, `canonical`, `image`, `type`, `noindex`, `jsonLd`. Menghasilkan: `<title>`, meta description, `canonical`, `robots` bila `noindex`, Open Graph (`og:type`, `og:title`, `og:description`, `og:image` 1200×630, `og:url`, `og:locale=id_ID`, `og:site_name`), Twitter (`summary_large_image`), `theme-color #BE185D`, tautan ikon dan manifest.

## Tag kepala yang wajib (layout pemasaran)
```html
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link rel="icon" href="/favicon.ico" sizes="48x48"><link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png"><link rel="manifest" href="/site.webmanifest">
<meta name="theme-color" content="#BE185D">
```
Berkas dari `.claude/brand/favicon/` dan `.claude/brand/social/` disalin ke `public/` (tugas DS-05).

## Data terstruktur (JSON-LD)
- `Organization` (nama, url, logo `/logo.png`), `WebSite`, `BreadcrumbList` di halaman dalam.
- Halaman template: `WebPage` + `BreadcrumbList`. Tidak ada `AggregateRating`, `Review`, atau `Offer` sebelum ada data nyata.
- `FAQPage` boleh dipakai untuk struktur isi, tetapi jangan mengharapkan tampilan khusus di hasil pencarian.
- Validasi dengan Rich Results Test sebelum rilis.

## Berkas teknis
`/sitemap.xml` (halaman pemasaran, template, panduan; `lastmod` jujur), `/robots.txt` (izinkan pemasaran, larang `/dashboard`, `/admin`, `/api`; cantumkan sitemap). Domain `share.` tidak diblokir di robots.txt, tetapi memakai `noindex`.

## Performa sebagai SEO
Target ponsel: LCP baik, CLS mendekati 0, INP baik. Font di-host sendiri dan di-preload, gambar WebP dengan `width`/`height`, GSAP dimuat sebagai chunk terpisah setelah konten terlihat, teks hero ada di HTML (tidak disembunyikan sampai JS jalan). Anggaran di CI (TPL-14, MKT-07).

## Konten awal (rencana 12 artikel panduan)
Cara membuat undangan digital pernikahan, cara membagikan undangan lewat WhatsApp ke banyak tamu, contoh kata pembuka undangan, tips memilih template, cara menambahkan peta dan hitung mundur, panduan amplop digital, etika undangan digital, susunan acara akad dan resepsi, undangan walimatul urs, undangan khitanan, undangan aqiqah, ide tema ulang tahun. Setiap artikel unik, ditulis untuk pembaca, dan menautkan ke template relevan.

## Analitik dan privasi
GA4 dan Search Console serta Bing Webmaster. Banner persetujuan untuk analitik, kebijakan privasi jelas, tanpa pelacak pada halaman undangan tamu selain hitungan kunjungan anonim. Tinjau dengan ahli hukum terkait UU Pelindungan Data Pribadi (DOM-14).

## Pratinjau tautan (WhatsApp, Facebook, X)
Undangan memakai `og:title` "Undangan Pernikahan {Nama & Nama}", `og:description` sopan dan netral, `og:image` hasil konversi `og` (1200×630) dari foto sampul. Nama tamu tidak masuk OG. Uji dengan Facebook Sharing Debugger dan tempelkan tautan ke WhatsApp.
