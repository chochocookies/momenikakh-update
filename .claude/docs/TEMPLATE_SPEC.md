# Spesifikasi template

## Satu folder, satu template
```
resources/templates/classic-elegant/
  manifest.json          identitas, dukungan jenis acara, palet, section, anggaran bobot
  index.blade.php        menyusun section sesuai urutan di manifest
  cover.blade.php        sampul (amplop) dengan slot ornamen
  theme.css              HANYA token --t-* dan aturan khas template
  theme.js               opsional: efek tambahan, import dinamis (boleh memakai GSAP)
  sections/              opsional: override section (nama file = kunci section)
  assets/                ornamen khas (svg, webp), maks 400 KB total
  thumb.webp  preview.webp
```

## manifest.json
```json
{
  "slug": "classic-elegant",
  "name": "Classic Elegant",
  "version": "1.0.0",
  "tier": "free",
  "event_types": ["wedding", "walimatul_urs", "engagement"],
  "tags": ["klasik", "formal", "serif"],
  "mood": "formal, hangat, bingkai ganda",
  "sections": ["cover", "opening", "couple", "story", "events", "countdown", "gallery", "gift", "rsvp", "wishes", "closing"],
  "palettes": [
    { "key": "burgundy", "name": "Burgundy", "tokens": { "--t-bg": "#FBF5EC", "--t-ink": "#4A1F2B", "--t-accent": "#7B1E3A", "--t-orn": "#B8893B" } },
    { "key": "sage", "name": "Sage", "tokens": { "--t-bg": "#F4F6EF", "--t-ink": "#2F3B2C", "--t-accent": "#5C7A4F", "--t-orn": "#A58B4A" } }
  ],
  "motion": { "cover": "envelope", "scroll": "fade-up", "ambient": "sway" },
  "budget": { "css_kb": 30, "js_kb": 20, "assets_kb": 400 }
}
```
Satu template dengan beberapa palet melipatgandakan pilihan tanpa menambah kode. Pengguna memilih palet di dashboard, disimpan di `invitations.settings.palette`.

## Kontrak data (`$invitation`, `InvitationViewModel`)
`title`, `slug`, `eventType`, `locale`, `parties[]` (role, name, nickname, photo, parents, social), `events[]` (kind, label, startsAt, endsAt, venue, address, mapsUrl, notes), `sections.{key}` (isi teks per section), `media.{collection}[]` (url, thumb, width, height, alt), `gifts[]`, `music` (url, title), `rsvp` (aktif, batas tamu), `wishes` (aktif, jumlah), `settings` (palette, animasi). Nama tamu tidak ada di sini (lihat ARCHITECTURE).

## Token (`theme.css`)
Wajib: `--t-bg`, `--t-surface`, `--t-ink`, `--t-muted`, `--t-accent`, `--t-accent-ink`, `--t-orn`, `--t-font-display`, `--t-font-body`, `--t-radius`, `--t-shadow`. Opsional: `--t-bg-image`, `--t-texture`, `--t-frame`.

## Slot ornamen (z-index tetap)
| Slot | z | Isi | Aturan |
|---|---|---|---|
| bg | 0 | warna, tekstur, pola samar | statis |
| pattern | 1 | watermark 5–10% | statis |
| frame | 2 | bingkai tipis | statis atau fade-in |
| corner | 3 | ornamen sudut | boleh `sway` |
| content | 4 | teks, foto | kontras ≥ 4,5 : 1 |
| divider | 5 | pemisah section | statis |
| float | 6 | kelopak, kilau | ≤ 12 elemen, `transform`/`opacity` |
| ui | 7 | tombol musik, navigasi | ≥ 44 px, selalu terlihat |
`pointer-events: none` untuk semua lapisan dekoratif.

## Modul JS template
`theme.js` mengekspor `export default function init(root, ctx)`. `ctx` memuat `invitation`, `reducedMotion`, dan event `momenikah:open` (dikirim `core.js` setelah sampul dibuka). Template yang tidak butuh efek khusus tidak memiliki `theme.js`.

## Aksesibilitas dan bobot
`lang="id"`, urutan fokus logis, tombol musik berlabel, ornamen `alt=""`, kontras diperiksa pada setiap palet, mode kurangi gerakan menonaktifkan semua animasi kecuali perubahan opasitas singkat. Anggaran bobot ada di `manifest.json` dan diperiksa `template:lint`.

## Menambah template (≤ 1 hari setelah aset siap)
1. `php artisan template:make soft-rose` membuat folder dari kerangka.
2. Isi `manifest.json`, token di `theme.css`, ornamen di `assets/`.
3. Susun `index.blade.php` dengan komponen `_shared`.
4. `php artisan template:lint soft-rose`, lalu periksa di `/preview/soft-rose` pada 360, 768, 1280 px.
5. Buat `thumb.webp` dan `preview.webp`, buka PR. Template muncul di katalog otomatis.

## Menambah section baru
Buat `_shared/sections/{key}.blade.php`, daftarkan kunci di `SectionRegistry`, tambahkan ke `EventTypeDefinition` yang memakainya, tambahkan data contoh dan tes render.
