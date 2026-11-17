<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Tests\Support\DesignSystem;
use Tests\Support\Surfaces;

beforeEach(fn () => $this->withoutVite());

/** Surface and path where /styleguide lives. Change if yours differs. */
const STYLEGUIDE_SURFACE = 'marketing';
const STYLEGUIDE_PATH = '/styleguide';

function styleguide(): string
{
    $res = test()->get(Surfaces::url(STYLEGUIDE_SURFACE, STYLEGUIDE_PATH));
    $res->assertSuccessful();

    return $res->getContent();
}

it('shows every ui component in the styleguide source', function () {
    $sources = '';
    foreach (File::allFiles(resource_path('views')) as $f) {
        if (str_contains($f->getFilename(), 'styleguide')) {
            $sources .= $f->getContents();
        }
    }
    expect($sources)->not->toBe('', 'no styleguide view found under resources/views');

    $missing = [];
    foreach (File::files(resource_path('views/components/ui')) as $file) {
        $name = str_replace('.blade.php', '', $file->getFilename());
        if (str_starts_with($name, '_')) {
            continue;
        }
        if (! str_contains($sources, "<x-ui.{$name}") && ! str_contains($sources, "x-ui.{$name} ")) {
            $missing[] = $name;
        }
    }
    expect($missing)->toBeEmpty();
});

it('renders without server errors and with unique ids', function () {
    $xp = DesignSystem::xpath(styleguide());
    $ids = [];
    foreach ($xp->query('//*[@id]') as $el) {
        $ids[] = $el->getAttribute('id');
    }
    $dupes = array_keys(array_filter(array_count_values($ids), fn ($n) => $n > 1));
    expect($dupes)->toBeEmpty();
});

it('has one h1 and a heading order that never skips levels', function () {
    $xp = DesignSystem::xpath(styleguide());
    expect($xp->query('//h1')->length)->toBe(1);
    $prev = 0;
    $skips = [];
    foreach ($xp->query('//h1|//h2|//h3|//h4|//h5|//h6') as $h) {
        $level = (int) substr($h->nodeName, 1);
        if ($prev && $level > $prev + 1) {
            $skips[] = "{$h->nodeName} after h{$prev}";
        }
        $prev = $level;
    }
    expect($skips)->toBeEmpty();
});

it('labels every form control and icon-only button', function () {
    $xp = DesignSystem::xpath(styleguide());
    $problems = [];

    foreach ($xp->query('//input[not(@type="hidden") and not(@type="submit") and not(@type="button")]|//select|//textarea') as $c) {
        $id = $c->getAttribute('id');
        $labelled = $c->hasAttribute('aria-label') || $c->hasAttribute('aria-labelledby')
            || ($id !== '' && $xp->query('//label[@for="'.$id.'"]')->length > 0)
            || $xp->query('ancestor::label', $c)->length > 0;
        if (! $labelled) {
            $problems[] = "unlabelled <{$c->nodeName}> ".($c->getAttribute('name') ?: $id);
        }
    }
    foreach ($xp->query('//button') as $b) {
        if (trim($b->textContent) === '' && ! $b->hasAttribute('aria-label') && ! $b->hasAttribute('aria-labelledby')) {
            $problems[] = 'icon-only <button> without aria-label';
        }
        if (! $b->hasAttribute('type')) {
            $problems[] = '<button> without explicit type';
        }
    }
    expect($problems)->toBeEmpty();
});

it('gives every image alt, width and height', function () {
    $xp = DesignSystem::xpath(styleguide());
    $bad = [];
    foreach ($xp->query('//img') as $img) {
        foreach (['alt', 'width', 'height'] as $a) {
            if (! $img->hasAttribute($a)) {
                $bad[] = $img->getAttribute('src')." lacks {$a}";
            }
        }
    }
    expect($bad)->toBeEmpty();
});

it('wires modals and tabs with valid aria relationships', function () {
    $xp = DesignSystem::xpath(styleguide());
    $problems = [];

    foreach ($xp->query('//*[@data-modal-open]') as $trigger) {
        $target = $trigger->getAttribute('data-modal-open');
        if ($xp->query('//dialog[@id="'.$target.'"]')->length !== 1) {
            $problems[] = "modal trigger points to missing dialog #{$target}";
        }
    }
    foreach ($xp->query('//dialog') as $d) {
        $ref = $d->getAttribute('aria-labelledby');
        if ($ref === '' || $xp->query('//*[@id="'.$ref.'"]')->length !== 1) {
            $problems[] = 'dialog without valid aria-labelledby';
        }
    }
    foreach ($xp->query('//*[@role="tablist"]') as $list) {
        $selected = 0;
        foreach ($xp->query('.//*[@role="tab"]', $list) as $tab) {
            $panel = $tab->getAttribute('aria-controls');
            if ($xp->query('//*[@role="tabpanel" and @id="'.$panel.'"]')->length !== 1) {
                $problems[] = "tab controls missing panel #{$panel}";
            }
            if ($tab->getAttribute('aria-selected') === 'true') {
                $selected++;
                if ($tab->getAttribute('tabindex') !== '0') {
                    $problems[] = 'selected tab must have tabindex=0';
                }
            } elseif ($tab->getAttribute('tabindex') !== '-1') {
                $problems[] = 'unselected tab must have tabindex=-1';
            }
        }
        if ($selected !== 1) {
            $problems[] = "tablist has {$selected} selected tabs";
        }
    }
    foreach ($xp->query('//*[@role="tabpanel"]') as $panel) {
        $ref = $panel->getAttribute('aria-labelledby');
        if ($xp->query('//*[@role="tab" and @id="'.$ref.'"]')->length !== 1) {
            $problems[] = 'tabpanel without matching tab';
        }
    }
    expect($problems)->toBeEmpty();
});

it('demonstrates the error, disabled and loading states', function () {
    $html = styleguide();
    expect($html)->toContain('aria-invalid="true"')->toContain('aria-busy="true"')->toContain('disabled');
});
