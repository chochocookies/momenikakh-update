---
name: security-review
description: Review a change for security (IDOR, XSS, upload, webhook, rate limits, headers, PII). Use for auth, public endpoints, media, billing, and templates.
---
# security-review
Periksa: Policy di setiap aksi tulis dan scope kepemilikan · Form Request/whitelist · `{{ }}` bukan `{!! !!}` · `?to=` hanya lewat `textContent` · throttle + honeypot + Turnstile di RSVP/buku tamu/login/daftar · unggahan (mime asli, ukuran, EXIF, nama acak) · webhook (signature, idempoten, jumlah dari server) · CSP/HSTS/cookie flags · log tanpa PII · rahasia tidak di repo · `composer audit` dan `npm audit`.
Keluaran: daftar temuan (tinggi/sedang/rendah) dengan file:baris dan perbaikan; tes untuk setiap perbaikan.
