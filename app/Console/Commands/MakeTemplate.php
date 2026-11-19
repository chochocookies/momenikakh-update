<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Templates\TemplateScaffolder;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'template:make', description: 'Create a new template folder from the skeleton (resources/templates/{slug})')]
final class MakeTemplate extends Command
{
    protected $signature = 'template:make {slug : kebab-case, for example soft-rose}
        {--name= : display name (default: from the slug)}
        {--events=wedding : comma separated event types the template supports}
        {--tier=free : free or premium}';

    public function handle(TemplateScaffolder $scaffolder): int
    {
        $events = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('events')))));

        try {
            $created = $scaffolder->make((string) $this->argument('slug'), $this->option('name') ?: null, $events, (string) $this->option('tier'));
        } catch (InvalidArgumentException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $slug = (string) $this->argument('slug');
        $this->components->info("Template [{$slug}] created.");
        foreach ($created as $path) {
            $this->line("  resources/templates/{$path}");
        }
        $this->newLine();
        $this->line('Next: set the tokens in theme.css, then check /preview/'.$slug.' and run php artisan template:lint '.$slug.'.');

        return self::SUCCESS;
    }
}
