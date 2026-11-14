---
paths: ["resources/css/**", "resources/js/**", "resources/views/**", "resources/templates/**"]
---
- Tailwind 4 lewat Vite saja: tanpa Play CDN, tanpa CDN baru, tanpa jQuery/Bootstrap.
- Token dari `resources/css/tokens.css`; dilarang hex mentah di komponen. Dua font saja: Cormorant Garamond dan Plus Jakarta Sans, self-host.
- Tanpa `<style>` atau `style=""` inline kecuali nilai dinamis lewat CSS variable.
- Teks ≥ 14 px, target sentuh ≥ 44 px, kontras ≥ 4,5 : 1, fokus terlihat, uji 360/768/1280 px.
- Ikon SVG inline satu set; tanpa emoji sebagai ikon. Gambar: `width`/`height`, WebP, `loading="lazy"` kecuali layar pertama.
