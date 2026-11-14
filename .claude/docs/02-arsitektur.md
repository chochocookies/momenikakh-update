# 02 — Arsitektur

## Tiga permukaan, satu aplikasi
| Permukaan | Domain | Sesi/cookie | Indeks | Isi |
|---|---|---|---|---|
| Pemasaran | `momenikah.online` | tidak (kecuali consent analitik) | ya | landing, katalog, jenis acara, blog, FAQ |
| Aplikasi | `app.momenikah.online` | ya | tidak | dasbor, editor, tagihan, `/admin` Filament |
| Undangan | `undangan.momenikah.online` | **tidak** | tidak | `/{slug}`, `/preview/{template}` |
Alasan: memisahkan cookie dan isolasi keamanan, lonjakan tamu tidak mengganggu dasbor, caching agresif. Rute dipisah di `routes/marketing.php`, `routes/app.php`, `routes/public.php` dengan `Route::domain()`.

## Modular monolith
`app/Domain/<Konteks>` berisi Models, Actions, DTO, Policies, Enums, Events. `app/Http` dan `app/Livewire` hanya orkestrasi. Pest arch test menjaga arah dependensi: `Domain` ↛ `Http`/`Livewire`/`Filament`.

## Model data (inti)
| Tabel | Kolom penting |
|---|---|
| `users` | nama, email, password, `is_admin` |
| `invitations` | `user_id`, `event_type`, `template_slug`, `slug` (unik), `title`, `status` (draft/published/expired/archived), `published_at`, `expires_at`, `locale`, `theme_overrides` (JSON ber-DTO), `sections` (JSON urutan+aktif), `music_media_id` |
| `invitation_people` | `invitation_id`, `role` (groom, bride, child, celebrant, parent, host…), nama, panggilan, urutan, `photo_media_id`, teks keluarga |
| `invitation_events` | `invitation_id`, `kind` (akad, resepsi, lamaran, khitan, aqiqah, pesta…), mulai/selesai, `timezone`, nama tempat, alamat, lat/lng, `map_url`, catatan |
| `invitation_story_items` | tanggal, judul, isi, foto |
| `invitation_gifts` | `type` (bank, ewallet, alamat), penyedia, nomor, nama pemilik |
| `guests` | `invitation_id`, nama, kode unik, grup, `invited_at`, `opened_at` |
| `rsvps` | `invitation_id`, `guest_id?`, nama, status (hadir/tidak/ragu), jumlah, catatan |
| `guest_messages` | `invitation_id`, nama, pesan, `is_hidden` |
| `invitation_daily_stats` | `invitation_id`, tanggal, tampilan unik |
| `plans`, `subscriptions`, `payments` | paket, masa aktif, `gateway_ref`, jumlah (int rupiah), status, payload mentah |
| `media` | tabel Spatie |
Aturan: setiap kolom yang diedit form ada di DTO + `$fillable` + tes round-trip.

## Alur data undangan
`Invitation (Eloquent)` → `InvitationViewModelBuilder` → `InvitationViewModel` (DTO tak bisa diubah, hanya data siap tampil) → `templates::<slug>.index` → HTML. HTML yang sama disimpan di cache Redis (kunci: id + `updated_at` + versi template). Nama tamu **tidak** dirender server: JS mengisi `?to=` lewat `textContent`, sehingga halaman tetap bisa di-cache dan aman XSS.

## Keamanan
CSP per permukaan; HSTS; cookie `Secure/HttpOnly/SameSite=Lax`; throttle login dan endpoint publik; Turnstile pada RSVP/buku tamu/registrasi; Policy di semua aksi tulis; unggahan divalidasi dan di-strip EXIF; webhook diverifikasi; log tidak memuat data pribadi; ekspor/hapus data pengguna (UU PDP) di dasbor; cadangan terenkripsi diuji pulih. Kustom CSS/JS pengguna ditolak sampai ada ADR + sandbox.

## Performa
Cache halaman undangan; aset immutable berhash di CDN; WebP/`srcset`; musik dimuat setelah interaksi; font self-host subset; GSAP hanya dimuat pada halaman yang memakainya; anggaran: undangan ≤ 400 KB awal (tanpa galeri/musik), landing JS ≤ 120 KB gzip.

## Observabilitas
Sentry (error), Pulse (queue, slow query), uptime check untuk tiga domain, alarm antrean menumpuk, log terstruktur tanpa PII.
