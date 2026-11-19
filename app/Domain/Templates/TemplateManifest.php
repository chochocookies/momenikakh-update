<?php

declare(strict_types=1);

namespace App\Domain\Templates;

use App\Domain\Invitation\Enums\EventType;
use App\Domain\Templates\Exceptions\InvalidTemplateManifest;

/** One template's identity, read from resources/templates/{slug}/manifest.json (shape: .claude/docs/TEMPLATE_SPEC.md). */
final readonly class TemplateManifest
{
    public const TIERS = ['free', 'premium'];

    public const CORE_CSS = 'resources/css/invitation-core.css';

    public const CORE_JS = 'resources/js/invitation/core.js';

    /** Tokens every palette must define (TEMPLATE_SPEC "Token"). */
    public const REQUIRED_TOKENS = ['--t-bg', '--t-ink', '--t-accent', '--t-orn'];

    public const BUDGET_KEYS = ['css_kb', 'js_kb', 'assets_kb'];

    /**
     * @param  list<EventType>  $events
     * @param  list<string>  $tags
     * @param  list<string>  $sections  normalized section keys, in render order
     * @param  list<array{key:string,name:string,tokens:array<string,string>}>  $palettes
     * @param  array<string,string>  $motion  cover, scroll, ambient
     * @param  array{css_kb:int,js_kb:int,assets_kb:int}  $budget
     */
    public function __construct(
        public string $slug,
        public string $name,
        public string $version,
        public string $tier,
        public array $events,
        public array $tags,
        public string $mood,
        public array $sections,
        public array $palettes,
        public array $motion,
        public array $budget,
    ) {}

    /**
     * @param  array<string,mixed>  $data  decoded manifest.json
     * @return list<string> human readable problems; empty when the manifest is valid
     */
    public static function problems(array $data, string $folder): array
    {
        $p = [];
        foreach (['slug', 'name', 'version', 'tier', 'event_types', 'sections', 'palettes', 'motion', 'budget'] as $key) {
            if (! array_key_exists($key, $data)) {
                $p[] = "missing key [{$key}].";
            }
        }
        if ($p !== []) {
            return $p;
        }

        if (! is_string($data['slug']) || $data['slug'] !== $folder) {
            $p[] = 'slug ['.(is_string($data['slug']) ? $data['slug'] : gettype($data['slug']))."] must equal the folder name [{$folder}].";
        }
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $folder)) {
            $p[] = 'folder name must be kebab-case (a-z, 0-9, hyphen).';
        }
        if (! is_string($data['name']) || trim($data['name']) === '' || mb_strlen($data['name']) > 60) {
            $p[] = 'name must be a non-empty string of at most 60 characters.';
        }
        if (! is_string($data['version']) || ! preg_match('/^\d+\.\d+\.\d+$/', $data['version'])) {
            $p[] = 'version must look like 1.0.0.';
        }
        if (! in_array($data['tier'], self::TIERS, true)) {
            $p[] = 'tier must be one of: '.implode(', ', self::TIERS).'.';
        }
        if (isset($data['tags']) && (! is_array($data['tags']) || ! array_is_list($data['tags']))) {
            $p[] = 'tags must be a list of strings.';
        }
        if (isset($data['mood']) && ! is_string($data['mood'])) {
            $p[] = 'mood must be a string.';
        }

        $p = [...$p, ...self::listProblems($data['event_types'], 'event_types', EventType::values())];
        $p = [...$p, ...self::sectionProblems($data['sections'])];
        $p = [...$p, ...self::paletteProblems($data['palettes'])];

        if (! is_array($data['motion'])) {
            $p[] = 'motion must be an object like {"cover":"envelope","scroll":"fade-up","ambient":"sway"}.';
        }
        if (! is_array($data['budget'])) {
            $p[] = 'budget must be an object with css_kb, js_kb and assets_kb.';
        } else {
            foreach (self::BUDGET_KEYS as $key) {
                $v = $data['budget'][$key] ?? null;
                if (! is_int($v) || $v < 1 || $v > 2000) {
                    $p[] = "budget.{$key} must be an integer between 1 and 2000.";
                }
            }
        }

        return $p;
    }

    /** @param  array<string,mixed>  $data  decoded manifest.json */
    public static function fromArray(array $data, string $folder): self
    {
        $problems = self::problems($data, $folder);
        if ($problems !== []) {
            throw InvalidTemplateManifest::forFolder($folder, $problems);
        }

        return new self(
            $data['slug'],
            $data['name'],
            $data['version'],
            $data['tier'],
            array_map(fn (string $v) => EventType::from($v), $data['event_types']),
            array_values($data['tags'] ?? []),
            (string) ($data['mood'] ?? ''),
            array_values(array_unique(array_map(SectionRegistry::normalize(...), $data['sections']))),
            array_values($data['palettes']),
            array_map('strval', $data['motion']),
            ['css_kb' => $data['budget']['css_kb'], 'js_kb' => $data['budget']['js_kb'], 'assets_kb' => $data['budget']['assets_kb']],
        );
    }

    public function supportsEvent(EventType $type): bool
    {
        return in_array($type, $this->events, true);
    }

    /**
     * The Vite entries an invitation page loads: the shared core and this template's theme.css. Nothing else.
     * theme.js (when it exists) is not listed: core.js imports it dynamically, so it stays a separate chunk.
     *
     * @return list<string>
     */
    public function viteEntries(): array
    {
        return [self::CORE_CSS, self::CORE_JS, "resources/templates/{$this->slug}/theme.css"];
    }

    public function isPremium(): bool
    {
        return $this->tier === 'premium';
    }

    /** @return list<string> */
    public function paletteKeys(): array
    {
        return array_map(fn (array $p) => $p['key'], $this->palettes);
    }

    public function defaultPalette(): string
    {
        return $this->palettes[0]['key'];
    }

    /** @return array<string,mixed> the same shape manifest.json decodes to (used by templates:cache) */
    public function toArray(): array
    {
        return [
            'slug' => $this->slug, 'name' => $this->name, 'version' => $this->version, 'tier' => $this->tier,
            'event_types' => array_map(fn (EventType $t) => $t->value, $this->events),
            'tags' => $this->tags, 'mood' => $this->mood, 'sections' => $this->sections,
            'palettes' => $this->palettes, 'motion' => $this->motion, 'budget' => $this->budget,
        ];
    }

    /**
     * @param  list<string>  $allowed
     * @return list<string>
     */
    private static function listProblems(mixed $value, string $key, array $allowed): array
    {
        if (! is_array($value) || ! array_is_list($value)) {
            return ["{$key} must be a list."];
        }
        if ($value === []) {
            return ["{$key} must not be empty."];
        }
        $p = [];
        foreach ($value as $item) {
            if (! is_string($item) || ! in_array($item, $allowed, true)) {
                $p[] = "{$key} contains unknown value [".(is_string($item) ? $item : gettype($item)).'] (allowed: '.implode(', ', $allowed).').';
            }
        }
        if (count($value) !== count(array_unique($value, SORT_REGULAR))) {
            $p[] = "{$key} contains duplicates.";
        }

        return $p;
    }

    /** @return list<string> */
    private static function sectionProblems(mixed $sections): array
    {
        if (! is_array($sections) || ! array_is_list($sections) || $sections === []) {
            return ['sections must be a non-empty list.'];
        }
        $allowed = [...SectionRegistry::KEYS, ...array_keys(SectionRegistry::ALIASES)];
        $p = [];
        foreach ($sections as $item) {
            if (! is_string($item) || ! in_array($item, $allowed, true)) {
                $p[] = 'sections contains unknown value ['.(is_string($item) ? $item : gettype($item)).'] (allowed: '.implode(', ', SectionRegistry::KEYS).').';
            }
        }
        $normalized = array_map(fn ($s) => is_string($s) ? SectionRegistry::normalize($s) : $s, $sections);
        if (count($normalized) !== count(array_unique($normalized, SORT_REGULAR))) {
            $p[] = 'sections contains duplicates.';
        }
        if (! in_array('cover', $normalized, true)) {
            $p[] = 'sections must include [cover].';
        }

        return $p;
    }

    /** @return list<string> */
    private static function paletteProblems(mixed $palettes): array
    {
        if (! is_array($palettes) || ! array_is_list($palettes) || $palettes === []) {
            return ['palettes must be a non-empty list.'];
        }
        $p = [];
        $keys = [];
        foreach ($palettes as $i => $palette) {
            $key = is_array($palette) ? ($palette['key'] ?? null) : null;
            if (! is_string($key) || ! preg_match('/^[a-z0-9-]+$/', $key)) {
                $p[] = "palettes[{$i}].key must be kebab-case.";

                continue;
            }
            $keys[] = $key;
            if (! is_string($palette['name'] ?? null) || trim($palette['name']) === '') {
                $p[] = "palettes.{$key}.name must be a non-empty string.";
            }
            $tokens = $palette['tokens'] ?? null;
            if (! is_array($tokens)) {
                $p[] = "palettes.{$key}.tokens must be an object.";

                continue;
            }
            foreach (self::REQUIRED_TOKENS as $token) {
                $value = $tokens[$token] ?? null;
                if (! is_string($value) || ! preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value)) {
                    $p[] = "palettes.{$key}.tokens.{$token} must be a hex colour like #FBF5EC.";
                }
            }
        }
        if (count($keys) !== count(array_unique($keys))) {
            $p[] = 'palettes contains duplicate keys.';
        }

        return $p;
    }
}
