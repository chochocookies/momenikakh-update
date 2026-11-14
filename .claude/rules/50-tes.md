---
paths: ["tests/**", "app/**"]
---
- Pest. Tes gagal dulu, lalu implementasi. Setiap kolom baru: tes round-trip form → model → view-model → HTML.
- Setiap Policy punya tes izin dan tolak. Setiap webhook punya tes tanda tangan salah, duplikat, jumlah tidak cocok.
- Tes arsitektur (Pest arch): `Domain` tidak mengimpor `Http`; template tidak memanggil Eloquent.
- Jangan memakai data nyata; factory dan seeder fiktif.
