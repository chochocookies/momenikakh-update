---
name: code-quality
description: Keep code consistent (Pint, Larastan, arch tests, dead code, duplicated templates). Use before finishing any task or when refactoring.
---
# code-quality
- `./vendor/bin/pint --test`, `./vendor/bin/phpstan`, `php artisan test --testsuite=Arch`.
- Pencarian cepat: `rg "style=\"" resources` (inline), `rg "cdn\." resources` (CDN), `rg "{!!" resources` (HTML mentah), `rg "DB::|Model::" resources/templates` (template menyentuh data).
- Duplikasi antar-template harus naik ke `_shared`. Fungsi > 40 baris dipecah. Nama jelas, tipe eksplisit.
- Hapus kode mati; jangan menyisakan fitur setengah jadi di rute.
