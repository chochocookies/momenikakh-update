@props(['template', 'size' => 'sm', 'dateLong', 'dateShort'])
{{-- A sample phone for the landing page. All names, dates and places are fictional. Only the big phone has a real button. --}}
@php
    $key = $template['key'];
    $big = $size === 'lg';
@endphp
<div {{ $attributes->class(['ph', "t-{$key}", $big ? 'ph--lg' : 'ph--sm']) }} data-name="{{ $template['name'] }}" @unless ($big) aria-hidden="true" @endunless>
    <div class="in">
        <p>Undangan pernikahan</p>
        <p class="nm">Raka &amp; Sinta</p>
        <p>{{ $dateLong }}</p>
        <div class="cd"><span><b data-d>0</b>hari</span><span><b data-h>0</b>jam</span><span><b data-m>0</b>menit</span></div>
        <p>Akad pukul 09.00 WIB<br>Gedung Mawar, lokasi contoh</p>
        <p class="gf">Amplop digital</p>
    </div>
    <div class="cv">
        <p>The Wedding of</p>
        <p class="nm">Raka &amp; Sinta</p>
        <p>{{ $dateShort }}</p>
        @if ($big)
            <button type="button" class="bo" data-open>Buka undangan</button>
        @else
            <span class="bo">Buka undangan</span>
        @endif
    </div>
    <div class="fx">@if ($key === 'floral') @for ($i = 0; $i < 9; $i++)<s></s>@endfor @endif</div>
</div>
