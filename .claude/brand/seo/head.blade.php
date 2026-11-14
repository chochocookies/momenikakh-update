{{-- resources/views/components/seo/head.blade.php — props: $seo (Seo value object) --}}
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
