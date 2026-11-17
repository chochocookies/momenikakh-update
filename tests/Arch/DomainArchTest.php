<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

// Complements FND-04. Keep both: this file adds rules for the models, enums and DTOs created in DOM-01..DOM-11.

arch('domain does not depend on the delivery layers')
    ->expect('App\Domain')
    ->not->toUse(['App\Http', 'Livewire', 'App\Livewire', 'Filament', 'App\Filament']);

arch('domain does not read request, session or the authenticated user')
    ->expect('App\Domain')
    ->not->toUse(['request', 'session', 'auth', 'Illuminate\Http\Request', 'Illuminate\Support\Facades\Auth', 'Illuminate\Support\Facades\Request']);

arch('domain enums are backed string enums')
    ->expect('App\Domain\Invitation\Enums')->toBeStringBackedEnums()
    ->and('App\Domain\Guest\Enums')->toBeStringBackedEnums()
    ->and('App\Domain\Billing\Enums')->toBeStringBackedEnums()
    ->and('App\Domain\Account\Enums')->toBeStringBackedEnums();

arch('DTOs are final and readonly')
    ->expect('App\Domain\Invitation\Data')->toBeFinal()->toBeReadonly();

arch('event type definitions are final and readonly')
    ->expect('App\Domain\Invitation\EventTypes\EventTypeDefinition')->toBeFinal()->toBeReadonly();

arch('policies live next to their domain and never touch HTTP')
    ->expect('App\Domain\Invitation\Policies')->not->toUse('Illuminate\Http');

arch('no debugging leftovers')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])->not->toBeUsed();

test('every model sets $fillable explicitly (no $guarded = [])', function () {
    $bad = [];
    $domainDir = dirname(__DIR__, 2).'/app/Domain';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($domainDir));

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $src = (string) file_get_contents($file->getPathname());
        if (str_contains($src, 'extends Model') && (str_contains($src, '$guarded = []') || ! str_contains($src, '$fillable'))) {
            $bad[] = $file->getFilename();
        }
    }

    expect($bad)->toBeEmpty();
});
