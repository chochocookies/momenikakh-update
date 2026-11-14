---
description: Buat template undangan baru dari kerangka
argument-hint: <slug-template>
---
Muat skill `template-authoring` dan `gsap-motion`. Jalankan `php artisan make:template $ARGUMENTS`, isi `manifest.php` (jenis acara yang didukung, bagian, palet, tingkat gerak), tulis `theme.css` dan `theme.js` hanya berupa token + animasi, dan override bagian hanya bila perlu. Jalankan `php artisan templates:audit` dan tes render untuk setiap jenis acara yang didukung.
