<?php

declare(strict_types=1);

namespace App\Domain\Templates;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

/**
 * `php artisan template:lint`: the checks of the template checklist that a machine can do (weight, fonts,
 * dependencies, required tokens, palette contrast, ornaments). Visual approval, axe and Lighthouse run elsewhere.
 */
final class TemplateLinter
{
    /** Tokens every template has (TEMPLATE_SPEC "Token"); invitation-core.css gives each a default. */
    public const REQUIRED_TOKENS = ['--t-bg', '--t-surface', '--t-ink', '--t-muted', '--t-accent', '--t-accent-ink', '--t-orn', '--t-font-display', '--t-font-body', '--t-radius', '--t-shadow'];

    public const MAX_ORNAMENT_FILES_PER_SECTION = 3;

    public const MAX_FLOATS_PER_SECTION = 12;

    private const FONT_FILES = ['woff', 'woff2', 'ttf', 'otf', 'eot'];

    public function __construct(
        private readonly Filesystem $files,
        private readonly TemplateRegistry $registry,
        private readonly string $coreCss,
    ) {}

    /** @return array{errors:list<string>,warnings:list<string>,stats:array<string,float|int>} */
    public function lint(TemplateManifest $manifest): array
    {
        $dir = $this->registry->path($manifest->slug);
        $errors = [];
        $warnings = [];

        $css = $this->read("{$dir}/theme.css");
        $js = $this->read("{$dir}/theme.js");
        $stats = [
            'css_kb' => round(strlen((string) gzencode($css, 9)) / 1024, 2),
            'js_kb' => round(strlen((string) gzencode($js, 9)) / 1024, 2),
            'assets_kb' => round($this->assetBytes($dir) / 1024, 2),
        ];

        // 1. weight against the manifest budget
        foreach (['css_kb' => 'theme.css', 'js_kb' => 'theme.js', 'assets_kb' => 'assets/'] as $key => $what) {
            if ($stats[$key] > $manifest->budget[$key]) {
                $errors[] = "{$what} weighs {$stats[$key]} KB, over the budget of {$manifest->budget[$key]} KB (budget.{$key}).";
            }
        }

        // 2. fonts: the two brand families only, so a template defines none
        if (preg_match('/@font-face/i', $css)) {
            $errors[] = 'theme.css declares @font-face. Templates use the brand fonts (Cormorant Garamond, Plus Jakarta Sans) only.';
        }
        // `\s*+` (possessive) matters: with a plain `\s*` the regex backtracks to zero spaces and flags `font-family: var(...)`.
        if (preg_match('/font-family\s*+:\s*+(?!var\(|inherit)[^;}]+/i', $css, $m)) {
            $errors[] = 'theme.css sets a font-family that is not a token: '.trim($m[0]).'. Use var(--t-font-display) or var(--t-font-body).';
        }
        foreach ($this->allFiles($dir) as $file) {
            if (in_array(strtolower($file->getExtension()), self::FONT_FILES, true)) {
                $errors[] = "Font file {$this->rel($file)} is not allowed in a template.";
            }
        }

        // 3. dependencies: nothing loaded from outside, no bare package imports
        foreach (['theme.css' => $css, 'theme.js' => $js] as $name => $source) {
            if (preg_match('#(?:https?:)?//[a-z0-9.-]+\.[a-z]{2,}#i', (string) preg_replace('#/\*.*?\*/#s', '', $source), $m)) {
                $errors[] = "{$name} references an external address ({$m[0]}). Nothing may load from a CDN.";
            }
        }
        if (preg_match_all('/\bimport\s+(?:[^\'"]*?\bfrom\s+)?[\'"]([^\'"]+)[\'"]/', $js, $imports)) {
            foreach ($imports[1] as $target) {
                if (! str_starts_with($target, '.') && ! str_starts_with($target, '/')) {
                    $errors[] = "theme.js imports the package [{$target}]. Templates add no JavaScript dependencies.";
                }
            }
        }
        foreach ($this->bladeFiles($dir) as $file) {
            if (preg_match('/<(?:script|link)\b[^>]*(?:src|href)\s*=\s*["\']https?:/i', $file->getContents())) {
                $errors[] = "{$this->rel($file)} loads a script or stylesheet from an external address.";
            }
        }

        // 4. colours live in tokens: hex only in manifest.json
        if (preg_match('/#[0-9a-fA-F]{3,8}\b(?![\w-])/', (string) preg_replace('#/\*.*?\*/#s', '', $css), $m)) {
            $errors[] = "theme.css contains the raw colour {$m[0]}. Reference a token from tokens.css, for example var(--color-rose).";
        }

        // 5. required tokens exist in the theme or in the core defaults
        $defined = $css.$this->read($this->coreCss);
        foreach (self::REQUIRED_TOKENS as $token) {
            if (! preg_match('/'.preg_quote($token, '/').'\s*:/', $defined)) {
                $errors[] = "Required token {$token} is not defined in theme.css or invitation-core.css.";
            }
        }

        // 6. palette contrast: text on the page, accent text on the page, button text on the accent
        foreach ($manifest->palettes as $palette) {
            $t = $palette['tokens'];
            $checks = [
                'ink on bg' => [$t['--t-ink'], $t['--t-bg']],
                'accent on bg' => [$t['--t-accent'], $t['--t-bg']],
                'accent-ink on accent' => [$t['--t-accent-ink'] ?? '#FFFFFF', $t['--t-accent']],
            ];
            foreach ($checks as $label => [$fg, $bg]) {
                $ratio = Contrast::ratio($fg, $bg);
                if ($ratio < Contrast::AA_TEXT) {
                    $errors[] = sprintf('Palette [%s]: %s is %.2f : 1, below %.1f : 1.', $palette['key'], $label, $ratio, Contrast::AA_TEXT);
                }
            }
        }

        // 7. every section has a partial (own or shared)
        foreach ($manifest->sections as $section) {
            $own = is_file("{$dir}/sections/{$section}.blade.php");
            $shared = is_file($this->registry->path(TemplateRegistry::SHARED)."/sections/{$section}.blade.php");
            if (! $own && ! $shared) {
                $errors[] = "Section [{$section}] has no partial in sections/ or in _shared/sections/.";
            }
        }

        // 8. ornaments: decorative, few per section, no unlabelled pictures
        foreach ($this->bladeFiles($dir) as $file) {
            $source = $file->getContents();
            $rel = $this->rel($file);
            if (preg_match_all('/<img\b[^>]*>/i', $source, $imgs)) {
                foreach ($imgs[0] as $tag) {
                    if (! preg_match('/\balt\s*=/i', $tag)) {
                        $errors[] = "{$rel}: <img> without alt. Decorative pictures need alt=\"\" and aria-hidden=\"true\".";
                    } elseif (preg_match('/alt\s*=\s*""/', $tag) && ! str_contains($tag, 'aria-hidden="true"')) {
                        $warnings[] = "{$rel}: decorative <img> with alt=\"\" should also have aria-hidden=\"true\".";
                    }
                }
            }
            preg_match_all('/<x-ornament\b[^>]*\bname="([a-z0-9-]+)"[^>]*>/i', $source, $ornaments, PREG_SET_ORDER);
            $distinct = array_unique(array_column($ornaments, 1));
            if (count($distinct) > self::MAX_ORNAMENT_FILES_PER_SECTION) {
                $errors[] = "{$rel} uses ".count($distinct).' different ornaments; the limit is '.self::MAX_ORNAMENT_FILES_PER_SECTION.' per section.';
            }
            $floats = count(array_filter($ornaments, fn (array $o) => str_contains($o[0], 'layer="float"')));
            if ($floats > self::MAX_FLOATS_PER_SECTION) {
                $errors[] = "{$rel} has {$floats} floating ornaments; the limit is ".self::MAX_FLOATS_PER_SECTION.'.';
            }
        }

        // 9. assets must carry their licence
        if ($this->assetBytes($dir) > 0 && ! is_file("{$dir}/assets/LICENSE.md")) {
            $warnings[] = 'assets/LICENSE.md is missing. Every asset needs its licence on record.';
        }
        foreach (['thumb.webp', 'preview.webp'] as $image) {
            if (! is_file("{$dir}/{$image}")) {
                $warnings[] = "{$image} is missing (needed for the catalogue).";
            }
        }

        return ['errors' => $errors, 'warnings' => $warnings, 'stats' => $stats];
    }

    private function rel(SplFileInfo $file): string
    {
        return str_replace('\\', '/', $file->getRelativePathname());
    }

    private function read(string $path): string
    {
        return is_file($path) ? (string) file_get_contents($path) : '';
    }

    private function assetBytes(string $dir): int
    {
        $bytes = 0;
        foreach ($this->files->isDirectory("{$dir}/assets") ? $this->files->allFiles("{$dir}/assets", true) : [] as $file) {
            if ($file->getFilename() !== 'LICENSE.md' && $file->getFilename() !== '.gitkeep') {
                $bytes += $file->getSize();
            }
        }

        return $bytes;
    }

    /** @return list<SplFileInfo> */
    private function allFiles(string $dir): array
    {
        return $this->files->isDirectory($dir) ? $this->files->allFiles($dir, true) : [];
    }

    /** @return list<SplFileInfo> */
    private function bladeFiles(string $dir): array
    {
        return array_values(array_filter($this->allFiles($dir), fn (SplFileInfo $f) => str_ends_with($f->getFilename(), '.blade.php')));
    }
}
