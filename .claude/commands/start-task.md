---
description: Mulai mengerjakan satu tugas (contoh: /start-task F2-05)
---
ID tugas: $ARGUMENTS. Bila kosong, tanyakan.
1. `node .claude/scripts/task.mjs show $ARGUMENTS`. Bila ada dependensi belum `done`, berhenti dan laporkan.
2. Baca skill dan dokumen yang relevan: `feature-development` selalu; tambah `template-authoring`, `event-types`, `landing-gsap`, `seo-implementation`, `brand-identity`, `security-review`, atau `database-management` sesuai tugas. Baca juga `.claude/docs/DECISIONS.md` untuk keputusan terkait.
3. Masuk Plan Mode. Tulis rencana: berkas yang disentuh, data, rute, komponen, tes, risiko, dokumen yang diperbarui. Tunggu persetujuan.
4. Setelah disetujui: `node .claude/scripts/task.mjs start $ARGUMENTS`, buat cabang `feat/$ARGUMENTS-slug-singkat`, kerjakan dengan tes.
