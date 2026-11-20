# Model data

Prinsip: skema generik, perilaku spesifik jenis acara dipegang `EventTypeDefinition`. Jenis acara baru tidak memerlukan migrasi. Sumber kebenaran: `database/migrations`.

| Tabel | Kolom utama | Catatan |
|---|---|---|
| `users` | id, name, email (unik), password, role (`user`/`admin`), phone, email_verified_at, two_factor_* | role sederhana; `passkeys` dari starter kit |
| `plans` | id, key (unik), name, tier (`free`/`premium`), price_idr, duration_days, features (JSON), limits (JSON), is_active, sort | DEC-01: `free` (sample) dan `paket-template` (Rp 50.000, tanpa masa kedaluwarsa); diisi `PlanSeeder` dari `config/entitlements.php` |
| `subscriptions` | id, user_id, plan_id, invitation_id (null = tingkat akun), status, starts_at, ends_at | satu paket menempel pada satu undangan |
| `payments` | id, user_id, plan_id, subscription_id, order_number (unik), provider, provider_reference (unik), amount_idr, status, payload (JSON), paid_at | idempoten per `order_number`; jumlah dari server |
| `invitations` | id, user_id, event_type, template_slug, slug (unik), title, status, locale, timezone, content (JSON), settings (JSON: palette, motion, music, rsvp_enabled, wishes_enabled, gifts_enabled), published_at, expires_at, soft deletes | indeks: slug, (user_id, status), (status, expires_at), event_type |
| `invitation_people` | id, invitation_id, role, name, nickname, parents_text, bio, birth_date, instagram, sort | role dari definisi (groom, bride, baby, child, celebrant, parent, host) |
| `invitation_events` | id, invitation_id, kind, label, starts_at (UTC), ends_at, timezone (null = zona undangan), venue_name, address, maps_url, latitude, longitude, dress_code, notes, sort | kind: akad, reception, walimah, khitan, aqiqah, party, engagement, dst. |
| `invitation_story_items` | id, invitation_id, title, body, happened_on, sort | |
| `invitation_gifts` | id, invitation_id, type (`bank`/`ewallet`/`address`), provider, holder_name, number, address_text, is_active, sort | QR lewat koleksi media `qr` |
| `guests` | id, invitation_id, name, group, phone, token (unik), opened_at | tautan `?to=` dan `&g=token` |
| `rsvps` | id, invitation_id, guest_id (null), name, attendance (`yes`/`no`/`maybe`), pax, note, ip_hash | throttle + Turnstile (PUB-09); IP hanya berupa hash |
| `guest_messages` | id, invitation_id, guest_id (null), name, message, is_visible | moderasi oleh pemilik (PUB-11) |
| `invitation_daily_stats` | id, invitation_id, date, views, unique_visitors, opens | unik per (invitation_id, date) |
| `media` | tabel spatie/medialibrary | koleksi: cover, gallery, music, photo (orang), qr (amplop), audio (katalog) |
| `music_tracks` | id, title, artist, license, license_url, requires_attribution, attribution_text, is_active, verified_at, sort | katalog berlisensi (DEC-06), mati secara bawaan |
| `audit_logs` | id, user_id (null = sistem), action, subject_type, subject_id, invitation_id, context (JSON, daftar putih), created_at | publish, hapus, moderasi, bayar; tanpa nama, email, telepon, atau IP |

## Direncanakan (belum ada migrasi)
`orders` dan `vouchers` (BIL-02, BIL-05 bila perlu), `posts` (blog, MKT-06), `invitation_sections` bila bagian perlu urutan per undangan (sekarang urutan dari manifest template dan `EventTypeDefinition`).

## Aturan
- `slug` undangan: nama + akhiran acak 4 karakter (contoh `raka-sinta-x7k2`), unik, ada daftar reserved (`config/reserved-slugs.php`).
- Status undangan: `draft → published → expired | archived`. Hanya `published` yang dapat dibuka tamu, dan hanya undangan dengan Paket Template yang dapat diterbitkan (DEC-01).
- Penghapusan: undangan kedaluwarsa disembunyikan, data tamu dihapus setelah masa tenggang (ditetapkan di DOM-14).
- Waktu acara disimpan UTC dan ditampilkan di zona acara (WIB, WITA, WIT).
- Kolom yang dibaca template selalu melewati `InvitationViewModel`. Tes kontrak (QA-06) membandingkan field definisi ↔ form ↔ model ↔ view-model.
