<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Templates\TemplateRegistry;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'templates:clear', description: 'Remove the template manifest cache')]
final class ClearTemplateCache extends Command
{
    public function handle(TemplateRegistry $registry): int
    {
        $registry->clearCache();
        $this->components->info('Template cache cleared.');

        return self::SUCCESS;
    }
}
