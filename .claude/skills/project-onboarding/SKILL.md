---
name: project-onboarding
description: Orientasi proyek Momenikah: urutan baca dokumen, perintah penting, peta folder, dan cara memilih tugas. Gunakan di awal sesi atau saat konteks hilang.
---
# Skill: project-onboarding
## Kapan dipakai
Awal sesi baru, setelah konteks terpotong, atau saat Anda tidak yakin harus mulai dari mana.
## Langkah
1. Baca `.claude/CLAUDE.md` (aturan emas). Jangan melompatinya.
2. `node .claude/scripts/task.mjs status` lalu `next 5` untuk melihat posisi proyek.
3. Baca dokumen yang relevan saja: struktur → `ARCHITECTURE.md`; template → `TEMPLATE_SPEC.md`; data → `DATA_MODEL.md`; SEO → `SEO.md`; brand → `BRAND.md`; keputusan → `DECISIONS.md`.
4. Pastikan keputusan yang menyentuh tugas bukan `Open` atau `Proposed`. Bila ya, tanyakan pemilik sebelum membangun.
5. Periksa `git status` dan `git log -5` agar tidak menimpa pekerjaan yang sedang berjalan.
## Perintah penting
`composer check` (Pint + Larastan + Pest), `npm run build`, `php artisan template:lint {slug}`, `php artisan route:list`, `node .claude/scripts/task.mjs ...`.
## Hasil
Ringkasan satu paragraf: posisi proyek, tugas berikutnya, dan keputusan yang menghalangi (bila ada).
