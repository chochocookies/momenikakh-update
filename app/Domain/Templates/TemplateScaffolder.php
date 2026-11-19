<?php

declare(strict_types=1);

namespace App\Domain\Templates;

use App\Domain\Invitation\Enums\EventType;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;

/** Creates a new template folder from the skeleton in TEMPLATE_SPEC.md. Nothing else has to be edited by hand. */
final class TemplateScaffolder
{
    public function __construct(private readonly Filesystem $files, private readonly TemplateRegistry $registry) {}

    /**
     * @param  list<string>  $events  EventType values
     * @return list<string> the files created, relative to the templates folder
     */
    public function make(string $slug, ?string $name = null, array $events = ['wedding'], string $tier = 'free'): array
    {
        $this->assertSlug($slug);
        foreach ($events as $event) {
            if (EventType::tryFrom($event) === null) {
                throw new InvalidArgumentException("Unknown event type [{$event}]. Use: ".implode(', ', EventType::values()).'.');
            }
        }
        if (! in_array($tier, TemplateManifest::TIERS, true)) {
            throw new InvalidArgumentException('Tier must be one of: '.implode(', ', TemplateManifest::TIERS).'.');
        }

        $dir = $this->registry->path($slug);
        if ($this->files->exists($dir)) {
            throw new InvalidArgumentException("Template [{$slug}] already exists.");
        }

        $name ??= ucwords(str_replace('-', ' ', $slug));
        $manifest = [
            'slug' => $slug, 'name' => $name, 'version' => '1.0.0', 'tier' => $tier,
            'event_types' => $events, 'tags' => [], 'mood' => '',
            'sections' => ['cover', 'opening', 'people', 'events', 'countdown', 'story', 'gallery', 'gift', 'map', 'rsvp', 'guestbook', 'closing', 'music'],
            'palettes' => [[
                'key' => 'default', 'name' => 'Bawaan',
                // Same values as the theme.css below; the template lint checks their contrast.
                'tokens' => ['--t-bg' => '#FFF8F3', '--t-ink' => '#2B1B22', '--t-accent' => '#BE185D', '--t-orn' => '#B8893B'],
            ]],
            'motion' => ['cover' => 'envelope', 'scroll' => 'fade-up', 'ambient' => 'none'],
            'budget' => ['css_kb' => 30, 'js_kb' => 20, 'assets_kb' => 400],
        ];

        $this->files->ensureDirectoryExists($dir.DIRECTORY_SEPARATOR.'sections');
        $this->files->ensureDirectoryExists($dir.DIRECTORY_SEPARATOR.'assets');
        $this->files->put($dir.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        $this->files->put($dir.'/index.blade.php', $this->index());
        $this->files->put($dir.'/theme.css', $this->theme($slug));
        $this->files->put($dir.'/sections/.gitkeep', '');
        $this->files->put($dir.'/assets/.gitkeep', '');

        $this->registry->flush();

        return ["{$slug}/manifest.json", "{$slug}/index.blade.php", "{$slug}/theme.css", "{$slug}/sections/", "{$slug}/assets/"];
    }

    public function assertSlug(string $slug): void
    {
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || strlen($slug) > 60) {
            throw new InvalidArgumentException('The slug must be kebab-case (a-z, 0-9, hyphen) and at most 60 characters, for example soft-rose.');
        }
    }

    private function index(): string
    {
        return <<<'BLADE'
<x-layouts.public
    :title="$invitation->title.' · '.$invitation->eventLabel"
    :description="'Undangan '.$invitation->eventLabel.' '.$invitation->title"
    :assets="$template->viteEntries()">
    <div class="inv" data-theme="{{ $template->slug }}" data-palette="{{ $invitation->palette }}" data-motion="{{ $invitation->motion }}" data-ambient="{{ $template->motion['ambient'] ?? 'none' }}">
        @foreach ($invitation->sections as $section)
            @invitationSection($section)
        @endforeach
    </div>
</x-layouts.public>

BLADE;
    }

    private function theme(string $slug): string
    {
        return <<<CSS
/* {$slug}: set the tokens, then add only rules that are really this template's own (TEMPLATE_SPEC.md "Token"). */
[data-theme='{$slug}'] {
    --t-bg: var(--color-ivory);
    --t-surface: var(--color-brand-on);
    --t-ink: var(--color-ink);
    --t-muted: var(--color-muted);
    --t-accent: var(--color-rose);
    --t-accent-ink: var(--color-brand-on);
    --t-orn: var(--color-gold);
    --t-line: var(--color-brand-line);
}

CSS;
    }
}
