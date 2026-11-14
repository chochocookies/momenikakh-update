# 01 — Tech stack

Versi diverifikasi lewat pencarian pada 14 Nov 2026; jalankan `composer`/`npm` untuk angka pasti saat instalasi dan catat di `THIRD_PARTY.md` (FND-09).

| Lapisan | Pilihan | Alasan |
|---|---|---|
| Bahasa/runtime | PHP 8.3+ (target 8.4) | Laravel 13 butuh 8.3–8.5 |
| Framework | **Laravel 13** (monolit modular) | Rilis 17 Mar 2026; dukungan bug 18 bln, keamanan 2 thn; satu codebase untuk tim kecil |
| UI aplikasi | **Livewire 4** + Blade + Alpine (bawaan Livewire) | Editor interaktif tanpa SPA; server-render |
| CSS | **Tailwind CSS 4** via Vite | Wajib untuk Filament 5; token lewat `@theme` |
| Admin | **Filament 5** (satu-satunya admin) | Butuh Livewire 4 + Tailwind 4; menghindari dua panel admin |
| Animasi | **GSAP** + ScrollTrigger (npm, dibundel Vite) | Landing tidak statis; template bergerak halus. Verifikasi lisensi di gsap.com (FND-09) |
| Database | MySQL 8.4 (atau MariaDB setara) | Standar hosting Indonesia |
| Cache/queue/sesi | Redis | Cache halaman undangan, antrean gambar/OG |
| Media | Spatie Media Library + Imagick/Intervention Image | Konversi WebP via queue |
| Penyimpanan | S3-kompatibel (mis. Cloudflare R2) + CDN | DEC-04 |
| Pembayaran | **Duitku** di balik interface `PaymentGateway` | Pasar Indonesia; adapter bisa diganti |
| Captcha | Cloudflare Turnstile | Tanpa fingerprint berat; gratis |
| Email | SMTP/Resend/Mailgun via Laravel Mail | DEC-02 |
| SEO | `spatie/laravel-sitemap` + kelas `Seo` sendiri | Kontrol penuh JSON-LD |
| Tes | Pest (+ arch tests), Playwright (e2e, visual, axe), k6 (beban) | |
| Kualitas | Laravel Pint, Larastan, `composer audit`, `npm audit`, Lighthouse CI | |
| Observabilitas | Sentry, Laravel Pulse, uptime monitor | Telescope hanya lokal |
| CI/CD | GitHub Actions → staging → produksi | |
| Hosting | VPS (Nginx/LiteSpeed + PHP-FPM + Supervisor) | DEC-02; staging wajib |

## Tidak dipakai (dan alasannya)
jQuery, Bootstrap, AOS, Font Awesome, Tailwind Play CDN (bobot dan duplikasi; Alpine + GSAP + SVG cukup). WordPress (butuh akun, paket, pembayaran). SPA Next/Nuxt (membangun ulang auth, admin, billing tanpa manfaat jelas). Custom JS pengguna (ADR-012).

## Paket Composer awal
`laravel/framework`, `livewire/livewire`, `filament/filament`, `spatie/laravel-medialibrary`, `spatie/laravel-sitemap`, `spatie/laravel-permission` (hanya bila peran bertambah), `sentry/sentry-laravel`, `laravel/pulse`; dev: `pestphp/pest`, `larastan/larastan`, `laravel/pint`, `barryvdh/laravel-debugbar` (opsional).
## Paket npm awal
`vite`, `tailwindcss`, `gsap`, `@fontsource/cormorant-garamond`, `@fontsource-variable/plus-jakarta-sans`, `photoswipe` (galeri), `@playwright/test`, `@axe-core/playwright`, `lhci`.
