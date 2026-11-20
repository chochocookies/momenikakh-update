---
description: Menambah jenis acara baru (contoh: /new-event-type aqiqah)
---
Kunci jenis acara: $ARGUMENTS. Gunakan skill `event-types` dan `docs/EVENT_TYPES.md`.
Pastikan tugas `EVT-01…05` yang terkait siap (`task.mjs show`) dan keputusan `DEC-11` (urutan jenis acara, D-13) sudah diputuskan. Tambahkan case di enum `EventType` dan entri di `config/event-types.php`, tambahkan teks bawaan dan data contoh (`SamplePayload`), perbarui `manifest.json` template yang mendukung, tulis tes kontrak, dan siapkan halaman kategori SEO. Tanpa migrasi.
