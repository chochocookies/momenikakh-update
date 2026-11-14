# 03 — Struktur folder (repo `momenikah`)

```
.claude/                      # satu-satunya konteks AI (bukan AI_CONTEXT)
app/
  Domain/
    Events/                   # EventType enum, EventTypeRegistry, definisi per jenis
    Invitations/              # Models, Actions, DTO, Policies, ViewModel builder
    Templates/                # TemplateRegistry, Manifest, ViewModel, Console/MakeTemplate
    Guests/  Media/  Billing/ # Duitku adapter di Billing/Gateways
    Seo/                      # Seo value object, JsonLd builders
  Http/Controllers/{Marketing,App,Public}/
  Livewire/{App,Editor}/
  Filament/                   # resource admin
config/{surfaces,event-types,templates,plans}.php
database/{migrations,factories,seeders}
resources/
  css/ app.css  tokens.css  invitation-core.css
  js/ app.js  landing/*.js  invitation/core.js  invitation/loader.js
  svg/ornaments/              # ornamen bersama (SVG satu warna)
  views/{layouts,components,marketing,app,public}/
  templates/
    _shared/                  # partial & JS/CSS dasar semua template
      sections/ cover.blade.php opening.blade.php people.blade.php events.blade.php story.blade.php
                gallery.blade.php gift.blade.php rsvp.blade.php guestbook.blade.php closing.blade.php
      layout.blade.php  base.css
    classic-elegant/
      manifest.php  index.blade.php  theme.css  theme.js
      sections/       # hanya yang berbeda dari _shared
      assets/         # ornamen, tekstur (WebP/SVG) — anggaran ≤ 400 KB
      preview.webp  thumb.webp
    floral-garden/ modern-minimal/ luxury-gold/ beach-sunset/ rustic-vintage/
routes/{marketing,app,public}.php
public/                       # favicon, manifest, og-default (dari .claude/brand)
tests/{Feature,Unit,Arch,E2E}/
```
Aturan penamaan: slug template `kebab-case`; satu folder = satu template; tidak ada kode template di luar folder itu kecuali token global. Template baru ≤ 5 menit lewat `php artisan make:template`.
