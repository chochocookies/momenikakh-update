@props(['title' => null, 'description' => null, 'canonical' => null, 'image' => null, 'noindex' => false, 'type' => 'website'])
@php
    $fullTitle = $title ? "{$title} | Momenikah" : 'Momenikah: Undangan Pernikahan Digital';
    $desc = $description ?? 'Buat undangan digital yang indah dan bagikan lewat satu link WhatsApp.';
    $url = $canonical ?? url()->current();
    $img = $image ?? asset('og-default.png');
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $desc }}">
<link rel="canonical" href="{{ $url }}">
@if ($noindex)<meta name="robots" content="noindex, nofollow">@endif
<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
<meta name="theme-color" content="{{ config('brand.theme_color', '#BE185D') }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="Momenikah">
<meta property="og:locale" content="id_ID">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $desc }}">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ $img }}">
<meta property="og:image:width" content="1200"><meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
