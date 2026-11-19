---
name: deployment-and-release
description: Rilis Momenikah ke staging dan produksi: checklist, migrasi aman, cache, antrian, DNS, rollback, dan pemantauan. Gunakan untuk FND-06, OPS-01, OPS-08.
---
# Skill: deployment-and-release
## Sebelum rilis
CI hijau, `composer audit` dan `npm audit` bersih, `npm run build`, migrasi ditinjau, cadangan DB baru, catatan rilis di `CHANGELOG.md`.
## Langkah (staging lalu produksi)
1. Mode pemeliharaan bila perlu. `git pull`, `composer install --no-dev --optimize-autoloader`, `npm ci && npm run build` (atau artefak CI).
2. `php artisan migrate --force` (hanya migrasi baru). `config:cache`, `route:cache`, `view:cache`, `event:cache`.
3. Muat ulang PHP-FPM dan worker antrian (`queue:restart`). Purge cache edge bila aset berubah.
4. Smoke test: beranda, masuk, buka undangan contoh di `share.`, RSVP uji, `/up`, pembayaran mode uji.
5. Pantau galat (Sentry/Flare) dan antrian selama 30 menit.
## Rollback
Kembali ke rilis sebelumnya, `migrate:rollback` hanya bila migrasi aman untuk dibalik, pulihkan cadangan bila data rusak. Rencana rollback ditulis sebelum rilis.
## Cutover DNS
Turunkan TTL sehari sebelumnya, uji di staging, ganti DNS, awasi, pertahankan redirect `/u/{slug}` lama (OPS-07).
