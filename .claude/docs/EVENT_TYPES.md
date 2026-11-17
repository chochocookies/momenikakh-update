# Jenis acara dan fitur

Fitur di bawah adalah **spesifikasi fitur**. Fase 9 (backlog) yang mengimplementasikannya. Fondasi (registry, section, peran, kegiatan) dibangun sejak Fase 1 supaya penambahan tidak memerlukan penulisan ulang.

## Fitur bersama semua jenis acara
Sampul amplop dan musik, nama tamu lewat `?to=`, hitung mundur, kegiatan lebih dari satu (kalender `.ics`), peta, galeri, amplop digital (rekening, e-wallet, alamat kado), RSVP dan jumlah tamu, ucapan dan doa, tombol bagikan, palet warna, statistik, kedaluwarsa sesuai paket.

## Pernikahan (`wedding`), dibangun lebih dulu
- Peran: mempelai pria, mempelai wanita, orang tua kedua pihak.
- Kegiatan: akad nikah, resepsi (boleh lebih dari satu, misalnya dua kota), unduh mantu.
- Section: pembuka, mempelai, kisah cinta (garis waktu), acara, hitung mundur, galeri, amplop digital, RSVP, ucapan, penutup. Opsional: protokol kesehatan, dress code, livestream.
- Teks bawaan: pembuka formal dan santai, ayat atau kutipan opsional, penutup. Semua dapat diubah.

## Walimatul Urs (`walimatul_urs`)
- Fokus: undangan resepsi pernikahan bernuansa Islami.
- Peran: mempelai, wali atau orang tua, keluarga yang mengundang (turut mengundang).
- Kegiatan: walimah, akad (opsional), dengan pemisahan tamu pria dan wanita bila perlu (jam atau lokasi berbeda).
- Fitur khas: pembuka salam dan doa (pilihan teks Arab dengan terjemahan), ucapan "turut mengundang", pemisahan sesi tamu, catatan adab hadir, ornamen geometri Islami, tanpa foto wajah sebagai opsi (mode siluet atau ilustrasi).
- Catatan: teks Arab dan ayat harus ditinjau penutur atau ustaz sebelum rilis.

## Khitanan (`khitanan`)
- Peran: anak yang dikhitan (nama, nama panggilan, orang tua), keluarga besar.
- Kegiatan: khitanan (syukuran), pengajian atau doa bersama, makan bersama.
- Fitur khas: tema ramah anak (warna cerah, ilustrasi), teks doa dan syukuran, kolom "turut berbahagia", galeri foto anak, RSVP sederhana, amplop digital opsional.

## Aqiqah (`aqiqah`)
- Peran: bayi (nama, tanggal lahir, urutan anak), orang tua, kakak atau keluarga.
- Kegiatan: aqiqah dan pemberian nama, syukuran.
- Fitur khas: kartu kelahiran (nama lengkap, tanggal, berat dan panjang opsional), doa untuk bayi, galeri bayi, teks "mohon doa restu", tema pastel, tanpa amplop digital sebagai bawaan (bisa diaktifkan).

## Ulang Tahun (`birthday`)
- Peran: yang berulang tahun (usia), tuan rumah, pengisi acara (opsional).
- Kegiatan: pesta, tiup lilin, acara tambahan.
- Fitur khas: hitung mundur menuju pesta, tema (anak, remaja, dewasa, ulang tahun emas), dress code atau tema pesta, daftar permintaan hadiah (wishlist) dengan tautan, RSVP dengan jumlah tamu dan alergi makanan, galeri "perjalanan usia".

## Tunangan (`engagement`)
- Peran: calon mempelai pria dan wanita, keluarga kedua pihak.
- Kegiatan: lamaran atau tunangan, ramah tamah, makan bersama.
- Fitur khas: sambutan "mohon doa restu", cerita singkat perkenalan, penjelasan adat setempat (kolom teks bebas), tautan ke undangan pernikahan menyusul (CTA "simpan tanggal"), tanpa amplop digital sebagai bawaan.

## Mekanisme penambahan
Setiap jenis acara adalah satu class di `app/EventTypes/`. Rinciannya: peran, kegiatan, section dan urutan, field, aturan validasi, teks bawaan, data contoh, palet saran. Template menyatakan `event_types` yang didukungnya. Tugas F9-xx per jenis acara membuat class, teks bawaan, dua template, dan tes.

## Fitur lintas-acara untuk tahap lanjut (Fase 10)
QR tiket dan check-in, livestream (embed YouTube), kustom domain, WhatsApp resmi untuk pengiriman massal, pustaka musik lebih besar, reseller dan hapus brand, bantuan penulisan teks, kustom CSS/JS khusus (hanya di host `share` dan dengan sandbox).
