# Model data

Prinsip: skema generik, perilaku spesifik jenis acara dipegang `EventTypeDefinition`. Jenis acara baru tidak memerlukan migrasi.

| Tabel | Kolom utama | Catatan |
|---|---|---|
| `users` | id, name, email (unik), password, role (`user`/`admin`), phone, email_verified_at, two_factor_* | role sederhana, tanpa paket permission |
| `plans` | id, key (unik), name, tier (`free`/`premium`), price_idr, duration_days, limits (JSON: foto, tamu, musik), is_active | harga diisi setelah D-02 |
| `orders` | id, number (unik), user_id, plan_id, invitation_id, amount_idr, status, voucher_id | idempoten per `number` |
| `payments` | id, order_id, provider, provider_ref (unik), status, raw (JSON), paid_at | simpan payload callback |
| `invitations` | id, user_id, event_type, template_slug, slug (unik), title, status, locale, timezone, settings (JSON: palette, motion, music), published_at, expires_at, view_count, soft deletes | indeks: slug, user_id, status, expires_at |
| `invitation_parties` | id, invitation_id, role, name, nickname, parents_text, bio, instagram, sort | role dari definisi (groom, bride, child, celebrant, parent, host) |
| `invitation_events` | id, invitation_id, kind, label, starts_at, ends_at, venue_name, address, maps_url, lat, lng, dress_code, notes, sort | kind: akad, resepsi, walimah, khitan, aqiqah, pesta, lamaran, dll. |
| `invitation_sections` | id, invitation_id, key, enabled, sort, content (JSON) | pembuka, cerita, doa, penutup, pengaturan RSVP |
| `media` | tabel spatie/medialibrary | koleksi: cover, gallery, party, music, gift_qr, og |
| `guests` | id, invitation_id, name, group, phone, token (unik), opened_at, created_at | tautan `?to=` dan `&g=token` |
| `rsvps` | id, invitation_id, guest_id (null), name, attendance (`yes`/`no`/`maybe`), pax, note, created_at | throttle + Turnstile |
| `wishes` | id, invitation_id, name, message, is_visible, created_at | moderasi oleh pemilik |
| `gift_accounts` | id, invitation_id, type (`bank`/`ewallet`/`address`), provider, holder_name, number, qr_media_id, sort | tampil publik atas kehendak pemilik |
| `vouchers` | id, code (unik), kind, value, max_uses, used, expires_at | |
| `audit_logs` | id, user_id, action, subject_type, subject_id, meta (JSON), created_at | publish, hapus, bayar |
| `posts` | id, slug, title, body, status, published_at, seo_title, seo_description | blog dan panduan |

## Aturan
- `slug` undangan: nama + akhiran acak 4 karakter (contoh `raka-sinta-x7k2`), unik, ada daftar reserved (`preview`, `admin`, `api`, `login`, `u`, dan seterusnya).
- Status undangan: `draft → published → expired | archived`. Hanya `published` yang dapat dibuka tamu.
- Penghapusan: undangan kedaluwarsa disembunyikan, data tamu dihapus setelah masa tenggang (ditetapkan di F8-07).
- Kolom yang dibaca template selalu melewati `InvitationViewModel`. Tes kontrak (F8-02) membandingkan field definisi ↔ form ↔ model ↔ view-model.
