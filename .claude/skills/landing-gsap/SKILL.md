---
name: landing-gsap
description: Membangun landing page Momenikah dengan GSAP: narasi gulir, intro logo, pin, scrub, performa, aksesibilitas, dan fallback. Gunakan untuk tugas F7-02 dan F7-11.
---
# Skill: landing-gsap
Acuan penuh: `docs/LANDING.md`.
## Prinsip
Kesan pertama yang kuat, tetapi teks hero harus ada di HTML dan LCP tidak menunggu JS. Animasi memperindah, bukan syarat membaca.
## Langkah
1. Entri Vite terpisah `resources/js/landing.js` dan `resources/css/landing.css`, dimuat hanya di beranda.
2. `import { gsap } from 'gsap'` dan plugin `ScrollTrigger`, `SplitText` dari `gsap/...`, lalu `gsap.registerPlugin(...)`. GSAP 3 saja (seluruh plugin gratis sejak 3.13).
3. Bungkus semua animasi dalam `gsap.matchMedia()`: cabang `no-preference` untuk gerak penuh, cabang `reduce` menampilkan keadaan akhir. Cabang `(min-width: 900px)` untuk pin dan scrub, mobile memakai `ScrollTrigger.batch` dan gulir native.
4. Intro: cincin digambar dan bertaut, hati jatuh, huruf wordmark naik, **n** berdenyut sekali, headline terungkap per baris. Lalu ponsel naik dan sampul terbuka.
5. Hero ter-pin: layar ponsel berganti tiga kali (pilih template, isi cerita, bagikan WhatsApp).
6. Bagian enam gaya: gulir horizontal scrub di desktop. Teks berjalan jenis acara. Kartu fitur menumpuk (sticky). CTA penutup dengan tombol magnetik (desktop).
7. SplitText dibuat ulang setelah `document.fonts.ready`, dan di-`revert()` saat media query berubah.
## Aturan
Hanya `transform` dan `opacity`. Tanpa `ScrollSmoother` dan tanpa membajak gulir. Keadaan awal tersembunyi hanya bila kelas `js` aktif. Bila GSAP gagal dimuat dalam 3 detik, kelas `js` dilepas. Anggaran: JS ≤ 90 KB gzip, CSS ≤ 40 KB, gambar layar pertama ≤ 250 KB.
## Selesai bila
Lighthouse ponsel baik, mode kurangi gerakan benar, tanpa JS konten terbaca, tidak ada klaim palsu, logo dan warna sesuai `BRAND.md`.
