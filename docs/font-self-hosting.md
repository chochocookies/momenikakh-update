# DS-02: Font Self-Hosting

## Status
✅ **Font face declarations** - Selesai (lihat `resources/css/tokens.css`)
⚠️ **Font files** - Perlu diunduh manual
✅ **Font loading strategy** - Selesai dengan `font-display: swap`

## Font Files yang Dibutuhkan

### Cormorant Garamond (subset Latin)
1. `cormorant-garamond-latin-400-normal.woff2` - Weight: 400 (normal)
2. `cormorant-garamond-latin-500-normal.woff2` - Weight: 500 (medium)  
3. `cormorant-garamond-latin-600-normal.woff2` - Weight: 600 (semibold)
4. `cormorant-garamond-latin-700-normal.woff2` - Weight: 700 (bold)
5. `cormorant-garamond-latin-500-italic.woff2` - Weight: 500, Style: italic

### Plus Jakarta Sans (subset Latin)
6. `plus-jakarta-sans-latin-400-normal.woff2` - Weight: 400 (normal)
7. `plus-jakarta-sans-latin-500-normal.woff2` - Weight: 500 (medium)
8. `plus-jakarta-sans-latin-600-normal.woff2` - Weight: 600 (semibold)
9. `plus-jakarta-sans-latin-700-normal.woff2` - Weight: 700 (bold)
10. `plus-jakarta-sans-latin-800-normal.woff2` - Weight: 800 (extrabold)

## Cara Mengunduh Font

### Opsi 1: Menggunakan Script (Rekomendasi)

```bash
# Windows (PowerShell)
npm run fonts:download

# Atau jalankan langsung
powershell -ExecutionPolicy Bypass -File ./scripts/download-fonts.ps1

# Linux/Mac
chmod +x ./scripts/download-fonts.sh
./scripts/download-fonts.sh
```

### Opsi 2: Mengunduh Manual

1. Kunjungi Google Fonts:
   - Cormorant Garamond: https://fonts.google.com/specimen/Cormorant+Garamond
   - Plus Jakarta Sans: https://fonts.google.com/specimen/Plus+Jakarta+Sans

2. Pilih semua weights yang dibutuhkan (400, 500, 600, 700, 800)
3. Klik "Download family"
4. Ekstrak file ke dalam folder `public/fonts/`
5. Pastikan nama file sesuai dengan yang dideklarasikan di CSS

### Opsi 3: Menggunakan Google Fonts API (untuk pengembangan)

Untuk sementara, Anda bisa menggunakan CDN dengan menambahkan di `resources/views/layouts/app.blade.php`:

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
```

## Implementasi yang Sudah Dilakukan

### 1. Font Face Declarations
Semua deklarasi `@font-face` sudah ditambahkan di `resources/css/tokens.css` dengan:
- `font-display: swap` untuk menghindari FOIT (Flash of Invisible Text)
- `unicode-range` untuk subset Latin saja (mengurangi ukuran file)
- Format `woff2` untuk kompresi optimal

### 2. Font Loading Strategy
- **Swap Display**: Font akan langsung ditampilkan dengan font fallback, lalu diganti setelah font selesai dimuat
- **System Font Fallbacks**: Menggunakan font sistem sebagai fallback untuk performa terbaik
- **Critical CSS Classes**: Kelas `.font-loading-*` dan `.font-loaded-*` untuk kontrol FOUT/FOIT

### 3. Integration dengan Tailwind
Font families sudah diintegrasikan ke tema Tailwind:
- `--font-sans: var(--font-family-sans)`
- `--font-serif: var(--font-family-serif)`

## Verifikasi

Setelah font diunduh, verifikasi dengan:

1. **Buka `/styleguide`** - Pastikan font loading bekerja dengan benar
2. **Cek Network Tab** di Developer Tools - Pastikan tidak ada request ke `fonts.googleapis.com`
3. **Inspeksi Element** - Pastikan font-family menggunakan font lokal

## Performa Optimasi

### Ukuran Font (Estimasi)
- Cormorant Garamond subset Latin: ~120KB total
- Plus Jakarta Sans subset Latin: ~140KB total
- **Total: ~260KB** (dikompresi dengan WOFF2)

### Font Display Strategy
```
Before font loads: System fallback font
During font load: Font starts downloading
After 100ms: Display text with fallback font (swap)
After font loads: Replace with custom font
```

### Best Practices Implemented
- ✅ `font-display: swap` untuk immediate text rendering
- ✅ Subset Latin untuk mengurangi ukuran file
- ✅ WOFF2 format untuk kompresi terbaik
- ✅ Preload hints (dapat ditambahkan nanti)
- ✅ System font fallbacks

## Troubleshooting

### Font Tidak Tampil
1. Pastikan file font ada di `public/fonts/`
2. Periksa nama file sesuai deklarasi CSS
3. Cek permissions: file harus readable oleh web server
4. Verifikasi path URL di CSS

### Performa Lambat
1. Gunakan format WOFF2 (sudah)
2. Implementasi subset Latin (sudah)
3. Tambahkan preload hints jika perlu:
   ```html
   <link rel="preload" href="/fonts/cormorant-garamond-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
   ```

### Browser Compatibility
- WOFF2: Chrome 36+, Firefox 39+, Edge 14+, Safari 10+
- Fallback: Semua browser mendukung font sistem

## Monitoring

Setelah deployment, monitor:
1. **WebPageTest**: Periksa font loading timeline
2. **Lighthouse**: Skor performa font loading
3. **Browser DevTools**: Network waterfall untuk font

## Catatan untuk DS-02 Completion

**Selesai bila**: 0 permintaan ke fonts.googleapis.com (terverifikasi di Network tab)

Untuk menyelesaikan DS-02:
1. Jalankan script download font
2. Deploy ke staging
3. Verifikasi tidak ada request ke Google Fonts
4. Update status di TODO.md menjadi `[x]`