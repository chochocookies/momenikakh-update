---
paths: ["database/**", "app/Domain/**/Models/**"]
---
- Migrasi aditif, ada `down()` yang berfungsi, indeks untuk kolom filter/foreign key.
- Semua kolom yang diisi form wajib ada di `$fillable`/DTO dan tercakup tes round-trip.
- Data bebas disimpan di JSON hanya bila dicast ke DTO bertipe; selainnya kolom.
- Uang dalam integer rupiah. Waktu disimpan UTC, ditampilkan `Asia/Jakarta` (atau zona acara).
