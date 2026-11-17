---
paths:
  - "tests/**"
  - "database/factories/**"
---
# Aturan tes
- Pest 4. Nama tes menjelaskan perilaku ("guest cannot publish another user's invitation").
- Alur yang wajib punya feature test: daftar, buat, simpan per langkah, publish, buka undangan (`?to=`), RSVP, ucapan, bayar (callback dobel), kedaluwarsa.
- Tes kontrak: setiap field di `EventTypeDefinition` harus ada di form, tersimpan, muncul di `InvitationViewModel`, dan terbaca oleh template contoh. Tes gagal bila ada field yang hilang.
- Tes keamanan: IDOR (pengguna A mengakses milik B), XSS di `?to=` dan ucapan, unggah file palsu, rate limit.
- Gunakan factory dan `RefreshDatabase`. Jangan memakai data nyata atau menghubungi layanan luar (fake HTTP untuk Duitku).
- Browser test (Pest browser): satu skenario undangan di 360 px dan satu di 1280 px.
