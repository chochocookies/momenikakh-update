---
name: testing-validation
description: Menulis dan menjalankan tes Pest untuk Momenikah: feature, unit, kontrak, keamanan, dan browser. Gunakan untuk setiap tugas dan untuk F8.
---
# Skill: testing-validation
Acuan: `.claude/rules/50-tests.md`.
## Strategi
- **Feature test** untuk alur pengguna. **Unit test** untuk aturan murni (slug, zona waktu, PlanGate). **Tes kontrak** agar field tidak hilang di salah satu lapisan. **Tes keamanan**: IDOR, XSS, unggah palsu, throttle. **Browser test** (Pest browser): undangan di 360 dan 1280 px.
- Nama tes menjelaskan perilaku. Factory dan `RefreshDatabase`. Layanan luar di-fake (HTTP Duitku, Turnstile).
## Langkah
1. Tulis tes gagal yang menggambarkan kriteria "Selesai bila".
2. Implementasikan sampai hijau.
3. `composer check` lalu `npm run build`.
4. Untuk UI: periksa manual di 360, 768, 1280 px dan mode kurangi gerakan, jalankan axe bila tersedia.
## Jangan
Menguji detail implementasi, memakai data nyata, atau menghubungi layanan luar saat tes.
