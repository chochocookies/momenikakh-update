<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Templates\Exceptions\InvalidTemplateManifest;
use App\Domain\Templates\TemplateLinter;
use App\Domain\Templates\TemplateRegistry;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'template:lint', description: 'Check templates against the checklist: manifest, weight, fonts, dependencies, tokens, contrast, ornaments')]
final class LintTemplates extends Command
{
    protected $signature = 'template:lint {slug? : one template; all when omitted}';

    public function handle(TemplateRegistry $registry, TemplateLinter $linter): int
    {
        try {
            $all = $registry->scan();
        } catch (InvalidTemplateManifest $e) {
            $this->components->error('Manifest check failed.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }

        $slug = $this->argument('slug');
        if ($slug !== null && ! isset($all[$slug])) {
            $this->components->error("Unknown template [{$slug}].");

            return self::FAILURE;
        }

        $failed = false;
        foreach ($slug !== null ? [$all[$slug]] : $all as $manifest) {
            $result = $linter->lint($manifest);
            $s = $result['stats'];
            $this->components->twoColumnDetail(
                "<fg=white;options=bold>{$manifest->slug}</>",
                "css {$s['css_kb']}/{$manifest->budget['css_kb']} KB · js {$s['js_kb']}/{$manifest->budget['js_kb']} KB · assets {$s['assets_kb']}/{$manifest->budget['assets_kb']} KB",
            );
            foreach ($result['errors'] as $error) {
                $this->components->error($error);
            }
            foreach ($result['warnings'] as $warning) {
                $this->components->warn($warning);
            }
            if ($result['errors'] === []) {
                $this->components->info("{$manifest->slug}: no errors".($result['warnings'] === [] ? '.' : ' ('.count($result['warnings']).' warnings).'));
            }
            $failed = $failed || $result['errors'] !== [];
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
