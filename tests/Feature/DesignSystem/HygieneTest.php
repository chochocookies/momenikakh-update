<?php

declare(strict_types=1);

use Tests\Support\DesignSystem;

/** Files allowed to break a rule (relative paths). Keep this list short and justified. */
const HYGIENE_ALLOW = [
    'inline_style' => [],
    'raw_hex' => [],
];

function offenders(array $files, string $pattern, array $allow = []): array
{
    $out = [];
    foreach ($files as $f) {
        if (in_array($f['rel'], $allow, true)) {
            continue;
        }
        if (preg_match($pattern, $f['content'], $m)) {
            $out[] = $f['rel'].' => '.trim($m[0]);
        }
    }

    return $out;
}

it('has no hard-coded colours in views, scripts or non-token css', function () {
    $arbitrary = '/\b(?:bg|text|border|ring|outline|fill|stroke|from|to|via|shadow|decoration|accent|caret)-\[#[0-9a-fA-F]{3,8}\]/';
    $attr = '/(?:style|fill|stroke|stop-color)="[^"]*#[0-9a-fA-F]{3,8}\b/';
    $bad = array_merge(
        offenders(DesignSystem::views(), $arbitrary),
        offenders(DesignSystem::views(), $attr, HYGIENE_ALLOW['raw_hex']),
        offenders(DesignSystem::scripts(), '/#[0-9a-fA-F]{6}\b/'),
        offenders(DesignSystem::cssExceptTokens(), '/(?<![&\w-])#[0-9a-fA-F]{6}\b|(?<![&\w-])#[0-9a-fA-F]{3}\b(?!\w)/'),
    );
    expect($bad)->toBeEmpty();
});

it('has no inline styles, inline scripts or inline event handlers in views', function () {
    $views = DesignSystem::views();
    $bad = array_merge(
        offenders($views, '/\sstyle="/', HYGIENE_ALLOW['inline_style']),
        offenders($views, '/<style[\s>]/'),
        offenders($views, '/<script(?![^>]*(?:src=|ld\+json|nonce))[^>]*>/'),
        offenders($views, '/\son(?:click|load|error|change|submit|focus|blur|mouse\w+|key\w+)="/i'),
        offenders($views, '/href="javascript:/i'),
    );
    expect($bad)->toBeEmpty();
});

it('never interpolates tailwind class names (they would be purged)', function () {
    $pattern = '/\b(?:bg|text|border|ring|outline|fill|stroke|from|to|via|shadow|rounded|p[xytrbl]?|m[xytrbl]?|gap|w|h|size)-(?:brand-)?\{\{/';
    expect(offenders(DesignSystem::views(), $pattern))->toBeEmpty();
});

it('reserves gold for decoration and never uses it for text', function () {
    // Gold on ivory is 2.99:1, below AA for text.
    expect(offenders(DesignSystem::views(), '/\btext-brand-gold(?:-alt)?\b/'))->toBeEmpty();
});

it('opens external links safely', function () {
    $bad = [];
    foreach (DesignSystem::views() as $f) {
        preg_match_all('/<a\b[^>]*target="_blank"[^>]*>/i', $f['content'], $m);
        foreach ($m[0] as $tag) {
            if (! preg_match('/rel="[^"]*noopener/i', $tag)) {
                $bad[] = $f['rel'].' => '.$tag;
            }
        }
    }
    expect($bad)->toBeEmpty();
});

it('gives every static img alt, width and height', function () {
    $bad = [];
    foreach (DesignSystem::views() as $f) {
        preg_match_all('/<img\b[^>]*>/i', $f['content'], $m);
        foreach ($m[0] as $tag) {
            foreach (['alt', 'width', 'height'] as $a) {
                if (! preg_match('/\s'.$a.'=/', $tag) && ! str_contains($tag, '{{ $attributes')) {
                    $bad[] = $f['rel']." lacks {$a}: ".substr($tag, 0, 80);
                }
            }
        }
    }
    expect($bad)->toBeEmpty();
});

it('gives every static button an explicit type', function () {
    $bad = [];
    foreach (DesignSystem::views() as $f) {
        preg_match_all('/<button\b[^>]*>/i', $f['content'], $m);
        foreach ($m[0] as $tag) {
            if (! preg_match('/\stype=/', $tag)) {
                $bad[] = $f['rel'].' => '.substr($tag, 0, 80);
            }
        }
    }
    expect($bad)->toBeEmpty();
});

it('limits fonts to the two brand families', function () {
    $bad = [];
    foreach (array_merge(DesignSystem::views(), DesignSystem::cssExceptTokens()) as $f) {
        if (preg_match('/font-family\s*:\s*(?!var\(|inherit)[^;]+/i', $f['content'], $m)) {
            $bad[] = $f['rel'].' => '.trim($m[0]);
        }
    }
    expect($bad)->toBeEmpty();
});
