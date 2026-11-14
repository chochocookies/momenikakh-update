---
name: event-types
description: Add or change an event type (wedding, walimah, engagement, khitan, aqiqah, birthday) through configuration. Use for EVT tasks and any copy/role/section difference by event.
---
# event-types
Sumber: `docs/05-jenis-acara.md`. Jenis acara = enum `EventType` + `config/event-types.php` (peran orang, bagian, label, salinan default, kata kunci SEO, template cocok).
- Jangan fork template; bedakan lewat konfigurasi dan bagian opsional.
- Ayat/doa tidak dipasang otomatis; pengguna yang mengaktifkan dan mengedit.
- Data anak diminimalkan (tanpa alamat rumah default; foto opsional).
- Setiap jenis punya data contoh fiktif, tes registry, dan render di semua template yang mendukungnya.
