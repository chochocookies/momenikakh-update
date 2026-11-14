---
name: gsap-motion
description: GSAP animation for the landing page and invitation templates (timelines, ScrollTrigger, reduced motion, performance, cleanup). Use for any animated UI.
---
# gsap-motion
- Impor dari npm (`gsap`, `gsap/ScrollTrigger`) dan daftarkan sekali; jangan CDN di aplikasi. Verifikasi lisensi (FND-09).
- Bungkus dengan `gsap.matchMedia()`: gerak penuh di `(prefers-reduced-motion: no-preference)`, keadaan akhir statis selain itu. Kembalikan fungsi pembersih; `ctx.revert()` saat unmount.
- Hanya `x/y/scale/rotate/opacity` (`autoAlpha`). Jangan animasikan `top/left/width/height/box-shadow`.
- Satu timeline utama untuk intro; ScrollTrigger hanya untuk cerita yang memang berurutan (pin + scrub). Tanpa scroll-hijacking yang menahan pengguna; selalu bisa menggulir keluar.
- `ScrollTrigger.refresh()` setelah font/gambar siap; `invalidateOnRefresh` untuk ukuran responsif.
- Mobile: kurangi pin, jumlah elemen bergerak, dan blur; uji di ponsel menengah.
- Teks yang dipecah per huruf harus tetap terbaca layar (`aria-label` pada induk, `aria-hidden` pada potongan).
- Tingkat gerak template: `none | gentle | rich` dari manifest.
