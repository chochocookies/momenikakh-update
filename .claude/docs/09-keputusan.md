# 09 — Keputusan

Status ADR: `Diputuskan` (pemilik sudah menyatakan), `Usulan` (menunggu persetujuan pemilik; jangan diperlakukan sebagai final). `DEC-nn` = keputusan terbuka yang memblokir tugas.

## ADR
| ID | Keputusan | Status |
|---|---|---|
| ADR-001 | Bangun ulang dari nol (greenfield); versi lama hanya referensi pelajaran | Diputuskan (pemilik) |
| ADR-002 | Konteks AI hanya di `.claude/`; tanpa `AI_CONTEXT/` | Diputuskan (pemilik) |
| ADR-003 | Nada, palet, huruf merek tetap (lihat `07-merek.md`); landing page memakai GSAP, tidak statis | Diputuskan (pemilik) |
| ADR-004 | Jenis acara tambahan (walimatul urs, khitanan, aqiqah, ulang tahun, tunangan) dibuat sebagai fitur di mesin yang sama | Diputuskan (pemilik) |
| ADR-005 | Laravel 13 + PHP 8.3+, monolit modular | Usulan |
| ADR-006 | Tiga domain: `momenikah.online`, `app.`, `undangan.` dalam satu aplikasi | Usulan |
| ADR-007 | Blade + Livewire 4 + Tailwind 4 (Vite) + GSAP npm; tanpa jQuery/Bootstrap/CDN | Usulan |
| ADR-008 | Filament 5 sebagai satu-satunya admin | Usulan |
| ADR-009 | Template = folder mandiri + manifest + `InvitationViewModel`; bagian bersama di `_shared` | Usulan |
| ADR-010 | Jenis acara = konfigurasi (`EventTypeRegistry`), bukan fork template | Usulan |
| ADR-011 | Media: Spatie Media Library, konversi WebP via queue, penyimpanan S3-kompatibel + CDN | Usulan |
| ADR-012 | Tanpa CSS/JS/HTML buatan pengguna di undangan publik pada v1 | Usulan |
| ADR-013 | Logo cincin-hati dan set favicon di `brand/` | Usulan (menunggu persetujuan akhir) |
| ADR-014 | Undangan publik `noindex`; hanya situs pemasaran diindeks | Usulan |
| ADR-015 | Nama tamu diisi di klien dari `?to=` (halaman tetap bisa di-cache) | Usulan |
| ADR-016 | Pembayaran Duitku di balik `PaymentGateway`; entitlement lewat `hasActivePlan()` | Usulan |

## Keputusan terbuka
| ID | Pertanyaan | Memblokir |
|---|---|---|
| DEC-01 | Nama paket, batas (foto, template premium, masa aktif), dan harga | DOM-09, BIL-03, MKT-05 |
| DEC-02 | Hosting produksi (VPS mana), email transaksional, anggaran bulanan | FND-06, OPS-* |
| DEC-03 | Subdomain undangan final (`undangan.`) dan skema slug | PUB-01 |
| DEC-04 | Vendor penyimpanan objek + CDN (mis. Cloudflare R2) | MED-01 |
| DEC-05 | Sumber aset ilustrasi (ilustrator, lisensi, anggaran) | TPL-15 |
| DEC-06 | Katalog musik: unggah pengguna saja, atau pustaka berlisensi | MED-05 |
| DEC-07 | Bahasa: hanya Indonesia di v1? | EVT-*, TPL-* (sebagian) |
| DEC-08 | Jadwal domain kustom dan reseller | backlog LCH-03 |
| DEC-09 | Notifikasi WhatsApp: tautan manual saja atau API resmi | GST-*, backlog |
| DEC-10 | Persetujuan logo dan penamaan merek final | DS-05, LCH-02 |
