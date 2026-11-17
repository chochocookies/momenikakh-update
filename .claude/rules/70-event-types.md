---
paths:
  - "app/EventTypes/**"
  - "config/events.php"
  - "resources/templates/_shared/sections/**"
---
# Aturan jenis acara
- Setiap jenis acara adalah satu class `EventTypeDefinition` di `app/EventTypes/`. Isinya: kunci, label, peran pihak (`parties`), jenis kegiatan (`events`), daftar section dan urutan bawaannya, skema field, aturan validasi, teks bawaan (pembuka, doa, penutup), dan data contoh.
- Menambah jenis acara = menambah satu class + (opsional) template + terjemahan label. Tanpa migrasi dan tanpa mengubah controller.
- Template menyatakan jenis acara yang didukung di `manifest.json` (`event_types`). Katalog hanya menampilkan template yang cocok.
- Teks keagamaan dan adat (doa, ayat, ucapan) harus disediakan sebagai pilihan yang bisa diubah, bukan dipaksakan, dan ditinjau oleh orang yang paham konteksnya sebelum rilis.
