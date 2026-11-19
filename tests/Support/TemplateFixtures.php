<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\Templates\InvitationViewModelBuilder;
use App\Domain\Templates\TemplateRegistry;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

/** Throw-away template folders for tests, so tests never depend on the real catalogue. */
final class TemplateFixtures
{
    /** @param  array<string,mixed>  $override  keys of manifest.json */
    public static function manifest(string $slug = 'soft-rose', array $override = []): array
    {
        return array_replace([
            'slug' => $slug, 'name' => 'Soft Rose', 'version' => '1.0.0', 'tier' => 'free',
            'event_types' => ['wedding', 'walimatul_urs', 'engagement', 'aqiqah', 'khitanan', 'birthday'],
            'tags' => ['klasik'], 'mood' => 'hangat',
            'sections' => ['cover', 'opening', 'people', 'events', 'story', 'gallery', 'gift', 'rsvp', 'guestbook', 'countdown', 'map', 'music', 'closing'],
            'palettes' => [
                ['key' => 'rose', 'name' => 'Rose', 'tokens' => ['--t-bg' => '#FBF5EC', '--t-ink' => '#4A1F2B', '--t-accent' => '#7B1E3A', '--t-orn' => '#B8893B']],
            ],
            'motion' => ['cover' => 'envelope', 'scroll' => 'fade-up', 'ambient' => 'none'],
            'budget' => ['css_kb' => 30, 'js_kb' => 20, 'assets_kb' => 400],
        ], $override);
    }

    /** @param  array<string,mixed>|null  $manifest  null = no manifest.json */
    public static function make(string $root, string $folder, ?array $manifest, bool $files = true, string $index = ''): void
    {
        $dir = $root.DIRECTORY_SEPARATOR.$folder;
        File::ensureDirectoryExists($dir);
        if ($manifest !== null) {
            file_put_contents($dir.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
        if ($files) {
            file_put_contents($dir.'/index.blade.php', $index);
            file_put_contents($dir.'/theme.css', '');
        }
    }

    public static function tempRoot(): string
    {
        $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'tpl-'.bin2hex(random_bytes(4));
        File::ensureDirectoryExists($root);

        return $root;
    }

    /** Makes the app use $root as its templates folder (registry, builder and views). */
    public static function useRoot(string $root, bool $withShared = false): TemplateRegistry
    {
        config(['templates.path' => $root, 'templates.cache' => $root.'-cache'.DIRECTORY_SEPARATOR.'templates.php']);
        app()->forgetInstance(TemplateRegistry::class);
        app()->forgetInstance(InvitationViewModelBuilder::class);
        View::replaceNamespace('templates', $withShared ? [$root, resource_path('templates')] : $root);

        return app(TemplateRegistry::class);
    }

    public static function cleanup(string $root): void
    {
        File::deleteDirectory($root);
        File::deleteDirectory($root.'-cache');
    }
}
