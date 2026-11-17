# Tech stack

Status verifikasi: versi bertanda ✔ dicek dari sumber publik pada 14 November 2026. Tanda ◻ berarti belum dicek, jadi pasang versi stabil terbaru saat instalasi dan catat hasilnya di `composer.json` dan `package.json`.

| Lapisan | Pilihan | Versi | Alasan | Alternatif |
|---|---|---|---|---|
| Bahasa | PHP | 8.3 sampai 8.5 ✔ | Syarat Laravel 13 | n/a |
| Framework | Laravel | 13 ✔ (rilis 17 Mar 2026; perbaikan bug sampai Q3 2027, keamanan sampai Q1 2028) | Ekosistem lengkap, tim Anda sudah mengenalnya | Laravel 12 bila ada paket yang belum kompatibel |
| Database | MySQL 8 atau MariaDB | ◻ | Umum dan didukung hosting | PostgreSQL |
| Cache, antrian, sesi | Database di awal, Redis menyusul | ◻ | Mulai sederhana. Pindah ke Redis saat lalu lintas undangan naik | Memcached |
| UI dashboard | Blade + Livewire 4 + Alpine | Livewire 4 ✔ (syarat Filament 5) | Interaktif tanpa SPA, Alpine ikut terpasang | Inertia + Vue |
| Halaman undangan | Blade murni + JS kecil (tanpa Livewire) | n/a | Dapat di-cache penuh, ringan di ponsel | n/a |
| CSS | Tailwind CSS 4 | 4 ✔ (syarat Filament 5) | Token lewat variabel CSS | n/a |
| Build | Vite | bawaan Laravel 13 ◻ | Entri per template otomatis | n/a |
| Admin | Filament 5 | 5.x ✔ (rilis 16 Jan 2026; butuh Livewire 4, Tailwind 4, Laravel 11.28+) | Panel admin cepat | Nova (berbayar) |
| Animasi | GSAP 3 | 3.13+ ✔ (seluruh plugin gratis, termasuk ScrollTrigger dan SplitText; `npm install gsap`) | Landing dan efek premium | CSS murni, Motion One |
| Tes | Pest 4 + plugin Laravel, Livewire, browser | 4 ✔ | Sintaks ringkas, browser test | PHPUnit |
| Kualitas | Laravel Pint, Larastan, Rector (opsional) | ◻ | Gaya dan analisis statis seragam | n/a |
| Media | spatie/laravel-medialibrary | ◻ | Konversi WebP, koleksi, antrian | Intervention Image manual |
| Slug | spatie/laravel-sluggable atau layanan sendiri | ◻ | Unik, daftar reserved | n/a |
| Sitemap | spatie/laravel-sitemap atau pengendali sendiri | ◻ | Hanya halaman pemasaran | n/a |
| Cadangan | spatie/laravel-backup | ◻ | Cadangan DB harian ke penyimpanan luar | Skrip mysqldump + cron |
| Pembayaran | Duitku (HTTP client Laravel) | ◻ | Metode lokal (VA, QRIS, e-wallet) | Midtrans, Xendit, Tripay |
| Captcha | Cloudflare Turnstile | ◻ | Gratis, ramah privasi | hCaptcha |
| Galeri | PhotoSwipe 5 atau GLightbox | ◻ | Lightbox ringan tanpa jQuery | n/a |
| Ikon | SVG inline (Lucide, MIT) | ◻ | Ringan, tanpa font ikon | Heroicons |
| Font | Cormorant Garamond + Plus Jakarta Sans, woff2 di-host sendiri | n/a | OFL, tanpa permintaan ke Google saat runtime | Fontsource |
| Peta | Tautan Google Maps + embed iframe opsional | n/a | Tanpa API key sendiri | Leaflet + OSM |
| Penyimpanan | S3-kompatibel (mis. Cloudflare R2) + CDN | ◻ | Foto dan musik tidak membebani server | Disk lokal di awal |
| Edge | Cloudflare (DNS, cache, WAF) | ◻ | Cache halaman undangan, perlindungan dasar | n/a |
| Pemantauan | Sentry atau Flare, Laravel Pulse | ◻ | Galat dan performa | n/a |
| CI/CD | GitHub Actions | n/a | Lint, analisis, tes, build, audit | n/a |
| Node | 20 atau lebih baru ✔ (syarat umum starter kit Laravel) | | | |

## Yang sengaja tidak dipakai
jQuery dan Bootstrap (berat dan membuat gaya tidak konsisten), Tailwind Play CDN, Font Awesome lengkap, WordPress, SPA penuh (React/Vue) untuk halaman undangan, Livewire di halaman undangan publik, captcha buatan sendiri, API key Google Maps di klien, aset dari pasar yang melarang aplikasi "on-demand".

## Lingkungan
`local` (Herd/Sail) → `staging` (data uji, domain terpisah) → `production`. Variabel penting di `.env.example`: `APP_URL`, `SHARE_DOMAIN`, `SESSION_DOMAIN` (kosong atau per host), `QUEUE_CONNECTION`, `CACHE_STORE`, `FILESYSTEM_DISK`, `DUITKU_*`, `TURNSTILE_*`, `SENTRY_DSN`. Rahasia tidak pernah masuk repo.

## Kapasitas awal (pedoman, ukur sebelum percaya)
Satu VPS dengan LiteSpeed atau Nginx + PHP-FPM, OPcache aktif, Cloudflare di depan, halaman undangan di-cache penuh di edge, antrian satu worker. Naik ke Redis dan worker tambahan bila antrian gambar menumpuk atau waktu respons dashboard memburuk (tugas F8-05).
