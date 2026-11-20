---
paths:
  - "app/Domain/Invitation/EventTypes/**"
  - "config/event-types.php"
  - "resources/templates/_shared/sections/**"
---
# Aturan jenis acara
- Setiap jenis acara adalah satu entri di `config/event-types.php` yang dibaca `EventTypeRegistry` menjadi `EventTypeDefinition`. Isinya: kunci, label, peran pihak (`parties`), jenis kegiatan (`events`), daftar section dan urutan bawaannya, skema field, aturan validasi, teks bawaan (pembuka, doa, penutup), dan data contoh.
- Menambah jenis acara = menambah satu case enum dan satu entri config + (opsional) template + terjemahan label. Tanpa migrasi dan tanpa mengubah controller.
- Template menyatakan jenis acara yang didukung di `manifest.json` (`event_types`). Katalog hanya menampilkan template yang cocok.
- Teks keagamaan dan adat (doa, ayat, ucapan) harus disediakan sebagai pilihan yang bisa diubah, bukan dipaksakan, dan ditinjau oleh orang yang paham konteksnya sebelum rilis.
