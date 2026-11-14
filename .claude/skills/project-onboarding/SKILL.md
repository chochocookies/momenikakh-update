---
name: project-onboarding
description: Orient before any task in Momenikah (repo state, TODO row, dependencies, decisions). Use at the start of every session or when the task is unclear.
---
# project-onboarding
1. `git status`, `git log --oneline -10`; catat cabang dan perubahan belum commit.
2. Baca `.claude/CLAUDE.md`, `docs/00-produk.md`, `docs/09-keputusan.md`.
3. Temukan baris tugas di `.claude/TODO.md`; cek `Dep` dan `DEC-nn`. Jika ada yang terbuka, berhenti dan tanya.
4. Jalankan `php artisan route:list --except-vendor` dan `ls resources/templates` bila tugas menyentuh rute atau template.
5. Hasilkan catatan 5 baris: ID, dependensi, risiko, rencana, keputusan dibutuhkan. Jangan edit file di tahap ini.
