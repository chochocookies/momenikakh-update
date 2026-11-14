# seo/
Berkas pendukung SEO. Pemetaan ke repo:
| Berkas | Tujuan di repo | Tugas |
|---|---|---|
| head.blade.php | resources/views/components/seo/head.blade.php | SEO-01 |
| jsonld-organization-website.json | dasar builder JSON-LD (tanpa rating/harga) | SEO-02 |
| robots-marketing.txt / robots-undangan.txt / robots-app.txt | public/robots.txt tiap domain | SEO-03 |
| sitemap.example.xml | contoh; hasilkan dengan spatie/laravel-sitemap | SEO-03 |
Aturan: undangan publik `noindex,nofollow` lewat meta + `X-Robots-Tag`, JANGAN `Disallow` di robots.txt (crawler harus bisa membaca tag noindex dan mengambil pratinjau). Gambar OG per jenis acara ada di ../social/.
