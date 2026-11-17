<?php

declare(strict_types=1);

use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Rules\AvailableSlug;
use App\Domain\Invitation\Services\SlugGenerator;

beforeEach(fn () => $this->slugs = app(SlugGenerator::class));

it('builds a readable slug with a 4 character suffix', function () {
    $slug = $this->slugs->generate('Raka & Sinta');
    expect($slug)->toMatch('/^raka-sinta-[a-hj-km-np-z2-9]{4}$/');
});

it('generates unique slugs for the same title', function () {
    $seen = [];
    foreach (range(1, 30) as $_) {
        $slug = $this->slugs->generate('Raka & Sinta');
        expect($seen)->not->toContain($slug);
        $seen[] = $slug;
        Invitation::factory()->create(['slug' => $slug]);
    }
    expect(array_unique($seen))->toHaveCount(30);
});

it('falls back to a safe base for empty or symbol-only titles', function () {
    expect($this->slugs->generate('???'))->toStartWith('undangan-');
    expect($this->slugs->generate(''))->toStartWith('undangan-');
});

it('truncates long titles on a word boundary', function () {
    $slug = $this->slugs->generate(str_repeat('abcdefgh ', 20));
    expect(strlen($slug))->toBeLessThanOrEqual(45)->and($slug)->not->toContain('--');
});

it('transliterates accents and strips unsafe characters', function () {
    $slug = $this->slugs->generate('Ráka <script>alert(1)</script> & Síntá');
    expect($slug)->toMatch('/^[a-z0-9]+(?:-[a-z0-9]+)*$/');
});

it('rejects reserved slugs, case-insensitively', function () {
    foreach (['preview', 'admin', 'api', 'login', 'u', 'styleguide', 'ADMIN', 'Preview'] as $reserved) {
        expect($this->slugs->isAvailable(strtolower($reserved)))->toBeFalse($reserved);
        expect($this->slugs->isReserved($reserved))->toBeTrue($reserved);
    }
});

it('rejects malformed slugs', function () {
    foreach (['ab', '-abc', 'abc-', 'a--b', 'Abc', 'a b c', 'a/b', '../etc', str_repeat('a', 61)] as $bad) {
        expect($this->slugs->isAvailable($bad))->toBeFalse($bad);
    }
});

it('treats taken slugs, including soft-deleted ones, as unavailable', function () {
    $i = Invitation::factory()->create(['slug' => 'raka-sinta-abcd']);
    expect($this->slugs->isAvailable('raka-sinta-abcd'))->toBeFalse();
    $i->delete(); // soft delete keeps the slug reserved so old links never point at someone else
    expect($this->slugs->isAvailable('raka-sinta-abcd'))->toBeFalse();
    expect($this->slugs->isAvailable('raka-sinta-abcd', $i->id))->toBeTrue(); // editing own slug is allowed
});

it('validates user chosen slugs through the AvailableSlug rule', function () {
    Invitation::factory()->create(['slug' => 'sudah-dipakai']);
    expect(Validator::make(['slug' => 'baru-saja'], ['slug' => [new AvailableSlug()]])->passes())->toBeTrue();
    expect(Validator::make(['slug' => 'sudah-dipakai'], ['slug' => [new AvailableSlug()]])->fails())->toBeTrue();
    expect(Validator::make(['slug' => 'admin'], ['slug' => [new AvailableSlug()]])->fails())->toBeTrue();
});
