# Brand Momenikah

Status: **Proposed** (U-11) sampai pemilik menyetujui. Nada dan warna sudah dinyatakan cocok oleh pemilik; logo, ikon, dan favicon di folder `.claude/brand` adalah rancangan pertama.

## Inti
Momenikah = *momen* + *nikah*. Huruf **n** dipakai bersama oleh kedua kata, dan itu menjadi ciri wordmark: "Mome**n**ikah" dengan n berwarna rose. Janji: **Satu link untuk semua momen nikahmu.** Nada: hangat, elegan, romantis, tenang, premium tanpa terasa jauh. Bahasa: Indonesia, "kamu/kalian", kalimat pendek, tanpa kata hiperbola.

## Logo
Lambang: dua cincin yang saling mengait dengan hati di atasnya (cincin dan permata). Wordmark: Cormorant Garamond SemiBold, huruf n berwarna rose. Semua huruf sudah diubah menjadi path, jadi tidak butuh font saat dipakai.

| Berkas (`.claude/brand/logo/`) | Pemakaian |
|---|---|
| `momenikah-logo-horizontal.svg/.png` | Header, email, dokumen (latar terang) |
| `momenikah-logo-horizontal-reverse.svg/.png` | Latar gelap |
| `momenikah-logo-stacked.svg/.png` | Poster, kartu, layar pembuka |
| `momenikah-wordmark.svg` | Footer, ruang sempit |
| `momenikah-icon.svg`, `-reverse.svg`, `-mono.svg` | Lambang saja, mode satu warna (`currentColor`) |

Aturan: ruang bebas minimal setinggi huruf "M" di sekeliling logo. Lebar minimum logo horizontal 120 px, lambang 24 px. Jangan memutar, meregangkan, menambah bayangan, atau mengganti warna di luar palet.

## Favicon dan ikon aplikasi (`.claude/brand/favicon/`)
`favicon.svg` (modern), `favicon.ico` (16/32/48), `favicon-16x16.png`, `favicon-32x32.png`, `apple-touch-icon.png` (180), `icon-192.png`, `icon-512.png`, `icon-maskable-512.png`, `site.webmanifest`. Gambar sosial: `social/og-default.png` (1200×630) dan `.svg`. Semuanya dibuat ulang oleh `python3 .claude/brand/build-brand.py` bila token berubah.

## Warna
| Token | Hex | Pakai untuk |
|---|---|---|
| rose | `#BE185D` | CTA, aksen, n di wordmark (kontras 5,9 : 1 di atas gading) |
| rose-light | `#F472B6` | aksen di latar gelap |
| rose-soft | `#FCE7F3` | lencana, hover |
| ink | `#2B1B22` | teks utama, latar gelap |
| muted | `#6B5A62` | teks sekunder |
| ivory | `#FFF8F3` | latar halaman |
| gold | `#B8893B` | hanya untuk elemen premium |

## Tipografi
Cormorant Garamond 500/600/700 untuk judul dan nama. Plus Jakarta Sans 400/500/700/800 untuk teks dan antarmuka. Skala 12/14/16/18/20/24/30/36/48/60. Judul besar boleh rapat (`letter-spacing: -0.01em`), teks isi 16 px dengan tinggi baris 1,65.

## Bentuk dan gerak
Radius 8 / 12 / 999. Tombol pil. Bayangan lembut berwarna ink 8–15%. Gerak: halus dan tenang, 200–700 ms, kurva `power3.out` atau `cubic-bezier(.7,0,.2,1)`. Motif: dua cincin yang bertaut, garis tipis, ornamen bunga. Dilarang: kilauan berlebihan, emoji sebagai ikon.

## Suara
Hangat, bukan formal yang kaku. Jelas: satu tombol, satu tujuan. Romantis tanpa berlebihan. Contoh CTA: "Buat undangan", "Coba template", "Salin link". Hindari: "terbaik se-Indonesia", "ribuan pengguna", atau klaim lain yang tidak bisa dibuktikan.
