---
name: ui-ux-implementation
description: Build or change Momenikah screens (dashboard, editor, marketing) with the design system. Use for any UI work outside invitation templates.
---
# ui-ux-implementation
- Pakai komponen `resources/views/components/*` dan token `tokens.css`; tidak ada hex mentah, tidak ada `<style>` inline.
- Mobile-first: 360 → 768 → 1280 px. Teks ≥ 14 px, target sentuh ≥ 44 px, kontras ≥ 4,5 : 1, fokus terlihat.
- Salinan: Indonesia, kalimat pendek, tombol = kata kerja ("Simpan perubahan", bukan "Submit"); kesalahan menjelaskan apa yang salah dan cara memperbaikinya; layar kosong mengajak bertindak.
- Livewire: validasi inline, status memuat/simpan terlihat, autosave dengan indikator.
- Periksa 3 lebar layar dan keyboard-only; jalankan axe (Playwright).
- Merek dan gerak: muat `brand-identity` dan `gsap-motion`.
