---
name: bug-investigation
description: Reproduce, isolate, and fix a defect with a regression test. Use when something is broken or behaves unexpectedly.
---
# bug-investigation
1. Reproduksi dengan langkah minimal; tulis tes yang gagal.
2. Tentukan lapisan (data, validasi, policy, view-model, template, JS) dengan log/`dd` lokal; jangan memakai data nyata.
3. Perbaiki akar masalah, bukan gejala; periksa tempat lain dengan pola sama (`rg`).
4. Tes regresi hijau; catat penyebab di changelog. Jika bug menyangkut keamanan, ikuti `security-review`.
