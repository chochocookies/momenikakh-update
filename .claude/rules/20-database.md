---
paths:
  - "database/**"
  - "app/Domain/**/Models/**"
  - "app/Domain/**/Enums/**"
---
# Aturan database
- Migrasi baru untuk setiap perubahan skema. Jangan mengedit migrasi yang sudah digabung. Sediakan `down()` yang benar.
- Gunakan foreign key, indeks untuk kolom yang difilter (`slug`, `user_id`, `status`, `invitation_id`), dan `softDeletes` pada undangan.
- Jenis acara baru tidak boleh memerlukan migrasi. Data spesifik jenis acara masuk ke `invitations.content` (JSON) atau `invitation_people.role` / `invitation_events.kind` yang divalidasi oleh `EventTypeDefinition`.
- Eloquent: `$fillable` eksplisit, `casts()` untuk enum dan JSON, eager loading (`with`) untuk mencegah N+1, `preventLazyLoading` aktif di non-produksi.
- Jangan menjalankan `migrate:fresh` atau `db:wipe` di lingkungan selain lokal dan tes. Jangan membaca dump `.sql`.
- Data pribadi (nama, telepon tamu) hanya disimpan seperlunya, dengan rencana penghapusan saat undangan kedaluwarsa (lihat DOM-14).
