---
name: event-types
description: Menambah atau mengubah jenis acara (pernikahan, walimatul urs, khitanan, aqiqah, ulang tahun, tunangan) lewat EventTypeDefinition tanpa migrasi. Gunakan untuk tugas F1-04 dan F9.
---
# Skill: event-types
Acuan fitur: `docs/EVENT_TYPES.md`. Aturan: `.claude/rules/70-event-types.md`.
## Menambah jenis acara
1. Buat class di `app/EventTypes/{Nama}.php` yang mengimplementasikan `EventTypeDefinition`: `key()`, `label()`, `parties()` (peran), `events()` (jenis kegiatan), `sections()` (urutan bawaan), `fields()` (kelompok dan field), `rules()`, `defaults()` (teks pembuka, doa, penutup), `sample()`, `suggestedPalettes()`.
2. Daftarkan di `EventTypeRegistry` dan `EventType` enum.
3. Tambahkan label dan teks di berkas bahasa `lang/id`.
4. Perbarui `manifest.json` template yang mendukungnya (`event_types`) atau buat template baru.
5. Tambahkan data contoh agar pratinjau semua section terisi.
6. Tes: kontrak field (definisi ↔ form ↔ model ↔ view-model ↔ template), validasi, render pratinjau.
7. Halaman kategori SEO dan kata kunci (F9-06).
## Larangan
Migrasi khusus jenis acara, `if ($type === ...)` di controller atau template umum, dan teks keagamaan yang dipaksakan. Teks Arab atau ayat harus ditinjau penutur sebelum rilis dan selalu bisa diubah pengguna.
## Catatan per jenis (ringkas)
Walimatul urs: salam dan doa, turut mengundang, sesi tamu terpisah. Khitanan: tema anak, doa dan syukuran. Aqiqah: kartu kelahiran dan doa bayi. Ulang tahun: usia, tema pesta, wishlist, alergi pada RSVP. Tunangan: restu keluarga, adat setempat, ajakan simpan tanggal.
