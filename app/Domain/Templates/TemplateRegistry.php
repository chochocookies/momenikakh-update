<?php

declare(strict_types=1);

namespace App\Domain\Templates;

use App\Domain\Invitation\Enums\EventType;
use App\Domain\Templates\Exceptions\InvalidTemplateManifest;
use InvalidArgumentException;

/**
 * Finds every template by scanning resources/templates/{slug}/manifest.json, so adding a template means adding a
 * folder and nothing else. `php artisan templates:cache` freezes the result into one file for production.
 */
final class TemplateRegistry
{
    public const SHARED = '_shared';

    /** @var array<string,TemplateManifest>|null */
    private ?array $manifests = null;

    public function __construct(private readonly string $path, private readonly ?string $cacheFile = null) {}

    public function path(string $slug = ''): string
    {
        return $slug === '' ? $this->path : $this->path.DIRECTORY_SEPARATOR.$slug;
    }

    /** @return array<string,TemplateManifest> slug => manifest, sorted by slug */
    public function all(): array
    {
        return $this->manifests ??= $this->load();
    }

    /** @return list<string> */
    public function slugs(): array
    {
        return array_keys($this->all());
    }

    public function has(string $slug): bool
    {
        return isset($this->all()[$slug]);
    }

    public function find(string $slug): ?TemplateManifest
    {
        return $this->all()[$slug] ?? null;
    }

    public function get(string $slug): TemplateManifest
    {
        return $this->find($slug) ?? throw new InvalidArgumentException("Unknown template [{$slug}].");
    }

    /** Catalogue images a template may ship. Nothing else in the folder is ever served. */
    public const IMAGES = ['thumb.webp', 'preview.webp'];

    /** Path of thumb.webp or preview.webp of a registered template, or null when it has none. */
    public function image(string $slug, string $file): ?string
    {
        if (! $this->has($slug) || ! in_array($file, self::IMAGES, true)) {
            return null;
        }
        $path = $this->path($slug).DIRECTORY_SEPARATOR.$file;

        return is_file($path) ? $path : null;
    }

    /** @return array<string,TemplateManifest> */
    public function forEvent(EventType $type): array
    {
        return array_filter($this->all(), fn (TemplateManifest $m) => $m->supportsEvent($type));
    }

    /** Forget what was loaded (used after the cache file changes and in tests). */
    public function flush(): void
    {
        $this->manifests = null;
    }

    /**
     * Scans the folders and validates every manifest. Never reads the cache file.
     *
     * @return array<string,TemplateManifest>
     *
     * @throws InvalidTemplateManifest naming every broken folder and problem
     */
    public function scan(): array
    {
        $found = [];
        $problems = [];

        $dirs = glob($this->path.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR) ?: [];
        sort($dirs);
        foreach ($dirs as $dir) {
            $folder = basename($dir);
            if (str_starts_with($folder, '_') || str_starts_with($folder, '.')) {
                continue;
            }

            $manifestFile = $dir.DIRECTORY_SEPARATOR.'manifest.json';
            if (! is_file($manifestFile)) {
                $problems[$folder] = ['manifest.json not found.'];

                continue;
            }

            $data = json_decode((string) file_get_contents($manifestFile), true);
            if (! is_array($data)) {
                $problems[$folder] = ['manifest.json must contain a JSON object.'];

                continue;
            }

            $list = TemplateManifest::problems($data, $folder);
            foreach (['index.blade.php', 'theme.css'] as $required) {
                if (! is_file($dir.DIRECTORY_SEPARATOR.$required)) {
                    $list[] = "{$required} not found.";
                }
            }
            if ($list !== []) {
                $problems[$folder] = $list;

                continue;
            }

            $found[$folder] = TemplateManifest::fromArray($data, $folder);
        }

        if ($problems !== []) {
            throw new InvalidTemplateManifest($problems);
        }

        return $found;
    }

    /** Writes the cache file. Returns how many templates were cached. */
    public function cache(): int
    {
        if ($this->cacheFile === null) {
            throw new InvalidArgumentException('No cache file configured for the template registry.');
        }

        $templates = $this->scan();
        $export = array_map(fn (TemplateManifest $m) => $m->toArray(), $templates);
        $dir = dirname($this->cacheFile);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($this->cacheFile, '<?php return '.var_export($export, true).';'.PHP_EOL, LOCK_EX);
        $this->manifests = $templates;

        return count($templates);
    }

    public function clearCache(): bool
    {
        $this->manifests = null;

        return $this->cacheFile !== null && is_file($this->cacheFile) && unlink($this->cacheFile);
    }

    public function isCached(): bool
    {
        return $this->cacheFile !== null && is_file($this->cacheFile);
    }

    /** @return array<string,TemplateManifest> */
    private function load(): array
    {
        if ($this->cacheFile !== null && is_file($this->cacheFile)) {
            $cached = (static fn (string $file): mixed => require $file)($this->cacheFile);
            if (is_array($cached)) {
                $out = [];
                foreach ($cached as $slug => $data) {
                    $out[(string) $slug] = TemplateManifest::fromArray($data, (string) $slug);
                }

                return $out;
            }
        }

        return $this->scan();
    }
}
