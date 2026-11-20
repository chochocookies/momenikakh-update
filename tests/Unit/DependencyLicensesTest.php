<?php

declare(strict_types=1);

/**
 * FND-11: every installed dependency has a licence we may ship (THIRD_PARTY.md lists the policy). A new package with
 * a copyleft or unknown licence turns this test, and so the PR, red until it is reviewed and the list below is changed.
 */
const ALLOWED_LICENCES = [
    'MIT', 'MIT-0', 'BSD-2-Clause', 'BSD-3-Clause', 'Apache-2.0', 'ISC', '0BSD', 'BlueOak-1.0.0', 'CC0-1.0',
    'MPL-2.0',   // lightningcss (Tailwind build tool): used at build time only, not shipped
    'OFL-1.1',   // self-hosted fonts (Cormorant Garamond, Plus Jakarta Sans)
];

/** True when at least one alternative of an SPDX "A OR B" expression is allowed. */
function licenceAllowed(string $expression): bool
{
    foreach (preg_split('/\s+OR\s+/i', trim($expression, '() ')) ?: [] as $alternative) {
        if (in_array(trim($alternative, '() '), ALLOWED_LICENCES, true)) {
            return true;
        }
    }

    return false;
}

it('understands licence expressions', function () {
    expect(licenceAllowed('MIT'))->toBeTrue()
        ->and(licenceAllowed('(MIT OR Apache-2.0)'))->toBeTrue()
        ->and(licenceAllowed('BSD-3-Clause OR GPL-2.0-only OR GPL-3.0-only'))->toBeTrue()
        ->and(licenceAllowed('GPL-3.0-only'))->toBeFalse()
        ->and(licenceAllowed('AGPL-3.0-or-later'))->toBeFalse()
        ->and(licenceAllowed('UNLICENSED'))->toBeFalse()
        ->and(licenceAllowed(''))->toBeFalse();
});

it('ships only Composer packages with an allowed licence', function () {
    $installed = json_decode((string) file_get_contents(base_path('vendor/composer/installed.json')), true)['packages'];
    $bad = [];
    foreach ($installed as $package) {
        $expression = implode(' OR ', $package['license'] ?? []);
        if (! licenceAllowed($expression)) {
            $bad[] = $package['name'].' ('.($expression ?: 'no licence').')';
        }
    }

    expect($installed)->not->toBeEmpty()->and($bad)->toBe([]);
});

it('ships only npm packages with an allowed licence', function () {
    $lock = json_decode((string) file_get_contents(base_path('package-lock.json')), true)['packages'];
    $bad = [];
    foreach ($lock as $path => $package) {
        if ($path === '') {
            continue;
        }
        $license = $package['license'] ?? '';
        $expression = is_array($license) ? implode(' OR ', array_map(fn ($l) => is_array($l) ? ($l['type'] ?? '') : $l, $license)) : $license;
        if (! licenceAllowed($expression)) {
            $bad[] = $path.' ('.($expression ?: 'no licence').')';
        }
    }

    expect($lock)->not->toBeEmpty()->and($bad)->toBe([]);
});

it('keeps the update bot and the audit job in place', function () {
    $dependabot = (string) file_get_contents(base_path('.github/dependabot.yml'));
    $ci = (string) file_get_contents(base_path('.github/workflows/ci.yml'));

    foreach (['composer', 'npm', 'github-actions'] as $ecosystem) {
        expect($dependabot)->toContain("package-ecosystem: {$ecosystem}");
    }
    expect($ci)->toContain('composer audit')->toContain('npm audit --audit-level=high');
});
