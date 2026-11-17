<?php

declare(strict_types=1);

use Tests\Support\DesignSystem;

function allCss(): string
{
    $css = '';
    foreach (array_merge(DesignSystem::cssExceptTokens(), [[
        'rel' => DesignSystem::TOKENS, 'content' => (string) file_get_contents(base_path(DesignSystem::TOKENS)),
    ]]) as $f) {
        $css .= $f['content']."\n";
    }

    return $css;
}

it('defines a visible keyboard focus style', function () {
    expect(allCss())->toContain(':focus-visible');
});

it('never removes the focus outline without a replacement', function () {
    $css = preg_replace('#/\*.*?\*/#s', '', allCss());
    preg_match_all('/([^{}]+)\{[^{}]*outline\s*:\s*(?:none|0)\b[^{}]*\}/i', $css, $m);
    $bad = array_values(array_filter($m[1], fn ($sel) => ! str_contains($sel, ':focus-visible') && ! str_contains($sel, ':focus:not(')));
    expect($bad)->toBeEmpty();
});

it('honours prefers-reduced-motion globally', function () {
    expect(allCss())->toMatch('/@media\s*\(prefers-reduced-motion:\s*reduce\)/');
});

it('does not use positive tabindex or disable zoom', function () {
    $bad = [];
    foreach (DesignSystem::views() as $f) {
        if (preg_match('/tabindex="[1-9]/', $f['content']) || preg_match('/user-scalable\s*=\s*(no|0)|maximum-scale/i', $f['content'])) {
            $bad[] = $f['rel'];
        }
    }
    expect($bad)->toBeEmpty();
});

it('keeps interactive text at 16px or more on form controls (no iOS zoom on focus)', function () {
    $bad = [];
    foreach (DesignSystem::views() as $f) {
        if (str_contains($f['rel'], 'components/ui/') && preg_match('/<(input|select|textarea)\b/', $f['content'])) {
            if (preg_match('/\btext-(xs|sm)\b/', preg_replace('/<label.*?<\/label>/s', '', $f['content']) ?? '')) {
                $bad[] = $f['rel'];
            }
        }
    }
    expect($bad)->toBeEmpty();
});
