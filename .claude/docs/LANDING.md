# Landing page Momenikah (GSAP)

Tujuan: kesan pertama yang membuat orang berhenti menggulir, tanpa mengorbankan kecepatan, SEO, atau aksesibilitas. Brand mengikuti `BRAND.md`. Teks hero ada di HTML sejak awal. Animasi hanya memperindah, bukan syarat untuk membaca.

## Teknologi
GSAP 3 lewat npm (`npm install gsap`). Sejak versi 3.13 seluruh plugin gratis, termasuk ScrollTrigger dan SplitText. Entri Vite khusus `resources/js/landing.js` dan `resources/css/landing.css`, dimuat hanya di beranda. Jangan memakai API GSAP 4 atau token berbayar lama.
```js
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';
gsap.registerPlugin(ScrollTrigger, SplitText);
```

## Narasi gulir
| # | Bagian | Animasi | Pin |
|---|---|---|---|
| 0 | Pembuka (±2,2 detik, sekali) | Cincin A dan B digambar (`stroke-dashoffset`), meluncur dari kiri dan kanan lalu bertaut, hati jatuh dan duduk di atas cincin (`back.out`), huruf wordmark naik (SplitText `chars`), huruf **n** menyala rose lalu berdenyut sekali (n bersama "momen" dan "nikah"), headline terungkap per baris (SplitText `lines` dengan mask) | tidak |
| 1 | Hero | Ponsel naik dari bawah dengan sedikit rotasi, sampul amplop terbuka, ornamen melayang berparalaks | ya (desktop) |
| 2 | Tiga langkah | Selama hero ter-pin, layar ponsel berganti tiga kali (template → isi cerita → bagikan WhatsApp), teks pendamping berganti | ya (desktop) |
| 3 | Enam gaya | Kartu template bergulir horizontal (scrub), tiap kartu menampilkan animasi khasnya (ombak, kelopak, kilau emas, garis, cap) | ya (desktop), mobile: gulir native + `scroll-snap` |
| 4 | Untuk setiap momen | Teks berjalan (marquee) enam jenis acara, kecepatan bertambah mengikuti arah gulir | tidak |
| 5 | Fitur | Kartu menumpuk lengket (sticky) dengan skala dan blur halus pada kartu di belakang | tidak |
| 6 | CTA penutup | Cincin kembali bertaut, tombol "Buat undangan" magnetik (desktop), konfeti kelopak ringan satu kali | tidak |

## Kerangka kode (landing.js)
```js
const mm = gsap.matchMedia();
mm.add('(prefers-reduced-motion: no-preference)', () => {
  // 0. intro
  const intro = gsap.timeline({ defaults: { ease: 'power3.out' } });
  intro.from('.mk-ring-a', { x: -80, opacity: 0, duration: .7 })
       .from('.mk-ring-b', { x: 80, opacity: 0, duration: .7 }, '<')
       .from('.mk-heart', { y: -60, scale: .6, opacity: 0, duration: .6, ease: 'back.out(2)' }, '-=.2');
  const wm = new SplitText('.wordmark', { type: 'chars' });
  intro.from(wm.chars, { yPercent: 100, opacity: 0, stagger: .04, duration: .5 }, '-=.3')
       .to('.wordmark-n', { scale: 1.15, yoyo: true, repeat: 1, duration: .25 }, '>-.1');
  const h1 = new SplitText('.hero h1', { type: 'lines', mask: 'lines' });
  intro.from(h1.lines, { yPercent: 110, stagger: .12, duration: .8 }, '-=.4');
  // 1-2. hero pin + phone screens (desktop only)
  mm.add('(min-width: 900px)', () => {
    const tl = gsap.timeline({ scrollTrigger: { trigger: '.hero', start: 'top top', end: '+=2400', pin: true, scrub: .6 } });
    tl.to('.phone-screen[data-i="1"]', { opacity: 1 }).to('.phone-screen[data-i="2"]', { opacity: 1 });
  });
  // reveals for the rest
  ScrollTrigger.batch('[data-reveal]', { onEnter: els => gsap.from(els, { y: 32, opacity: 0, stagger: .08, overwrite: true }), once: true });
});
mm.add('(prefers-reduced-motion: reduce)', () => { gsap.set('[data-reveal], .hero *', { clearProps: 'all' }); });
```
Catatan: `SplitText` dibuat ulang saat font siap (`document.fonts.ready`) dan di-`revert()` bila `matchMedia` berubah.

## Aturan performa
- Elemen LCP adalah headline teks, bukan gambar ponsel. Gambar ponsel `fetchpriority="low"` bila di luar layar pertama.
- Keadaan awal "tersembunyi" hanya dipasang bila JS aktif (kelas `js` pada `<html>` lewat skrip bernonce di `<head>`). Tanpa JS, semua konten terlihat.
- Jika GSAP gagal dimuat dalam 3 detik, kelas `js` dilepas dan konten tampil apa adanya.
- Anggaran: JS landing ≤ 90 KB gzip (ukur saat build), CSS ≤ 40 KB, total gambar layar pertama ≤ 250 KB. Gambar WebP dengan `width` dan `height`.
- Tidak ada `ScrollSmoother` atau gulir yang dibajak. Hanya `transform` dan `opacity`. `will-change` hanya pada elemen yang sedang dianimasikan.
- Di ponsel: tanpa pin, tanpa efek magnetik, stagger lebih pendek.

## Aksesibilitas
Mode kurangi gerakan menampilkan keadaan akhir tanpa animasi. Tombol dan tautan tetap dapat difokuskan dan terlihat saat animasi. Ornamen dekoratif `aria-hidden`. Kontras teks ≥ 4,5 : 1. Teks berjalan memiliki tombol jeda atau berhenti saat tidak ada gerakan.

## Isi (tanpa klaim palsu)
Headline "Satu link untuk semua momen nikahmu." Sub: "Buat undangan digital yang indah, bagikan lewat WhatsApp, dan biarkan tamu membuka cerita kalian dari ponsel masing-masing." CTA utama "Buat undangan", sekunder "Lihat template". Bagian fitur hanya memuat fitur yang sudah ada. Tidak ada angka pengguna, rating, testimoni, atau harga sebelum datanya nyata (D-02).

## Berkas
`resources/views/marketing/home.blade.php`, `resources/js/landing.js`, `resources/css/landing.css`, aset ilustrasi di `resources/images/landing/`. Logo dan ikon dari `.claude/brand/`.

## Prototipe
Versi awal statis ada di artefak yang sudah dibuat. Versi GSAP dikerjakan di tugas F7-02 setelah logo final disetujui.
