# Perbaikan GST dan MED (momenikah-update)

Salin isi zip ini ke root repo (struktur folder sudah sama), lalu hapus berkas di bagian "Hapus".

## Hapus
- `app/Console/Kernel.php`  (Laravel 11+ tidak memuatnya; jadwal di dalamnya tidak pernah jalan. Jadwalnya sudah dipindah ke `routes/console.php`.)
- Opsional, sisa perintah yang salah di Windows (cek isinya dulu): `$null`, `echo/`, `appDomain*`, `appFilament*`, `appLivewire*` di root repo.

## Ubah (berkas ada di zip)
| Berkas | Perubahan |
|---|---|
| `app/Http/Controllers/Controller.php` | tambah trait `AuthorizesRequests` (semua controller GST/MED memanggil `$this->authorize()`) |
| `bootstrap/providers.php` | daftarkan `GstMediaServiceProvider` |
| `bootstrap/app.php` | pengecualian CSRF `*/opened` untuk beacon "sudah dibuka" |
| `routes/app.php` | rute tamu (GST-01..03) dan media (MED-01..05) |
| `routes/public.php` | rute beacon `POST {slug}/opened` |
| `routes/console.php` | jadwal `media:prune-orphans` dan `CleanupOldBackups` |
| `routes/marketing.php` | rute `/styleguide` |
| `app/Domain/Invitation/Models/Invitation.php`, `InvitationPerson.php`, `InvitationGift.php` | `implements HasMedia` + trait koleksi/konversi (Invitation.php juga dirapikan Pint) |
| `app/Domain/Media/Concerns/RegistersInvitationMedia.php`, `RegistersPersonMedia.php` | `performOnCollections()` dipindah ke awal rantai (perilaku sama, lolos Larastan) |
| `config/filesystems.php` | disk `media` |
| `config/media-library.php` | `disk_name`, `max_file_size`, `queue_name`, `path_generator`, `default_loading_attribute_value` |
| `database/migrations/2026_11_17_073651_create_media_table.php` | tambah `down()` (tanpa ini migrate, rollback, migrate gagal: tabel `media` sudah ada) |
| `app/Domain/Guest/Services/GuestCsvParser.php` | pemeriksaan byte NUL dipindah ke setelah dekode (CSV UTF-16 sebelumnya ditolak) |
| `resources/js/app.js` | impor `./ui-copy` (tombol Salin) |
| `resources/js/public.js` (baru) + `vite.config.js` + `layouts/public.blade.php` | entri JS khusus halaman publik (nama tamu, musik, beacon) |
| `resources/views/dashboard/guests/index.blade.php` | `tone="brand"` (tidak ada) menjadi `tone="rose"` |
| `phpunit.xml` | SQLite memori (sebelumnya MySQL), disk media lokal, `PUBLIC_BASE_URL` |
| `.env.example` | `PUBLIC_BASE_URL` dan `MEDIA_*` |

## Tes baru (berkas baru, tidak menimpa tes Anda)
`tests/Feature/Wiring/{GstWiringTest,MedWiringTest,SurfaceWiringTest}.php` dan `tests/Support/WiringFixtures.php`.
Butuh `tests/Pest.php` seperti di INSTALL.md (`Tests\TestCase` + `RefreshDatabase` untuk folder `Feature`).
Jalankan: `php artisan test tests/Feature/Wiring`

## Setelah menyalin
```bash
npm run build
php artisan migrate        # tidak ada migrasi baru; media sudah ada
php artisan storage:link   # bila MEDIA_DRIVER tidak diisi (disk media lokal)
```
Worker antrian: `php artisan queue:work --queue=media,default`
