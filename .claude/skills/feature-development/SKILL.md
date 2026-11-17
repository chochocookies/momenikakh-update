---
name: feature-development
description: Alur membangun fitur Momenikah dari rencana, tes, implementasi, sampai verifikasi. Gunakan untuk tugas fitur apa pun di TODO.
---
# Skill: feature-development
## Alur
1. **Pahami tugas**: `task.mjs show ID`, baca kriteria "Selesai bila" dan dependensi. Masuk Plan Mode.
2. **Rencana singkat**: berkas yang disentuh, model data, rute, komponen, tes, dampak ke dokumen. Cari kode yang sudah ada sebelum menulis baru (komponen UI, service, registry).
3. **Kontrak dulu**: bila menyentuh data undangan, mulai dari `EventTypeDefinition` lalu form, model, `InvitationViewModel`, template. Satu perubahan di satu lapisan wajib tercermin di lapisan lain (tes kontrak F8-02).
4. **Tes dulu atau bersamaan**: feature test untuk perilaku, unit test untuk aturan. Tes keamanan bila ada input pengguna.
5. **Implementasi kecil**: Form Request untuk validasi, Policy untuk akses, service untuk logika, controller tipis, komponen Blade untuk UI.
6. **Verifikasi**: `composer check`, `npm run build`, periksa UI di 360/768/1280 px dan mode kurangi gerakan.
7. **Tutup**: `/review-diff`, `/finish-task`.
## Daftar periksa
- Tidak ada Eloquent atau `request()` di template. Output di-escape.
- Tidak ada warna, font, atau ukuran hardcode. Teks pengguna dalam bahasa Indonesia.
- Batas paket lewat `PlanGate`, bukan `if` tersebar.
- Tidak menambah dependensi tanpa alasan tertulis (lisensi, ukuran, pemeliharaan).
- Tidak ada klaim palsu di UI. Keadaan kosong dan galat ditangani dengan pesan sopan.
