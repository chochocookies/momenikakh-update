<?php

declare(strict_types=1);

use App\Models\User;
use Tests\Support\Surfaces;

beforeEach(fn () => $this->withoutVite());

it('renders the sign-in, register and reset pages on the app host, not indexed', function (string $path) {
    $res = $this->get(Surfaces::url('app', $path))->assertOk();

    expect($res->getContent())->toContain('noindex')->toContain('data-surface="app"');
})->with(['/login', '/register', '/forgot-password']);

it('renders the email verification notice for a signed-in unverified user', function () {
    $this->actingAs(User::factory()->unverified()->create())->get(route('verification.notice'))->assertOk()->assertSee('data-surface="app"', false);
});
