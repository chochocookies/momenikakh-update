# Produk Momenikah

## Ringkasan
Platform undangan digital Indonesia. Pemilik acara memilih template, mengisi data, lalu membagikan satu tautan lewat WhatsApp. Tamu membuka undangan di ponsel tanpa aplikasi: sampul amplop, musik, kisah, acara, galeri, amplop digital, RSVP, dan ucapan.

## Referensi pasar (diamati dari linkundangan.com, hanya sebagai pembelajaran)
- Tumpukan teknologi terbaca: Laravel (cookie `*_session` dan `XSRF-TOKEN`), LiteSpeed, Hostinger, GA4, jQuery + Bootstrap 5 di halaman undangan.
- Pola produk: undangan di subdomain terpisah (`undangan.`), pratinjau template terpisah (`/inv-preview/{kode}`, noindex), nama tamu lewat `?to=`, data undangan dimuat lewat JSON, galeri PhotoSwipe, salin rekening dengan toast, buku tamu dan RSVP, jumlah RSVP, pengunjung langsung lewat WebSocket, banyak jenis acara, kustom CSS/JS/HTML, reseller dan hapus brand, kustom domain.
- Pembeda mereka ada di fitur, katalog, dan model bisnis, bukan di teknologi. Kita tidak menyalin aset, template, atau teks mereka.
- Angka klaim mereka (jumlah pengguna dan undangan) tidak diverifikasi dan tidak dipakai.

## Persona
| Persona | Tujuan | Rasa sakit | Fitur kunci |
|---|---|---|---|
| Calon pengantin (25–35 th) | Undangan indah, cepat, murah, mudah dibagikan | Desainer mahal, template jelek di ponsel, data tamu berantakan | Wizard, pratinjau langsung, `?to=`, daftar tamu |
| Orang tua / keluarga | Undangan sopan, ada doa, bisa dibaca semua umur | Teks kecil, animasi berlebihan | Template klasik/islami, teks besar |
| Tamu undangan | Membuka cepat, tahu kapan dan di mana, konfirmasi hadir | Link berat, halaman patah-patah | Halaman ringan, peta, kalender, RSVP |
| Penyelenggara jasa (WO, fotografer) | Membuatkan banyak undangan | Kerja berulang | Duplikasi, reseller (nanti) |
| Admin Momenikah | Mengelola pengguna, template, pembayaran | Moderasi manual | Panel Filament |

## Perjalanan utama
Landing → lihat template → daftar → pilih jenis acara dan template → isi data (autosave, pratinjau) → publish → salin tautan atau kirim per tamu lewat WhatsApp → pantau RSVP, ucapan, kunjungan → undangan kedaluwarsa sesuai paket.

## Ruang lingkup
**MVP (Fase 0–8):** pernikahan; 6 template; wizard; daftar tamu dan `?to=`; RSVP; ucapan; amplop digital; galeri; musik; peta; hitung mundur; paket dan pembayaran; admin; landing dan SEO.
**Setelah MVP (Fase 9–10):** walimatul urs, khitanan, aqiqah, ulang tahun, tunangan; QR check-in; livestream; kustom domain; reseller; WhatsApp resmi.

## Metrik (ukur dulu, tetapkan target setelah ada data awal)
Aktivasi: persentase pendaftar yang mem-publish undangan. Waktu dari daftar sampai publish. Rasio tamu yang membuka sampul. Rasio RSVP per tamu terbuka. LCP dan CLS halaman undangan di ponsel. Konversi gratis → berbayar.

## Prinsip
Cepat dibuka di jaringan seluler. Indah tanpa berat. Hangat dan jelas. Tidak ada klaim yang tidak bisa dibuktikan. Data pribadi tamu diperlakukan hati-hati.
