---
name: deployment-and-release
description: Deploy to staging/production safely (tags, migrations, queues, cache, rollback). Use for release tasks and OPS work.
---
# deployment-and-release
Alur: PR hijau → merge `main` → tag → deploy staging → uji asap (daftar → buat → publikasi → buka → RSVP) → deploy produksi. Migrasi aditif sebelum kode; `php artisan optimize`, `templates:cache`, restart queue; rencana rollback tertulis; cadangan DB terbaru terverifikasi. Jangan deploy Jumat sore. Catat di `docs/10-changelog.md`.
