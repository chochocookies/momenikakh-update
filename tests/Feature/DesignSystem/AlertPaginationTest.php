<?php

declare(strict_types=1);

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;
use Tests\Support\DesignSystem;

function pager(int $page, int $total = 120, int $per = 10): LengthAwarePaginator
{
    return new LengthAwarePaginator([], $total, $per, $page, ['path' => 'https://app.test/undangan']);
}

it('renders an alert per tone with the right live-region role and an icon that is not the only message', function (string $tone, string $role) {
    $xp = DesignSystem::xpath(Blade::render('<x-ui.alert :tone="$tone" title="Judul">Isi pesan</x-ui.alert>', ['tone' => $tone]));

    expect($xp->query('//div[@role="'.$role.'"]')->length)->toBe(1)
        ->and($xp->query('//*[@aria-hidden="true"]//svg | //span[@aria-hidden="true"]')->length)->toBeGreaterThanOrEqual(1)
        ->and($xp->query('//p[normalize-space()="Judul"]')->length)->toBe(1)
        ->and($xp->textContent ?? 'x')->not->toBeEmpty();
})->with([['info', 'status'], ['success', 'status'], ['warning', 'alert'], ['danger', 'alert']]);

it('falls back to info for an unknown tone and escapes its text', function () {
    $html = Blade::render('<x-ui.alert tone="rainbow" title="<b>x</b>">{{ $text }}</x-ui.alert>', ['text' => '<script>alert(1)</script>']);

    expect($html)->toContain('role="status"')->not->toContain('<script>alert(1)')->not->toContain('<b>x</b>')->toContain('&lt;b&gt;x&lt;/b&gt;');
});

it('renders previous, a window of pages, first and last page, and marks the current one', function () {
    $xp = DesignSystem::xpath(Blade::render('<x-ui.pagination :paginator="$p" />', ['p' => pager(4)]));

    $labels = [];
    foreach ($xp->query('//nav//a | //nav//span[@aria-current]') as $node) {
        $labels[] = trim(str_replace('Halaman', '', $node->textContent));
    }

    expect($xp->query('//nav[@aria-label="Halaman"]')->length)->toBe(1)
        ->and($labels)->toBe(['Sebelumnya', '1', '3', '4', '5', '12', 'Berikutnya'])
        ->and($xp->query('//*[@aria-current="page"]')->length)->toBe(1)
        ->and(trim($xp->query('//*[@aria-current="page"]')->item(0)->textContent))->toContain('4')
        ->and($xp->query('//a[@rel="prev"]/@href')->item(0)->nodeValue)->toContain('page=3')
        ->and($xp->query('//a[@rel="next"]/@href')->item(0)->nodeValue)->toContain('page=5')
        ->and($xp->query('//span[normalize-space()="…"]')->length)->toBe(2);
});

it('disables previous on the first page and next on the last, as text and not as links', function () {
    $first = DesignSystem::xpath(Blade::render('<x-ui.pagination :paginator="$p" />', ['p' => pager(1)]));
    $last = DesignSystem::xpath(Blade::render('<x-ui.pagination :paginator="$p" />', ['p' => pager(12)]));

    expect($first->query('//span[@aria-disabled="true"][normalize-space()="Sebelumnya"]')->length)->toBe(1)
        ->and($first->query('//a[@rel="prev"]')->length)->toBe(0)->and($first->query('//a[@rel="next"]')->length)->toBe(1)
        ->and($last->query('//span[@aria-disabled="true"][normalize-space()="Berikutnya"]')->length)->toBe(1)
        ->and($last->query('//a[@rel="next"]')->length)->toBe(0);
});

it('renders nothing for a single page', function () {
    expect(trim(Blade::render('<x-ui.pagination :paginator="$p" />', ['p' => pager(1, 5)])))->toBe('');
});

it('gives every page link a touch target of at least 44 px', function () {
    $html = Blade::render('<x-ui.pagination :paginator="$p" />', ['p' => pager(4)]);

    expect(substr_count($html, 'min-h-11'))->toBeGreaterThanOrEqual(6);
    expect($html)->not->toContain('min-h-8')->not->toContain('min-h-9')->not->toContain('min-h-10');
});
