<?php

use Illuminate\Support\Facades\File;

it('has no emoji in views', function () {
    $hits = collect(File::allFiles(resource_path('views')))
        ->filter(fn ($f) => preg_match('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $f->getContents()))
        ->map->getRelativePathname()->values()->all();

    expect($hits)->toBeEmpty();
});

it('uses only icons that exist', function () {
    $missing = [];

    foreach (File::allFiles(resource_path('views')) as $f) {
        preg_match_all('/<x-ui\.icon[^>]*\sname="([a-z0-9-]+)"/', $f->getContents(), $m);
        foreach ($m[1] as $n) {
            is_file(resource_path("svg/icons/{$n}.svg")) || $missing[] = $n;
        }
    }

    expect(array_unique($missing))->toBeEmpty();
});
