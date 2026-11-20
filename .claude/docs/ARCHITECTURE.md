# Arsitektur

## Gaya
Monolit modular Laravel. Satu aplikasi, dua host, batas yang jelas antara pembuat (dashboard) dan penyaji (undangan publik).

```
momenikah.online        → pemasaran (diindeks)
app.momenikah.online    → masuk, dashboard, /admin (sesi dan cookie)
undangan.momenikah.online → /{slug} undangan, /preview/{template} pratinjau (tanpa sesi dan cookie)
```
Halaman undangan tidak memakai sesi. Hitungan kunjungan memakai cookie kecil. Rute undangan ada di `routes/public.php`, didaftarkan lewat `Route::domain(config('surfaces.public.domain'))` dengan grup middleware `public` (`StatelessPublic`, tanpa sesi, cookie, dan CSRF).

## Struktur folder (nyata)
```
app/
  Domain/                       tanpa Http, Livewire, Filament (dijaga arch test)
    Account/  Audit/  Billing/  Guest/  Invitation/  Media/  Templates/
      Models/ Enums/ Services/ Policies/ Data/ Exceptions/   (sesuai konteks)
    Invitation/EventTypes/      EventTypeDefinition, EventTypeRegistry (isi dari config/event-types.php)
    Templates/                  TemplateRegistry, TemplateManifest, TemplateRenderer, TemplateLinter,
                                TemplateScaffolder, TemplateSampleData, InvitationViewModel(+Builder), SectionRegistry, Labels
    Billing/                    Entitlements, SubscriptionResolver, PlanCatalog, Money
  Http/Controllers/{Marketing,Dashboard,Share,Api}   Http/Middleware   Http/Requests
  Console/Commands/             template:make, template:lint, templates:cache|clear, invitations:expire, media:prune-orphans, backup:*
  Providers/                    DomainServiceProvider, TemplateServiceProvider, GstMediaServiceProvider, FortifyServiceProvider
config/                         surfaces, entitlements, event-types, templates, momenikah, media-limits, guests, security, reserved-slugs
resources/
  css/ tokens.css  app.css  landing.css  invitation-core.css
  js/  app.js  landing.js  public.js  invitation/{core,cover,countdown,lightbox,reveal,theme-loader}.js  public/{guest-name,music}.js
  svg/ icons/  ornaments/       ikon UI dan ornamen currentColor
  views/ marketing/ dashboard/ auth/ preview/ components/{ui,layouts,invitation,marketing,brand,seo}
  templates/
    _shared/sections/           13 partial: cover, opening, people, events, story, gallery, gift, rsvp, guestbook, countdown, map, music, closing
    classic-elegant/            manifest.json, index.blade.php, theme.css, ornaments/, assets/, thumb.webp, preview.webp
    (enam template penuh menyusul: TPL-09…13)
routes/{marketing,app,public}.php
public/ brand/ fonts/ template-sample/   favicon, logo, font self-host, gambar contoh
tests/{Feature,Unit,Arch,e2e,e2e-media,standalone}
```
Menambah template tidak menyentuh folder lain: `php artisan template:make {slug}` membuat folder, `template:lint` memeriksanya. `TemplateRegistry` memindai `manifest.json`, jadi tidak ada daftar yang harus diedit manual.

## Alur render undangan
1. `GET undangan.../{slug}` → `Share\InvitationController@show` mencari undangan berstatus `published` (selain itu 404).
2. `InvitationViewModelBuilder::fromModel($invitation)` (relasi di-eager-load di sana) membuat objek read-only (tanggal terformat, URL media, section aktif, teks bawaan jenis acara).
3. `TemplateRenderer` memilih view `templates.{slug}.index` dan entri Vite `theme.css` / `theme.js` milik template itu.
4. HTML bersifat netral terhadap tamu: tidak ada nama tamu di sisi server. `core.js` membaca `?to=`, memotong 60 karakter, dan menulisnya dengan `textContent` ke slot `[data-guest]`. Cara ini membuat halaman bisa di-cache penuh di Cloudflare dengan aturan cache yang mengabaikan parameter `to`.
5. Respons membawa `X-Robots-Tag: noindex, nofollow`, `Cache-Control` publik berumur pendek dengan `stale-while-revalidate`, dan ETag. Saat undangan diubah atau dipublish, cache di-purge lewat API Cloudflare (tugas OPS-06).
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
