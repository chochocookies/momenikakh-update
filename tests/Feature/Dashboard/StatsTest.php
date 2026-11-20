<?php

declare(strict_types=1);

use App\Domain\Guest\Models\GuestMessage;
use App\Domain\Guest\Models\InvitationDailyStat;
use App\Domain\Guest\Models\Rsvp;
use App\Domain\Guest\Services\StatsSummary;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;
use Tests\Support\DesignSystem;

beforeEach(function () {
    $this->withoutVite();
    $this->owner = User::factory()->create();
    $this->invitation = Invitation::factory()->for($this->owner)->wedding()->complete()->published()->create();
    $day = fn (int $ago, int $views, int $unique, int $opens) => InvitationDailyStat::factory()->create([
        'invitation_id' => $this->invitation->id, 'date' => now()->subDays($ago)->toDateString(), 'views' => $views, 'unique_visitors' => $unique, 'opens' => $opens,
    ]);
    $day(0, 10, 6, 5);
    $day(3, 20, 12, 9);
    $day(29, 5, 4, 2);   // the 30th day back is still inside the window
    $day(30, 99, 99, 99); // one day too old
    foreach ([['yes', 3], ['yes', 2], ['maybe', 1], ['no', 4]] as [$attendance, $pax]) {
        Rsvp::factory()->create(['invitation_id' => $this->invitation->id, 'attendance' => $attendance, 'pax' => $pax]);
    }
    GuestMessage::factory()->count(2)->create(['invitation_id' => $this->invitation->id]);
    GuestMessage::factory()->create(['invitation_id' => $this->invitation->id, 'is_visible' => false]);
});

it('adds up the last 30 days and what guests answered', function () {
    $s = app(StatsSummary::class)->for($this->invitation);

    expect($s)->toMatchArray(['days' => 30, 'unique_visitors' => 22, 'views' => 35, 'opens' => 16, 'rsvps' => 4, 'attending_people' => 5, 'wishes' => 2])
        ->and($s['daily'])->toHaveCount(3)->and($s['daily'][0])->toBe(['date' => now()->toDateString(), 'unique_visitors' => 6, 'views' => 10]);
});

it('shows the numbers on the invitation page of a published invitation', function () {
    $res = $this->actingAs($this->owner)->get(route('dashboard.invitations.show', $this->invitation))->assertOk();
    $xp = DesignSystem::xpath($res->getContent());
    $cell = fn (string $label) => trim($xp->query('//th[normalize-space()="'.$label.'"]/following-sibling::td')->item(0)?->textContent ?? '');

    expect($cell('Pengunjung unik'))->toBe('22')->and($cell('Kunjungan'))->toBe('35')->and($cell('Undangan dibuka'))->toBe('16')
        ->and($cell('Konfirmasi kehadiran'))->toBe('4')->and($cell('Tamu yang akan hadir'))->toBe('5')->and($cell('Ucapan tampil'))->toBe('2');
});

it('shows no statistics for an invitation that was never published', function () {
    $draft = Invitation::factory()->for($this->owner)->wedding()->complete()->create();

    $this->actingAs($this->owner)->get(route('dashboard.invitations.show', $draft))->assertOk()->assertDontSee('Statistik');
});

it('counts only the numbers of this invitation', function () {
    $other = Invitation::factory()->for($this->owner)->wedding()->complete()->published()->create();

    expect(app(StatsSummary::class)->for($other))->toMatchArray(['unique_visitors' => 0, 'views' => 0, 'rsvps' => 0, 'wishes' => 0]);
});
