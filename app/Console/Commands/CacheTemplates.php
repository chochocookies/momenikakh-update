<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Templates\Exceptions\InvalidTemplateManifest;
use App\Domain\Templates\TemplateRegistry;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'templates:cache', description: 'Validate every template manifest and cache the list (run on deploy)')]
final class CacheTemplates extends Command
{
    public function handle(TemplateRegistry $registry): int
    {
        try {
            $count = $registry->cache();
        } catch (InvalidTemplateManifest $e) {
            $this->components->error('Templates not cached.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Templates cached ({$count}).");

        return self::SUCCESS;
    }
}
