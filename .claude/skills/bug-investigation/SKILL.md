---
name: bug-investigation
description: Menyelidiki bug Momenikah secara sistematis: reproduksi, isolasi, akar masalah, perbaikan, dan tes regresi.
---
# Skill: bug-investigation
1. **Reproduksi**: langkah, data, lingkungan, ekspektasi vs kenyataan. Tulis tes gagal bila bisa.
2. **Isolasi**: lapisan mana (route, policy, request, service, model, view, JS)? Gunakan `php artisan route:list`, log, Telescope/Pulse, dan konsol browser. Jangan membaca `.env` atau log yang memuat data pribadi.
3. **Akar masalah**: jelaskan mengapa, bukan hanya di mana. Cek apakah ada tempat lain dengan pola yang sama.
4. **Perbaikan minimal** di satu PR dengan tes regresi. Tidak mencampur refaktor.
5. **Verifikasi**: `composer check`, uji manual alur yang terdampak, periksa efek samping (cache, antrian, tampilan di ponsel).
6. **Catat**: ringkas penyebab dan pencegahan di PR atau `CHANGELOG.md`.
Bug keamanan: jangan membahas detail eksploit di tempat publik, perbaiki dulu, lalu periksa log penyalahgunaan.
