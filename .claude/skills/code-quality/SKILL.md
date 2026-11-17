---
name: code-quality
description: Standar kode Momenikah: gaya, struktur, tipe, penamaan, dan refaktor aman. Gunakan untuk review dan saat menulis kode.
---
# Skill: code-quality
- Pint untuk gaya, Larastan untuk analisis statis (naikkan level bertahap), `declare(strict_types=1)` pada kode baru.
- Controller tipis: validasi di Form Request, akses di Policy, logika di service, tampilan di komponen.
- Nama jelas dalam bahasa Inggris. Satu class satu tanggung jawab. Hindari trait "serbaguna" dan helper global.
- Enum untuk nilai tetap (status, peran, jenis acara). DTO atau view-model untuk data lintas lapisan.
- Hindari duplikasi: cari komponen atau service yang ada sebelum menulis baru. Tiga duplikasi berarti saatnya diekstrak.
- Refaktor hanya dengan tes hijau dan di PR terpisah dari fitur.
- Komentar menjelaskan mengapa, bukan apa. Tidak ada kode mati atau `dd()` tersisa.
- Dependensi baru butuh alasan, lisensi, ukuran, dan kondisi pemeliharaan.
