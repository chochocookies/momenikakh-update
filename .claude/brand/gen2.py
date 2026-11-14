import cairosvg, os, re
exec(open('gen.py').read().split('# logos')[0])   # helper: B, font, outline, mark, svg, w
for d in ('logo/png','social','seo','favicon'): os.makedirs(B+d,exist_ok=True)
def ras(svgtxt,path,wd,ht=None,bg=None):
    cairosvg.svg2png(bytestring=svgtxt.encode(),write_to=B+path,output_width=wd,output_height=ht,background_color=bg)
# PNG logo (transparan)
for n in ('horizontal','horizontal-white','stacked','stacked-white'):
    s=open(B+f'logo/logo-{n}.svg').read(); ras(s,f'logo/png/logo-{n}-1600.png',1600)
for px in (512,1024): ras(open(B+'logo/logo-mark.svg').read(),f'logo/png/logo-mark-{px}.png',px)
# avatar sosial 400x400 (mawar) dan versi ivory
fav=lambda pad:svg('0 0 64 64',f'<rect width="64" height="64" fill="{ROSE}"/><g transform="translate(32 32) scale({1-pad}) translate(-32 -32)">'+mark(IVORY,GOLDL,IVORY,sw=3.4,r=14,ca=24,cb=40)+'</g>')
ras(fav(.3),'social/avatar-400.png',400)
# OG per jenis acara
EV=[('pernikahan','Pernikahan'),('walimatul-urs','Walimatul Urs'),('tunangan','Tunangan'),('khitanan','Khitanan'),('aqiqah','Aqiqah'),('ulang-tahun','Ulang Tahun')]
def og(title,sub):
    a,_=outline('Momenikah',f600,44,112,86,INK)
    t,_=outline(title,f600,128,96,360,INK)
    s,_=outline(sub,f500i,48,100,440,ROSE)
    bg=f'<rect width="1200" height="630" fill="{IVORY}"/><g opacity=".14" transform="translate(660 -30) scale(9.2)">{mark(heart=False)}</g><g transform="translate(40 40) scale(.95)">{mark()}</g>'
    return svg('0 0 1200 630',bg+a+t+s,f'Momenikah: undangan digital {title}')
for k,t in EV: ras(og(t,'Undangan digital dalam satu tautan'),f'social/og-{k}.png',1200,630)
# pinned tab + tile
w('favicon/safari-pinned-tab.svg',svg('0 0 64 64',mark('#000','#000','#000',heart=True)))
w('favicon/browserconfig.xml','<?xml version="1.0" encoding="utf-8"?><browserconfig><msapplication><tile><square150x150logo src="/android-chrome-192.png"/><TileColor>#BE185D</TileColor></tile></msapplication></browserconfig>')
# SEO
w('seo/robots-marketing.txt','User-agent: *\nAllow: /\nDisallow: /register\nDisallow: /login\nSitemap: https://momenikah.online/sitemap.xml\n')
w('seo/robots-undangan.txt','# Jangan Disallow: pratinjau WhatsApp/sosial perlu mengambil halaman.\n# Pengindeksan dicegah lewat <meta robots noindex> + header X-Robots-Tag, bukan robots.txt.\nUser-agent: *\nAllow: /\n')
w('seo/robots-app.txt','User-agent: *\nDisallow: /\n')
w('seo/sitemap.example.xml','<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n  <url><loc>https://momenikah.online/</loc><changefreq>weekly</changefreq><priority>1.0</priority></url>\n  <url><loc>https://momenikah.online/template</loc></url>\n  <url><loc>https://momenikah.online/jenis-acara/pernikahan</loc></url>\n  <url><loc>https://momenikah.online/jenis-acara/walimatul-urs</loc></url>\n</urlset>\n<!-- Contoh saja. Hasilkan dengan spatie/laravel-sitemap (SEO-03); hanya halaman pemasaran. -->\n')
w('seo/head.blade.php','''{{-- resources/views/components/seo/head.blade.php — props: $seo (Seo value object) --}}
<title>{{ $seo->title }}</title>
<meta name="description" content="{{ $seo->description }}">
<link rel="canonical" href="{{ $seo->canonical }}">
<meta name="robots" content="{{ $seo->robots }}">{{-- index,follow | noindex,nofollow --}}
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#BE185D">
<link rel="manifest" href="/site.webmanifest">
<meta name="theme-color" content="#BE185D">
<meta property="og:site_name" content="Momenikah">
<meta property="og:locale" content="id_ID">
<meta property="og:type" content="{{ $seo->ogType ?? 'website' }}">
<meta property="og:title" content="{{ $seo->title }}">
<meta property="og:description" content="{{ $seo->description }}">
<meta property="og:url" content="{{ $seo->canonical }}">
<meta property="og:image" content="{{ $seo->image }}">
<meta property="og:image:width" content="1200"><meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo->title }}">
<meta name="twitter:description" content="{{ $seo->description }}">
<meta name="twitter:image" content="{{ $seo->image }}">
@foreach ($seo->jsonLd as $block)
<script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG) !!}</script>
@endforeach
''')
w('seo/jsonld-organization-website.json','''[
 {"@context":"https://schema.org","@type":"Organization","name":"Momenikah","url":"https://momenikah.online","logo":"https://momenikah.online/android-chrome-512.png"},
 {"@context":"https://schema.org","@type":"WebSite","name":"Momenikah","url":"https://momenikah.online","inLanguage":"id-ID"},
 {"@context":"https://schema.org","@type":"SoftwareApplication","name":"Momenikah","applicationCategory":"BusinessApplication","operatingSystem":"Web","description":"Pembuat undangan digital untuk pernikahan, walimatul urs, tunangan, khitanan, aqiqah, dan ulang tahun."}
]
''')
w('seo/README.md','''# seo/
Berkas pendukung SEO. Pemetaan ke repo:
| Berkas | Tujuan di repo | Tugas |
|---|---|---|
| head.blade.php | resources/views/components/seo/head.blade.php | SEO-01 |
| jsonld-organization-website.json | dasar builder JSON-LD (tanpa rating/harga) | SEO-02 |
| robots-marketing.txt / robots-undangan.txt / robots-app.txt | public/robots.txt tiap domain | SEO-03 |
| sitemap.example.xml | contoh; hasilkan dengan spatie/laravel-sitemap | SEO-03 |
Aturan: undangan publik `noindex,nofollow` lewat meta + `X-Robots-Tag`, JANGAN `Disallow` di robots.txt (crawler harus bisa membaca tag noindex dan mengambil pratinjau). Gambar OG per jenis acara ada di ../social/.
''')
print(sorted(os.listdir(B+'social')))
