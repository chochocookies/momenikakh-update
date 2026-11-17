---
description: Membuat template undangan baru (contoh: /new-template soft-rose)
---
Slug: $ARGUMENTS (kebab-case). Gunakan skill `template-authoring` dan `docs/TEMPLATE_SPEC.md`.
Tanyakan hanya yang belum jelas: suasana, jenis acara yang didukung, palet warna (2 sampai 4), daftar aset ornamen yang tersedia, tier (free atau premium).
Lalu: `php artisan template:make $ARGUMENTS`, isi manifest, token, susun section dari `_shared`, tambahkan ornamen, `php artisan template:lint $ARGUMENTS`, periksa pratinjau di 360, 768, 1280 px untuk semua palet dan mode kurangi gerakan. Jangan menyentuh template lain.
