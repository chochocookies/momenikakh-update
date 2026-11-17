---
name: database-management
description: Perubahan skema dan data Momenikah: migrasi, indeks, relasi, seeder, kinerja query. Gunakan untuk tugas yang menyentuh database/ atau model.
---
# Skill: database-management
Acuan: `docs/DATA_MODEL.md`, aturan: `.claude/rules/20-database.md`.
## Langkah
1. Migrasi baru per perubahan, dengan `down()` benar. Jangan mengedit migrasi yang sudah digabung.
2. Foreign key dan indeks untuk `slug`, `user_id`, `status`, `invitation_id`, `expires_at`. Soft delete pada undangan.
3. Jenis acara baru tidak memerlukan migrasi: pakai JSON di `invitation_sections.content` dan kolom string yang divalidasi definisi.
4. Model: `$fillable` eksplisit, `casts()` enum dan JSON, eager loading, `Model::preventLazyLoading(! app()->isProduction())`.
5. Factory dan seeder untuk semua tabel. Tes memakai `RefreshDatabase`.
6. Perubahan di produksi: cadangan dulu, migrasi tanpa mengunci tabel besar, rencana rollback tertulis.
## Larangan
`migrate:fresh` atau `db:wipe` selain lokal dan tes, membaca dump `.sql`, menyimpan data pribadi tanpa alasan.
