---
name: feature-development
description: Implement a TODO task that adds behavior end to end (migration, model, DTO, Form Request, policy, Livewire/controller, views, tests, docs). Use for any feature task.
---
# feature-development
Urutan: **migrasi (aditif) → model/factory → DTO + Form Request → Policy → Action/service → Livewire/controller → Blade → JS**.
- Rencana pendek: data (kolom, `$fillable`, casts), input (aturan tiap field), otorisasi (policy tiap aksi tulis), rute (satu nama per halaman), tampilan (bagian template yang terdampak), tes.
- Tulis tes gagal dulu, termasuk tes round-trip untuk setiap field persisten (form → model → view-model → HTML).
- Perubahan yang menyentuh template: ikuti `template-authoring`; yang menyentuh jenis acara: `event-types`.
- Jangan membuat logika di Blade; letakkan di Action/ViewModel.
- Selesai: `/finish-task`.
