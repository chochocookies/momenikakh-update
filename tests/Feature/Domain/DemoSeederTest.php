<?php

declare(strict_types=1);

use App\Domain\Invitation\Data\InvitationData;
use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\Enums\InvitationStatus;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Billing\Models\Plan;
use Database\Seeders\DemoSeeder;

it('seeds one published demo invitation per event type', function () {
    $this->seed(DemoSeeder::class);

    expect(Invitation::query()->count())->toBe(count(EventType::cases()));
    foreach (EventType::cases() as $type) {
        $invitation = Invitation::query()->where('event_type', $type->value)->firstOrFail();
        expect($invitation->status)->toBe(InvitationStatus::Published);
        expect($invitation->slug)->toStartWith('demo-');

        $data = InvitationData::fromModel($invitation);
        expect($data->people)->not->toBeEmpty()->and($data->events)->not->toBeEmpty();
        expect($invitation->guests()->count())->toBeGreaterThan(0);
        expect($invitation->rsvps()->count())->toBeGreaterThan(0);
        expect($invitation->messages()->count())->toBeGreaterThan(0);
        expect($invitation->dailyStats()->count())->toBe(7);
    }
});

it('is idempotent', function () {
    $this->seed(DemoSeeder::class);
    $this->seed(DemoSeeder::class);
    expect(Invitation::query()->count())->toBe(count(EventType::cases()));
    expect(App\Models\User::query()->where('email', 'demo@momenikah.test')->count())->toBe(1);
});

it('uses only fictional contact data', function () {
    $this->seed(DemoSeeder::class);
    $phones = App\Domain\Guest\Models\Guest::query()->pluck('phone');
    foreach ($phones as $phone) {
        expect($phone)->toStartWith('08120000');
    }
    expect(DB::table('invitation_gifts')->pluck('number')->unique()->all())->each->toMatch('/^(1234567890|081200000001)$/');
});

it('refuses to run in production', function () {
    $this->app->detectEnvironment(fn () => 'production');
    $this->seed(DemoSeeder::class);
    expect(Invitation::query()->count())->toBe(0);
    expect(App\Models\User::query()->where('email', 'demo@momenikah.test')->exists())->toBeFalse();
});

it('seeds the plans with undecided prices and an inactive premium placeholder', function () {
    $this->seed(Database\Seeders\PlanSeeder::class);
    expect(Plan::query()->where('key', 'free')->first()->price_idr)->toBeNull();
    $premium = Plan::query()->where('key', 'premium')->first();
    expect($premium->price_idr)->toBeNull()->and($premium->is_active)->toBeFalse();
});
