# Arsitektur

## Gaya
Monolit modular Laravel. Satu aplikasi, dua host, batas yang jelas antara pembuat (dashboard) dan penyaji (undangan publik).

```
momenikah.online        → pemasaran, auth, dashboard, admin (/admin), webhook pembayaran
share.momenikah.online  → /{slug} undangan, /preview/{template} pratinjau (cookie dan sesi terpisah)
```
Halaman undangan tidak memakai sesi. Hitungan kunjungan memakai cookie kecil. Rute `share` didaftarkan lewat `Route::domain(config('app.share_domain'))` dan tidak memuat middleware sesi.

## Struktur folder (efektif untuk banyak template)
```
app/
  Enums/                 EventType, InvitationStatus, PartyRole, EventKind, PlanTier, OrderStatus
  EventTypes/            Wedding.php, WalimatulUrs.php, Khitanan.php, Aqiqah.php, Birthday.php, Engagement.php
                         EventTypeDefinition.php (kontrak), EventTypeRegistry.php
  Models/                User, Invitation, Party, Event, Section, Guest, Rsvp, Wish, GiftAccount, Plan, Order, Payment
  Policies/
  Services/
    Templates/           TemplateRegistry, TemplateManifest, TemplateRenderer, TemplateSampleData, SectionRegistry
    Invitations/         InvitationViewModel, InvitationPublisher, SlugGenerator, GuestLinkBuilder
    Billing/             DuitkuClient, OrderService, PlanGate
    Seo/                 SeoData, JsonLd, SitemapBuilder
  Http/Controllers/
    Marketing/ Dashboard/ Share/ Webhook/ Api/
  View/Components/       Ui/*, Seo, Ornament
  Console/Commands/      template:make, template:lint, sitemap:build
resources/
  css/tokens.css  app.css  landing.css
  js/app.js  landing.js
  fonts/                 woff2 (subset latin)
  views/                 layouts/ components/ marketing/ dashboard/ auth/ errors/
  templates/
    _shared/
      sections/          cover, opening, person, couple, story, events, countdown, gallery, gift, rsvp, wishes, map, music, closing
      ornaments/         SVG currentColor: corner-*, divider-*, frame-*, float-*
      css/base.css       kerangka section dan tata letak (membaca variabel --t-*)
      js/core.js         buka sampul, musik, reveal, salin, galeri, hitung mundur, nama tamu dari ?to=
    classic-elegant/     (satu folder per template)
    floral-garden/
    luxury-gold/ ...
public/                  favicon dan ikon dari .claude/brand/favicon, robots.txt
tests/{Feature,Unit,Browser}
```
Menambah template tidak menyentuh folder lain: cukup membuat `resources/templates/{slug}/` (`php artisan template:make {slug}`). `TemplateRegistry` memindai manifest, jadi tidak ada daftar yang harus diedit manual.

## Alur render undangan
1. `GET share.../{slug}` → `PublicInvitationController@show` memuat undangan dan relasinya (eager loading).
2. `InvitationViewModel::from($invitation)` membuat objek read-only (tanggal terformat, URL media, section aktif, teks bawaan jenis acara).
3. `TemplateRenderer` memilih view `templates.{slug}.index` dan entri Vite `theme.css` / `theme.js` milik template itu.
4. HTML bersifat netral terhadap tamu: tidak ada nama tamu di sisi server. `core.js` membaca `?to=`, memotong 60 karakter, dan menulisnya dengan `textContent` ke slot `[data-guest]`. Cara ini membuat halaman bisa di-cache penuh di Cloudflare dengan aturan cache yang mengabaikan parameter `to`.
5. Respons membawa `X-Robots-Tag: noindex, nofollow`, `Cache-Control` publik berumur pendek dengan `stale-while-revalidate`, dan ETag. Saat undangan diubah atau dipublish, cache di-purge lewat API Cloudflare (tugas F3-13).
6. Aksi tamu (RSVP, ucapan, ping "sudah buka") memakai `fetch` JSON ke `/api/*`, dilindungi Turnstile dan throttle.

## Alur pembuatan (dashboard)
Livewire 4 membentuk wizard dari `EventTypeDefinition` (kelompok, field, aturan). Setiap langkah disimpan otomatis. Pratinjau memuat iframe `/preview/{invitation}` dengan data draf lewat `InvitationViewModel` yang sama. Satu sumber data untuk form, pratinjau, dan halaman publik.

## Batas keamanan
Dashboard dan undangan berbeda host dan cookie. Kode kustom pengguna (bila suatu hari diizinkan) hanya berjalan di host `share`. Admin di `/admin` dengan 2FA.

## Caching
Edge: halaman undangan dan aset (immutable, nama file ber-hash). Aplikasi: `Cache::remember` untuk katalog template dan sitemap. Kunci cache undangan memuat `updated_at`. Invalidasi eksplisit saat publish, edit, dan kedaluwarsa.

## Antrian dan jadwal
Antrian: konversi gambar, email, purge cache, kedaluwarsa. Jadwal harian: tandai undangan kedaluwarsa, kirim pengingat masa aktif, cadangan DB, bersihkan media yatim, `sitemap:build`.

## Observabilitas
Log terstruktur, Sentry/Flare, Pulse untuk antrian dan permintaan lambat, `/up` untuk health check.
