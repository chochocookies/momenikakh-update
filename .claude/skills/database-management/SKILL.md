---
name: database-management
description: Create or change schema safely (additive migrations, indexes, factories, seeders). Use for any migration or model change.
---
# database-management
- Migrasi aditif dengan `down()` yang bekerja; satu tujuan per migrasi. Indeks untuk kolom filter dan foreign key; `constrained()->cascadeOnDelete()` hanya bila benar.
- Uang = integer rupiah; waktu UTC; enum lewat `BackedEnum` + cast.
- Setiap kolom baru: `$fillable`/DTO + factory + tes round-trip.
- Jangan pernah `migrate:fresh`/`db:wipe` di luar basis data lokal sekali pakai. Tes memakai `RefreshDatabase`.
