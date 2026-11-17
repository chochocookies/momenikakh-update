# .claude untuk Momenikah

Paket kerja Claude Code untuk membangun Momenikah dari nol. Berdiri sendiri: tidak membutuhkan dokumen atau kode lama.

| Folder | Isi |
|---|---|
| `CLAUDE.md` | Aturan emas yang selalu aktif |
| `docs/` | PRODUCT, TECH_STACK, ARCHITECTURE, TEMPLATE_SPEC, DATA_MODEL, EVENT_TYPES, SEO, BRAND, LANDING, DECISIONS, ROADMAP, TODO |
| `skills/` | 14 skill (onboarding, fitur, template, jenis acara, brand, landing GSAP, SEO, database, keamanan, tes, performa, rilis, kualitas, bug) |
| `rules/` | 7 aturan per folder (template, database, keamanan, frontend, tes, SEO, jenis acara) |
| `commands/` | `/next-task` `/start-task` `/finish-task` `/review-diff` `/handoff` `/new-template` `/new-event-type` `/release-check` |
| `scripts/task.mjs` | Pelacak tugas untuk `docs/TODO.md` |
| `brand/` | Logo, favicon, ikon aplikasi, gambar OG, dan generator `build-brand.py` |
| `settings.json` | Izin aman (rahasia dan perintah destruktif ditolak) |

## Pemakaian
1. Salin folder `.claude/` ke root repositori baru, lalu buka di Claude Code.
2. Jalankan `/next-task`. Mulai dari F0-01 (keputusan terbuka di `docs/DECISIONS.md`).
3. Kerjakan satu tugas per cabang: `/start-task F0-03` ... `/finish-task F0-03`.
4. Pelacak tugas: `node .claude/scripts/task.mjs status | next | show ID | estimate | check`.
5. Brand: salin `brand/favicon/*` ke `public/` dan `brand/social/og-default.png` ke `public/` (tugas F7-06). Bangun ulang dengan `python3 .claude/brand/build-brand.py`.

## Catatan
Versi teknologi bertanda ✔ di `docs/TECH_STACK.md` sudah dicek pada 14 November 2026. Sisanya perlu dicek saat instalasi. Keputusan `Proposed` dan `Open` di `docs/DECISIONS.md` belum final. Estimasi di `ROADMAP.md` adalah asumsi dan harus dihitung ulang setelah Fase 0.
