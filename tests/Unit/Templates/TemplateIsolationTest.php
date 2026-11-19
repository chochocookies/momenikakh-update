<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

/** What a template file must never contain: models, queries, the request, the session or the signed-in user. */
function templateViolations(string $source): array
{
    $rules = [
        'Eloquent import' => '/Illuminate\\\\Database|App\\\\Models\\\\|App\\\\Domain\\\\[A-Za-z]+\\\\Models\\\\/',
        'static query' => '/\b[A-Z][A-Za-z]+::(query|find|findOrFail|where|all|first|create|with|whereIn)\s*\(/',
        'DB facade' => '/\bDB::|\bSchema::/',
        'request helper' => '/\brequest\s*\(|\bRequest::|\$request\b/',
        'session helper' => '/\bsession\s*\(|\bSession::/',
        'auth helper' => '/\bauth\s*\(|\bAuth::|\bGate::|@auth|@guest\b|@can\b/',
        'relation call' => '/->(?:people|events|storyItems|gifts|guests|rsvps|messages|media)\s*\(/',
        'raw php block' => '/@php\b.*(?:query|DB::)/s',
    ];

    $found = [];
    foreach ($rules as $label => $pattern) {
        if (preg_match($pattern, $source)) {
            $found[] = $label;
        }
    }

    return $found;
}

test('the violation scanner really detects forbidden code', function (string $snippet, string $expected) {
    expect(templateViolations($snippet))->toContain($expected);
})->with([
    ['{{ \App\Domain\Invitation\Models\Invitation::query()->count() }}', 'Eloquent import'],
    ['{{ Invitation::where("id", 1)->first() }}', 'static query'],
    ['{{ DB::table("guests")->count() }}', 'DB facade'],
    ['{{ request()->query("to") }}', 'request helper'],
    ['{{ session("x") }}', 'session helper'],
    ['@auth secret @endauth', 'auth helper'],
    ['{{ $invitation->guests()->count() }}', 'relation call'],
]);

test('no template file queries data, reads the request or uses Eloquent', function () {
    $dir = resource_path('templates');
    $bad = [];
    foreach (File::exists($dir) ? File::allFiles($dir) : [] as $file) {
        if (! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }
        foreach (templateViolations($file->getContents()) as $violation) {
            $bad[] = str_replace('\\', '/', $file->getRelativePathname()).' => '.$violation;
        }
    }

    expect($bad)->toBe([]);
});
