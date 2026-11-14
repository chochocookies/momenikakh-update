---
paths: ["resources/templates/**", "app/Domain/Templates/**"]
---
- Template hanya membaca `$invitation` (`InvitationViewModel`). Dilarang: Eloquent, `request()`, `auth()`, query di Blade.
- Bagian yang sama dengan `_shared` jangan disalin; pakai `@includeFirst` dan override seperlunya.
- CSS template = token (`--inv-*`) + gaya khas. JS template = `export function init({ gsap, ScrollTrigger, root })`.
- Anggaran per template: gambar dekorasi ≤ 400 KB, font tambahan 0, dependensi JS baru 0 (`templates:audit`).
- Gerak hanya `transform`/`opacity`; hormati `prefers-reduced-motion`; kontrol musik selalu terlihat.
- Nama tamu diisi dari `?to=` lewat `textContent`, tidak pernah `innerHTML`.
