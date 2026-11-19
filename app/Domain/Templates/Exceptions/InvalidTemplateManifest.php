<?php

declare(strict_types=1);

namespace App\Domain\Templates\Exceptions;

use RuntimeException;

/** Thrown when one or more template folders cannot be loaded. The message names every folder and every problem. */
final class InvalidTemplateManifest extends RuntimeException
{
    /** @param  array<string,list<string>>  $problems  folder => problems */
    public function __construct(public readonly array $problems)
    {
        $lines = [];
        foreach ($problems as $folder => $list) {
            foreach ($list as $problem) {
                $lines[] = "resources/templates/{$folder}: {$problem}";
            }
        }

        parent::__construct("Invalid template manifest:\n- ".implode("\n- ", $lines));
    }

    /** @param  list<string>  $problems */
    public static function forFolder(string $folder, array $problems): self
    {
        return new self([$folder => $problems]);
    }
}
