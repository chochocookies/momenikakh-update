---
name: event-types
description: Menambah atau mengubah jenis acara (pernikahan, walimatul urs, khitanan, aqiqah, ulang tahun, tunangan) lewat EventTypeDefinition tanpa migrasi. Gunakan untuk tugas DOM-01 dan EVT-01…05.
---
# Skill: event-types
Acuan fitur: `docs/EVENT_TYPES.md`. Aturan: `.claude/rules/70-event-types.md`.
## Menambah jenis acara
1. Tambahkan satu case di enum `App\Domain\Invitation\Enums\EventType` dan satu entri di `config/event-types.php`: `label`, `description`, `roles` (min dan maks), `events` (jumlah dan jenis kegiatan), `sections` (urutan bawaan), `content` (kunci teks dan panjang maksimum), `features`, `defaults` (teks pembuka, doa, penutup).
2. `EventTypeRegistry` membaca config itu dan gagal saat boot bila ada jenis acara tanpa entri. Tambahkan data contoh di `SamplePayload` dan label di `Labels`.
3. Tambahkan label dan teks di berkas bahasa `lang/id`.
4. Perbarui `manifest.json` template yang mendukungnya (`event_types`) atau buat template baru.
5. Tambahkan data contoh agar pratinjau semua section terisi.
6. Tes: kontrak field (definisi ↔ form ↔ model ↔ view-model ↔ template), validasi, render pratinjau.
7. Halaman kategori SEO dan kata kunci (EVT-07).
## Larangan
Migrasi khusus jenis acara, `if ($type === ...)` di controller atau template umum, dan teks keagamaan yang dipaksakan. Teks Arab atau ayat harus ditinjau penutur sebelum rilis dan selalu bisa diubah pengguna.
## Catatan per jenis (ringkas)
Walimatul urs: salam dan doa, turut mengundang, sesi tamu terpisah. Khitanan: tema anak, doa dan syukuran. Aqiqah: kartu kelahiran dan doa bayi. Ulang tahun: usia, tema pesta, wishlist, alergi pada RSVP. Tunangan: restu keluarga, adat setempat, ajakan simpan tanggal.
