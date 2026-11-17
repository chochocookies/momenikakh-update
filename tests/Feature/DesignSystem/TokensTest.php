<?php

declare(strict_types=1);

use Tests\Support\DesignSystem;

it('keeps the approved brand colours exactly', function () {
    $t = DesignSystem::tokens();
    $expected = [
        '--color-brand-bg' => '#FFF8F3', '--color-brand-ink' => '#2B1B22', '--color-brand-muted' => '#6B5A62',
        '--color-brand-rose' => '#BE185D', '--color-brand-rose-light' => '#FCE7F3', '--color-brand-line' => '#EBD9DF',
        '--color-brand-bg-dark' => '#1B1217', '--color-brand-ink-dark' => '#F6E9EE', '--color-brand-muted-dark' => '#B9A5AE',
        '--color-brand-rose-dark' => '#F472B6', '--color-brand-rose-light-dark' => '#3A1F2C',
    ];
    foreach ($expected as $name => $hex) {
        expect($t)->toHaveKey($name);
        expect(strtoupper($t[$name]))->toBe($hex, $name);
    }
});

it('meets WCAG AA contrast for every text pairing', function () {
    $t = DesignSystem::tokens();
    $pairs = [
        ['--color-brand-ink', '--color-brand-bg'], ['--color-brand-muted', '--color-brand-bg'],
        ['--color-brand-rose', '--color-brand-bg'], ['--color-brand-on', '--color-brand-rose'],
        ['--color-brand-ink', '--color-brand-rose-light'], ['--color-brand-rose', '--color-brand-rose-light'],
        ['--color-brand-ink-dark', '--color-brand-bg-dark'], ['--color-brand-muted-dark', '--color-brand-bg-dark'],
        ['--color-brand-rose-dark', '--color-brand-bg-dark'], ['--color-brand-on-dark', '--color-brand-rose-dark'],
    ];
    $failures = [];
    foreach ($pairs as [$fg, $bg]) {
        $ratio = DesignSystem::contrast($t[$fg], $t[$bg]);
        if ($ratio < 4.5) {
            $failures[] = sprintf('%s on %s = %.2f', $fg, $bg, $ratio);
        }
    }
    expect($failures)->toBeEmpty();
});

it('keeps form-control borders and focus rings at 3:1 or better', function () {
    $t = DesignSystem::tokens();
    // Input borders use the muted token; focus ring uses rose on light and rose-dark on dark surfaces.
    expect(DesignSystem::contrast($t['--color-brand-muted'], '#FFFFFF'))->toBeGreaterThanOrEqual(3.0);
    expect(DesignSystem::contrast($t['--color-brand-rose'], $t['--color-brand-bg']))->toBeGreaterThanOrEqual(3.0);
    expect(DesignSystem::contrast($t['--color-brand-rose-dark'], $t['--color-brand-bg-dark']))->toBeGreaterThanOrEqual(3.0);
    // Documented trap: rose on the dark ink background is below 3:1, so dark sections must use rose-dark.
    expect(DesignSystem::contrast($t['--color-brand-rose'], $t['--color-brand-ink']))->toBeLessThan(3.0);
});

it('does not place non-colour values in the colour namespace', function () {
    $bad = [];
    foreach (DesignSystem::tokens() as $name => $value) {
        if (str_starts_with($name, '--color-') && preg_match('/gradient\(|url\(/i', $value)) {
            $bad[] = $name;
        }
    }
    // Gradients belong in --background-image style variables, not --color-*, or bg-* utilities break.
    expect($bad)->toBeEmpty();
});

it('exposes the brand fonts through Tailwind font namespaces', function () {
    $t = DesignSystem::tokens();
    // Tailwind 4 builds font-sans / font-serif only from --font-sans / --font-serif (not --font-family-*).
    expect($t)->toHaveKey('--font-sans');
    expect($t)->toHaveKey('--font-serif');
    expect($t['--font-sans'])->toContain('Plus Jakarta Sans');
    expect($t['--font-serif'])->toContain('Cormorant Garamond');
});

it('uses Tailwind namespaces for type scale tokens', function () {
    $names = array_keys(DesignSystem::tokens());
    $wrong = array_values(array_filter($names, fn ($n) => preg_match('/^--(font-size|line-height|letter-spacing)-/', $n)));
    // Use --text-*, --leading-*, --tracking-* so text-*, leading-*, tracking-* utilities pick them up.
    expect($wrong)->toBeEmpty();
});

it('self-hosts fonts with font-display swap and existing files', function () {
    $css = (string) file_get_contents(base_path(DesignSystem::TOKENS));
    preg_match_all('/@font-face\s*\{(.*?)\}/s', $css, $blocks);
    expect(count($blocks[1]))->toBeGreaterThanOrEqual(8);

    $families = [];
    foreach ($blocks[1] as $block) {
        expect($block)->toContain('font-display: swap');
        if (preg_match("/font-family:\s*'([^']+)'/", $block, $fam)) {
            $families[$fam[1]] = true;
        }
        preg_match_all("/url\('([^']+\.woff2)'\)/", $block, $urls);
        foreach ($urls[1] as $url) {
            expect(file_exists(public_path(ltrim($url, '/'))))->toBeTrue("missing font file {$url}");
        }
    }
    expect(array_keys($families))->toEqualCanonicalizing(['Cormorant Garamond', 'Plus Jakarta Sans']);
});

it('never loads fonts or scripts from third-party CDNs', function () {
    $bad = [];
    $pattern = '#https?://(fonts\.googleapis\.com|fonts\.gstatic\.com|cdn\.jsdelivr\.net|cdnjs\.cloudflare\.com|unpkg\.com|cdn\.tailwindcss\.com|code\.jquery\.com|use\.fontawesome\.com)#i';
    $all = array_merge(
        DesignSystem::views(), DesignSystem::cssExceptTokens(), DesignSystem::scripts(),
        [['rel' => DesignSystem::TOKENS, 'content' => (string) file_get_contents(base_path(DesignSystem::TOKENS))]],
    );
    foreach ($all as $f) {
        if (preg_match($pattern, $f['content'])) {
            $bad[] = $f['rel'];
        }
    }
    expect($bad)->toBeEmpty();
});
