# 05 — Jenis acara (fitur)

Satu mesin: `EventType` enum + `config/event-types.php` menentukan peran orang, bagian, label, salinan default, kata kunci SEO, dan template yang cocok. Fitur di bawah dibuat sebagai konfigurasi + bagian opsional, bukan fork.

| Kunci | Nama | Peran orang | Bagian khas | Salinan/label khas |
|---|---|---|---|---|
| `wedding` | Pernikahan | mempelai pria, wanita, orang tua | kisah cinta, akad + resepsi, amplop digital | "Undangan Pernikahan", "Kami mengundang…" |
| `walimah` | Walimatul Urs | mempelai, orang tua, tuan rumah | seperti pernikahan + ayat/doa opsional, dress code, catatan tempat (mis. tempat tamu putra/putri bila ada) | "Walimatul 'Urs", salam Islami (bisa dimatikan) |
| `engagement` | Tunangan/Lamaran | calon mempelai, dua keluarga | acara lamaran/tunangan, rundown, amplop opsional | "Tunangan", "Lamaran" |
| `khitan` | Khitanan | anak, orang tua | data anak (nama, panggilan), doa, satu acara, galeri | "Khitanan", "Syukuran Khitan" |
| `aqiqah` | Aqiqah | bayi, orang tua | data kelahiran (tanggal lahir; berat/panjang opsional), arti nama, doa, satu acara | "Aqiqah", "Syukuran Kelahiran" |
| `birthday` | Ulang Tahun | perayaan (nama), penyelenggara | usia/tonggak, tema, galeri perjalanan umur, daftar keinginan (opsional), RSVP | "Ulang Tahun ke-…" |

## Fitur lintas jenis (dikontrol per jenis lewat konfigurasi)
Cover + `?to=`; hitung mundur; peta per acara; tambah ke kalender (`.ics`); amplop digital (bank/e-wallet/alamat); galeri; musik; RSVP (jumlah tamu, status); buku tamu + moderasi; tautan per tamu; statistik; bagikan WhatsApp; urutan dan pengaktifan bagian.

## Aturan konten
- Jangan memasang ayat/doa otomatis; sediakan pilihan teks yang pengguna aktifkan dan edit sendiri (sensitif secara agama).
- Label peran mengikuti jenis (bukan "mempelai" untuk aqiqah).
- Data anak (khitan, aqiqah, ulang tahun anak): minimalkan data pribadi; tanpa alamat rumah di undangan default; foto bersifat opsional.
- Jenis baru = case enum + entri konfigurasi + data contoh + tes registry (`/new-event-type`).

## Urutan peluncuran
Pernikahan lebih dulu (EVT bersama TPL), lalu walimatul urs dan tunangan (varian dekat), kemudian khitanan, aqiqah, ulang tahun (peran dan bagian berbeda).
