<?php

declare(strict_types=1);

use App\Domain\Invitation\Models\Invitation;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Tests\Support\Surfaces;

it('defines the product settings and keeps payments off until checkout works', function () {
    expect(config('momenikah.name'))->toBe('Momenikah')
        ->and(config('momenikah.tagline'))->toBe('Satu link untuk semua momen nikahmu.')
        ->and(config('momenikah.features.payments'))->toBeFalse();
});

it('holds no secret in config/momenikah.php', function () {
    $source = (string) file_get_contents(config_path('momenikah.php'));

    expect(preg_match('/(secret|password|token|api[_-]?key|private)/i', preg_replace('#/\*.*?\*/#s', '', $source) ?? ''))->toBe(0);
});

it('says that payment opens soon only while payments are off', function (bool $payments, bool $shown) {
    $this->withoutVite();
    $this->seed(PlanSeeder::class);
    config(['momenikah.features.payments' => $payments]);
    $user = User::factory()->create();
    $invitation = Invitation::factory()->for($user)->create();

    $landing = $this->get(Surfaces::url('marketing', '/'))->getContent();
    $page = $this->actingAs($user)->get(route('dashboard.invitations.show', $invitation))->getContent();

    expect(str_contains($landing, 'Pembayaran online segera dibuka.'))->toBe($shown)
        ->and(str_contains($page, 'Pembayaran online segera dibuka.'))->toBe($shown);
})->with([[false, true], [true, false]]);
