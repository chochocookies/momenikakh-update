# .claude untuk Momenikah

Paket kerja Claude Code untuk membangun Momenikah dari nol. Berdiri sendiri: tidak membutuhkan dokumen atau kode lama.

| Folder | Isi |
|---|---|
| `CLAUDE.md` | Aturan emas yang selalu aktif |
| `TODO.md` | **Satu-satunya** pelacak tugas (status, dependensi, kriteria "Selesai bila", milestone) |
| `docs/` | PRODUCT, TECH_STACK, ARCHITECTURE, TEMPLATE_SPEC, DATA_MODEL, EVENT_TYPES, SEO, BRAND, LANDING, DECISIONS, ROADMAP |
| `skills/` | 14 skill proyek (onboarding, fitur, template, jenis acara, brand, landing GSAP, SEO, database, keamanan, tes, performa, rilis, kualitas, bug) + 8 skill eksternal (`frontend-design`, `impeccable`, `ui-ux-pro-max`, `animate`, `review-animations`, `improve-animations`, `animejs`, `ponytail`). Aturan prioritasnya ada di `CLAUDE.md` |
| `rules/` | 7 aturan per folder (template, database, keamanan, frontend, tes, SEO, jenis acara) |
| `commands/` | `/next-task` `/start-task` `/finish-task` `/review-diff` `/handoff` `/new-template` `/new-event-type` `/release-check` |
| `scripts/task.mjs` | Pelacak tugas untuk `TODO.md` dan keputusan DEC di `docs/DECISIONS.md` |
| `brand/` | Logo, favicon, ikon aplikasi, gambar OG, dan generator `build-brand.py` |
| `settings.json` | Izin aman (rahasia dan perintah destruktif ditolak) |

## Pemakaian
1. Salin folder `.claude/` ke root repositori baru, lalu buka di Claude Code.
2. Jalankan `/next-task`. Ia memilih tugas siap berikutnya; keputusan terbuka (`DEC-nn`) ada di `docs/DECISIONS.md` dan dilihat dengan `task.mjs decisions`.
3. Kerjakan satu tugas per cabang: `/start-task TPL-04` ... `/finish-task TPL-04`.
4. Pelacak tugas: `node .claude/scripts/task.mjs status | next | show ID | list | blocked | decisions | estimate | check`.
5. Brand: salin `brand/favicon/*` ke `public/` dan `brand/social/og-default.png` ke `public/` (tugas DS-05). Bangun ulang dengan `python3 .claude/brand/build-brand.py`.

## Catatan
Versi teknologi bertanda ✔ di `docs/TECH_STACK.md` sudah dicek pada 14 November 2026. Sisanya perlu dicek saat instalasi. Keputusan `Proposed` dan `Open` di `docs/DECISIONS.md` belum final. Estimasi di `ROADMAP.md` adalah asumsi dan harus dihitung ulang setelah Fase 0.
