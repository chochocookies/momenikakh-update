---
paths:
  - "resources/views/templates/**"
---

# Invitation templates

Six templates: classic-elegant, beach-sunset, floral-garden, luxury-gold, modern-minimal, rustic-vintage. Each has `cover`, `index`, `sections/*`, `partials/*`.

- Read only real columns. These do NOT exist: `story`, `phone`, `email`, `couple_name`, `wedding_date` (and about 20 more read by classic-elegant). Real ones include `love_story`, `groom_father`, `bride_father`, `event_maps_url`, `music_url`, `gift_settings`, `gallery_photos`.
- Classic Elegant is the fallback template and shows sample data today (P0-03).
- Pass user data to JavaScript only through `data-*` attributes or `@js()`. Never put `{{ }}` inside `onclick` or a JavaScript string (P0-04).
- Do not add the Tailwind Play CDN, a new CDN script or a new font. Images need `loading="lazy"`, `decoding="async"`, `width`, `height`. Audio needs `preload="none"`.
- A template change must also consider the other five templates, the preview sample data in `TemplateController`, and a check at 360 px.
- Before structural work run `node AI_CONTEXT/cline/show.mjs debt DEBT-01` and `adr ADR-016`.
