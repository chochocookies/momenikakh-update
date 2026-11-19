<?php

declare(strict_types=1);

return [
    /*
    | TPL-01: where templates live and where `php artisan templates:cache` writes the frozen manifest list.
    | One folder per template: resources/templates/{slug}/manifest.json (shape: .claude/docs/TEMPLATE_SPEC.md).
    */
    'path' => resource_path('templates'),

    'cache' => base_path('bootstrap/cache/templates.php'),

    // Rendered when an invitation names a template that no longer exists.
    'default' => 'classic-elegant',
];
