# Memasang paket ini (DS tests + DOM-01..DOM-11)

Paket ini ditimpa di root repo `momenikah`. Semua berkas baru; tidak ada yang menimpa berkas Anda kecuali yang disebut di langkah 3-6.

## 1. Salin
```bash
unzip momenikah-ds-dom-pack.zip -d /tmp/pack
rsync -a --ignore-existing /tmp/pack/momenikah-ds-dom-pack/ ./     # tidak menimpa berkas yang sudah ada
```

## 2. Pest harus memuat TestCase dan RefreshDatabase untuk folder Feature
`tests/Pest.php`:
```php
pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');
```
Tes DOM tidak memanggil `uses()` sendiri, jadi baris ini wajib. Gunakan SQLite memori di `phpunit.xml`

## 3. Daftarkan provider (`bootstrap/providers.php`)
```php
App\Providers\DomainServiceProvider::class,
```

## 4. Tambahkan trait ke `app/Models/User.php`
```php
use App\Domain\Account\Concerns\OwnsInvitations;
// di dalam class:
use OwnsInvitations;      // juga mendaftarkan cast role -> UserRole otomatis
```
Pastikan `role` TIDAK ada di `$fillable` User.

## 5. Jadwal kedaluwarsa (`routes/console.php`)
Salin isi `routes/console.php.snippet` ke `routes/console.php`.

## 6. Seeder
`database/seeders/DatabaseSeeder.php.snippet` menunjukkan dua baris untuk `run()`.

## 7. Playwright
```bash
npm i -D @playwright/test @axe-core/playwright
npx playwright install --with-deps chromium
```
Tambahkan ke `package.json`: `"scripts": { "e2e": "playwright test" }`. `E2E_BASE_URL` harus menunjuk host yang melayani `/styleguide`.

## 8. Jalankan
```bash
php artisan migrate:fresh                         # hanya lokal
php artisan test --filter=Domain                  # DOM-01..DOM-11
php artisan test --filter=DesignSystem            # DS-03..DS-07 (sisi PHP)
php artisan test --testsuite=Arch                 # atau ./vendor/bin/pest tests/Arch
npm run build && npm run e2e                      # DS-07 (axe) dan perilaku komponen
php artisan db:seed --class=DemoSeeder            # DOM-11
```
Tambahkan ke `composer.json`: `"migrate:check": ["@php artisan migrate --force", "@php artisan migrate:rollback --force", "@php artisan migrate --force"]` untuk bukti "migrate + rollback bersih" di CI (di basis data uji, bukan produksi).
