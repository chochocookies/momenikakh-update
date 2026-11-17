---
description: Menutup tugas: verifikasi, tandai selesai, catat perubahan (contoh: /finish-task F2-05)
---
ID tugas: $ARGUMENTS. Bila kosong, ambil dari cabang saat ini.
1. Jalankan `composer check` dan `npm run build`. Perbaiki sampai hijau.
2. Periksa kriteria "Selesai bila" satu per satu (`task.mjs show`), beri bukti (tes, tangkapan layar, perintah).
3. Untuk UI: periksa 360, 768, 1280 px dan mode kurangi gerakan.
4. Jalankan `/review-diff`. Pastikan tidak ada rahasia di diff.
5. Perbarui dokumen yang terdampak dan `CHANGELOG.md`.
6. `node .claude/scripts/task.mjs done $ARGUMENTS`, lalu usulkan pesan commit conventional dan deskripsi PR.
