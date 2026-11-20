@props(['paginator'])
{{--
    Page links for a LengthAwarePaginator: previous, a window of numbers around the current page (with the first and
    last page and "…" for the gaps) and next. Every target is at least 44 px; the current page is aria-current.
--}}
@php
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $pages = collect([1, $last, $current - 1, $current, $current + 1])->filter(fn ($p) => $p >= 1 && $p <= $last)->unique()->sort()->values();
    $link = 'inline-flex min-h-11 min-w-11 items-center justify-center rounded-xl px-3 text-base font-semibold ring-1 ring-inset ring-ink/20 hover:bg-rose-light';
    $off = 'inline-flex min-h-11 min-w-11 items-center justify-center rounded-xl px-3 text-base text-muted ring-1 ring-inset ring-ink/10';
@endphp
@if ($last > 1)
    <nav aria-label="Halaman" {{ $attributes->class(['flex flex-wrap items-center gap-2']) }}>
        @if ($paginator->onFirstPage())
            <span class="{{ $off }}" aria-disabled="true">Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $link }}">Sebelumnya</a>
        @endif

        @foreach ($pages as $i => $page)
            @if ($i > 0 && $page - $pages[$i - 1] > 1)
                <span class="px-1 text-muted" aria-hidden="true">…</span>
            @endif
            @if ($page === $current)
                <span class="{{ $link }} bg-ink text-white ring-ink" aria-current="page"><span class="sr-only">Halaman </span>{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}" class="{{ $link }}"><span class="sr-only">Halaman </span>{{ $page }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $link }}">Berikutnya</a>
        @else
            <span class="{{ $off }}" aria-disabled="true">Berikutnya</span>
        @endif
    </nav>
@endif
