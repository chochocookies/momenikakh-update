# Temuan pada `tokens.css` (DS-01/DS-02) dan cara memperbaikinya

Tailwind 4 hanya membuat utilitas dari namespace variabel tema tertentu: `--color-*`, `--font-*` (keluarga huruf), `--text-*` (ukuran), `--font-weight-*`, `--tracking-*`, `--leading-*`, `--radius-*`, `--shadow-*`, `--breakpoint-*`, `--container-*`, `--spacing-*`, `--ease-*`, `--animate-*`. Nama di luar itu tidak menghasilkan utilitas.

| Di `tokens.css` sekarang | Akibat | Perbaikan |
|---|---|---|
| `--font-family-sans`, `--font-family-serif` | `font-sans` dan `font-serif` tetap memakai huruf bawaan Tailwind, bukan Plus Jakarta Sans dan Cormorant Garamond | ganti nama menjadi `--font-sans` dan `--font-serif` (tambahkan `--font-display` bila ingin utilitas `font-display`) |
| `--font-size-*` | `text-xl` dan kawan-kawan memakai skala bawaan | ganti nama menjadi `--text-xs`, `--text-sm`, dan seterusnya |
| `--line-height-*` | `leading-*` memakai skala bawaan | ganti menjadi `--leading-tight`, `--leading-snug`, ... |
| `--letter-spacing-*` | `tracking-*` memakai skala bawaan | ganti menjadi `--tracking-tight`, `--tracking-wide`, ... |
| `--color-template-beach-bg: linear-gradient(...)` dan `...-floral-bg` | gradien bukan warna: `bg-template-beach-bg` menghasilkan CSS tidak valid | pindahkan ke `:root` sebagai `--template-beach-bg` lalu pakai `bg-(image:--template-beach-bg)` (cek sintaksnya di dokumentasi Tailwind 4 versi yang terpasang) |
| `--spacing-1` ... `--spacing-32` dan `--spacing-px` | sama dengan skala bawaan, hanya menambah ukuran berkas | hapus, atau cukup atur `--spacing: 0.25rem` |
| `--duration-*` | bukan namespace Tailwind, tidak ada utilitas `duration-fast` | tetap boleh sebagai variabel biasa; pakai `duration-(--duration-fast)` |
| teks emas `--color-brand-gold-alt` (#B8893B) | kontras 2,99 : 1 di atas gading | hanya untuk dekorasi, jangan untuk teks (dijaga oleh `HygieneTest`) |

Contoh perbaikan blok huruf:
```css
@theme {
  --font-sans: 'Plus Jakarta Sans', system-ui, sans-serif;
  --font-serif: 'Cormorant Garamond', Georgia, serif;
  --font-display: 'Cormorant Garamond', Georgia, serif;
  --text-sm: 0.875rem;
  --leading-normal: 1.5;
  --tracking-wide: 0.025em;
}
```
Setelah diperbaiki, perbarui `.font-loaded-*` yang masih merujuk `--font-family-*`. Tes yang mengawasi hal ini: `TokensTest` ("exposes the brand fonts...", "uses Tailwind namespaces...", "does not place non-colour values in the colour namespace").

Catatan nama warna: token Anda memakai awalan `brand-` (`bg-brand-rose`, `text-brand-ink`). Itu sah, dan tes membaca nama itu langsung.
