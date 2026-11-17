<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Guest\Enums\Attendance;
use App\Domain\Guest\Models\Guest;
use App\Domain\Guest\Models\GuestMessage;
use App\Domain\Guest\Models\InvitationDailyStat;
use App\Domain\Guest\Models\Rsvp;
use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DOM-11: fictional demo data for every event type. NEVER runs in production.
 * php artisan db:seed --class=DemoSeeder   (idempotent: re-running does not duplicate)
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('DemoSeeder refuses to run in production.');

            return;
        }

        $this->call(PlanSeeder::class);

        $user = User::query()->firstOrCreate(
            ['email' => 'demo@momenikah.test'],
            ['name' => 'Akun Demo', 'password' => Hash::make('password'), 'email_verified_at' => now()],
        );

        foreach (EventType::cases() as $type) {
            $slug = 'demo-'.str_replace('_', '-', $type->value);
            if (Invitation::withTrashed()->where('slug', $slug)->exists()) {
                continue;
            }

            $invitation = Invitation::factory()
                ->for($user)->forType($type)->complete()->published()
                ->create(['slug' => $slug]);

            Guest::factory()->count(5)->for($invitation)->create();
            Rsvp::factory()->count(4)->for($invitation)->create();
            Rsvp::factory()->for($invitation)->create(['attendance' => Attendance::Yes]);
            GuestMessage::factory()->count(3)->for($invitation)->create();
            foreach (range(0, 6) as $daysAgo) {
                InvitationDailyStat::factory()->for($invitation)->create([
                    'date' => now()->subDays($daysAgo)->format('Y-m-d'),
                ]);
            }
        }
    }
}
