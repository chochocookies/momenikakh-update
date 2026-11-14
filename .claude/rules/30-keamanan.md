---
paths: ["app/Http/**", "app/Livewire/**", "routes/**", "app/Domain/Billing/**", "app/Domain/Media/**"]
---
- Setiap aksi tulis diotorisasi lewat Policy; ambil data lewat scope kepemilikan (cegah IDOR).
- Input lewat Form Request/DTO; whitelist field; sanitasi teks bebas; keluaran Blade memakai `{{ }}`. `{!! !!}` hanya untuk SVG milik repo.
- Endpoint publik (RSVP, buku tamu, login): throttle + honeypot + Turnstile.
- Unggahan: cek mime asli, ukuran, dimensi; hapus EXIF; nama acak; jangan percaya nama klien.
- Webhook pembayaran: verifikasi signature, idempoten, bandingkan jumlah dari server.
- Domain `undangan.` tanpa cookie sesi/CSRF; CSP ketat; tanpa skrip pihak ketiga kecuali peta dan analitik yang disetujui.
