<?php

declare(strict_types=1);

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Guest\Enums\Attendance;
use App\Domain\Guest\Models\Guest;
use App\Domain\Guest\Models\GuestMessage;
use App\Domain\Guest\Models\Rsvp;
use App\Domain\Invitation\Models\Invitation;
use App\Models\User;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->invitation = Invitation::factory()->for($this->owner)->create(['slug' => 'raka-sinta-abcd', 'timezone' => 'Asia/Makassar']);
    $plan = Plan::factory()->withFeatures(['export_guests' => true])->create();
    Subscription::factory()->for($this->owner)->create(['plan_id' => $plan->id, 'invitation_id' => $this->invitation->id]);

    $this->parse = function (string $body, string $delimiter = ','): array {
        $h = fopen('php://memory', 'r+');
        fwrite($h, substr($body, 3));       // skip BOM
        rewind($h);
        $rows = [];
        while (($r = fgetcsv($h, 0, $delimiter, '"', '')) !== false) {
            $rows[] = $r;
        }

        return $rows;
    };
});

it('exports RSVPs with safe headers, a BOM and the right rows', function () {
    $guest = Guest::factory()->for($this->invitation)->create(['group' => 'Keluarga']);
    Rsvp::factory()->for($this->invitation)->create(['name' => 'Budi', 'attendance' => Attendance::Yes, 'pax' => 3, 'note' => 'Sampai jumpa', 'guest_id' => $guest->id, 'created_at' => '2027-03-01 02:00:00']);
    Rsvp::factory()->for(Invitation::factory()->create())->create(['name' => 'Orang Lain']);   // another invitation

    $res = $this->actingAs($this->owner)->get(route('dashboard.guests.export.rsvps', $this->invitation))->assertOk();

    expect($res->headers->get('Content-Type'))->toContain('text/csv');
    expect($res->headers->get('Content-Disposition'))->toContain('attachment')->toContain('rsvp-raka-sinta-abcd-')->toContain('.csv');
    expect($res->headers->get('Cache-Control'))->toContain('no-store');
    expect($res->headers->get('X-Content-Type-Options'))->toBe('nosniff');

    $body = $res->streamedContent();
    expect(str_starts_with($body, "\xEF\xBB\xBF"))->toBeTrue();
    $rows = ($this->parse)($body);
    expect($rows[0])->toBe(['Nama', 'Kehadiran', 'Jumlah tamu', 'Catatan', 'Kelompok', 'Waktu']);
    expect($rows)->toHaveCount(2);                                             // only this invitation
    expect($rows[1])->toBe(['Budi', 'Hadir', '3', 'Sampai jumpa', 'Keluarga', '2027-03-01 10:00']); // 02:00 UTC = 10:00 WITA
});

it('neutralises spreadsheet formulas in every exported text cell', function () {
    $attacks = ['=HYPERLINK("http://evil.test","x")', '+1+1', '-2+3', '@SUM(1,1)', "\t=1+1", "=cmd|' /C calc'!A0"];
    foreach ($attacks as $a) {
        Rsvp::factory()->for($this->invitation)->create(['name' => $a, 'note' => $a]);
        GuestMessage::factory()->for($this->invitation)->create(['name' => $a, 'message' => $a]);
    }
    foreach (['rsvps', 'messages'] as $kind) {
        $body = $this->actingAs($this->owner)->get(route("dashboard.guests.export.{$kind}", $this->invitation))->streamedContent();
        $rows = ($this->parse)($body);
        foreach (array_slice($rows, 1) as $row) {
            foreach ($row as $cell) {
                expect($cell)->not->toMatch('/^[\t\r\n ]*[=+\-@]/', "unsafe cell in {$kind}: {$cell}");
            }
        }
    }
});

it('keeps quotes, commas, newlines and unicode intact', function () {
    GuestMessage::factory()->for($this->invitation)->create(['name' => 'Zoë 日本', 'message' => "Selamat, \"bahagia\"\nselalu"]);
    $rows = ($this->parse)($this->actingAs($this->owner)->get(route('dashboard.guests.export.messages', $this->invitation))->streamedContent());
    expect($rows[1][0])->toBe('Zoë 日本')->and($rows[1][1])->toBe("Selamat, \"bahagia\"\nselalu");
});

it('can use a semicolon for Excel with Indonesian settings', function () {
    Rsvp::factory()->for($this->invitation)->create(['name' => 'Budi']);
    $body = $this->actingAs($this->owner)->get(route('dashboard.guests.export.rsvps', [$this->invitation, 'format' => 'excel-id']))->streamedContent();
    expect(($this->parse)($body, ';')[0])->toHaveCount(6);
});

it('exports a large invitation without loading everything into memory', function () {
    Rsvp::factory()->count(1200)->for($this->invitation)->create();
    $rows = ($this->parse)($this->actingAs($this->owner)->get(route('dashboard.guests.export.rsvps', $this->invitation))->streamedContent());
    expect($rows)->toHaveCount(1201);
});

it('is forbidden for other users and for plans without the export feature', function () {
    $this->actingAs(User::factory()->create())->get(route('dashboard.guests.export.rsvps', $this->invitation))->assertForbidden();

    $free = Invitation::factory()->for($this->owner)->create();
    $this->actingAs($this->owner)->get(route('dashboard.guests.export.rsvps', $free))->assertForbidden();   // free plan fallback
});
