<?php

declare(strict_types=1);

use App\Domain\Invitation\Data\InvitationData;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Invitation\Models\InvitationEvent;
use App\Domain\Invitation\Services\InvitationWriter;
use App\Domain\Templates\InvitationViewModelBuilder;
use App\Domain\Templates\Labels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

function wedding(string $invitationZone = 'Asia/Jakarta'): Invitation
{
    return Invitation::factory()->wedding()->create(['timezone' => $invitationZone]);
}

function eventRow(array $override = []): array
{
    return $override + ['kind' => 'akad', 'starts_at' => '2027-03-14 09:00', 'venue_name' => 'Gedung Contoh', 'address' => 'Jl. Contoh 1'];
}

it('uses Indonesian as the application locale and for Carbon', function () {
    expect(app()->getLocale())->toBe('id')->and(config('app.fallback_locale'))->toBe('id')->and(config('app.faker_locale'))->toBe('id_ID')
        ->and(Carbon::getLocale())->toBe('id')
        ->and(Carbon::parse('2027-03-14 09:00')->translatedFormat('l, j F Y'))->toBe('Minggu, 14 Maret 2027')
        ->and(Carbon::parse('2027-08-17')->translatedFormat('F'))->toBe('Agustus');
});

it('shows every month and weekday in Indonesian', function () {
    $months = [];
    foreach (range(1, 12) as $m) {
        $months[] = Labels::monthYear("2027-{$m}-01");
    }
    expect($months)->toBe(['Januari 2027', 'Februari 2027', 'Maret 2027', 'April 2027', 'Mei 2027', 'Juni 2027', 'Juli 2027', 'Agustus 2027', 'September 2027', 'Oktober 2027', 'November 2027', 'Desember 2027']);

    $days = [];
    foreach (range(14, 20) as $d) {
        $days[] = explode(',', Labels::date(Carbon::parse("2027-03-{$d}")))[0];
    }
    expect($days)->toBe(['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
});

it('has Indonesian validation messages', function () {
    $v = Validator::make(['title' => '', 'email' => 'bukan-email', 'pax' => 'x'], ['title' => 'required', 'email' => 'email', 'pax' => 'integer']);

    expect($v->errors()->first('title'))->toBe('Judul wajib diisi.')
        ->and($v->errors()->first('email'))->toBe('Email harus berupa alamat email yang valid.')
        ->and($v->errors()->first('pax'))->toBe('Jumlah tamu harus berupa bilangan bulat.');
});

it('stores the local time of the invitation zone as UTC and shows it back unchanged (WIB, WITA, WIT)', function (string $zone, string $utc, string $label) {
    $invitation = wedding($zone);
    app(InvitationWriter::class)->apply($invitation, 'events', ['events' => [eventRow()]]);

    $raw = DB::table('invitation_events')->where('invitation_id', $invitation->id)->value('starts_at');
    $event = InvitationData::fromModel($invitation->fresh())->events[0];

    expect(substr((string) $raw, 0, 16))->toBe($utc)
        ->and($event->startsAt->format('Y-m-d H:i'))->toBe('2027-03-14 09:00')
        ->and($event->timezone)->toBe($zone)
        ->and(Labels::time($event))->toBe("09.00 {$label}");
})->with([
    'WIB' => ['Asia/Jakarta', '2027-03-14 02:00', 'WIB'],
    'WITA' => ['Asia/Makassar', '2027-03-14 01:00', 'WITA'],
    'WIT' => ['Asia/Jayapura', '2027-03-14 00:00', 'WIT'],
]);

it('lets one event sit in another zone than its invitation', function () {
    $invitation = wedding('Asia/Jakarta');
    app(InvitationWriter::class)->apply($invitation, 'events', ['events' => [
        eventRow(['kind' => 'akad', 'timezone' => 'Asia/Makassar']),
        eventRow(['kind' => 'reception', 'starts_at' => '2027-03-14 11:00']),
    ]]);

    $events = InvitationData::fromModel($invitation->fresh())->events;

    expect($events[0]->timezone)->toBe('Asia/Makassar')->and($events[0]->startsAt->format('H:i'))->toBe('09:00')
        ->and($events[1]->timezone)->toBe('Asia/Jakarta')->and($events[1]->startsAt->format('H:i'))->toBe('11:00')
        ->and(Labels::time($events[0]))->toBe('09.00 WITA')->and(Labels::time($events[1]))->toBe('11.00 WIB');
    // 09:00 WITA and 10:00 WIB are the same instant.
    expect(InvitationEvent::query()->where('invitation_id', $invitation->id)->orderBy('id')->first()->starts_at->utc()->format('H:i'))->toBe('01:00');
});

it('counts down to the same instant from any zone', function () {
    $wita = wedding('Asia/Makassar');
    $wib = wedding('Asia/Jakarta');
    app(InvitationWriter::class)->apply($wita, 'events', ['events' => [eventRow(['starts_at' => '2027-03-14 10:00'])]]);
    app(InvitationWriter::class)->apply($wib, 'events', ['events' => [eventRow(['starts_at' => '2027-03-14 09:00'])]]);

    $a = InvitationData::fromModel($wita->fresh())->events[0]->startsAt;
    $b = InvitationData::fromModel($wib->fresh())->events[0]->startsAt;

    expect($a->getTimestamp())->toBe($b->getTimestamp())->and($a->toIso8601String())->toEndWith('+08:00')->and($b->toIso8601String())->toEndWith('+07:00');
});

it('keeps an event without its own zone on the zone of its invitation, also when that zone changes', function () {
    $invitation = wedding('Asia/Jakarta');
    app(InvitationWriter::class)->apply($invitation, 'events', ['events' => [eventRow()]]);
    expect(DB::table('invitation_events')->value('timezone'))->toBeNull();

    $invitation->update(['timezone' => 'Asia/Jayapura']);

    expect(InvitationData::fromModel($invitation->fresh())->events[0]->timezone)->toBe('Asia/Jayapura');
});

it('only accepts WIB, WITA and WIT for an event', function () {
    $rules = app(EventTypeRegistry::class)->for('wedding')->rules('events');

    foreach (['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura', null] as $zone) {
        expect(Validator::make(['events' => [eventRow(['timezone' => $zone])]], $rules)->passes())->toBeTrue();
    }
    foreach (['Europe/Paris', 'UTC', 'WIB', '../etc'] as $zone) {
        expect(Validator::make(['events' => [eventRow(['timezone' => $zone])]], $rules)->fails())->toBeTrue("{$zone} must be refused");
    }
});

it('round-trips the event zone through the DTO array', function () {
    $invitation = wedding();
    app(InvitationWriter::class)->apply($invitation, 'events', ['events' => [eventRow(['timezone' => 'Asia/Jayapura'])]]);

    $array = InvitationData::fromModel($invitation->fresh())->events[0]->toArray();

    expect($array['timezone'])->toBe('Asia/Jayapura')->and($array['starts_at'])->toBe('2027-03-14 09:00');
});

it('prints dates on the invitation in Indonesian', function () {
    $invitation = Invitation::factory()->wedding()->complete()->create();
    $vm = app(InvitationViewModelBuilder::class)->fromModel(Invitation::query()->findOrFail($invitation->id));

    expect($vm->dateLabel($vm->schedule[0]))->toMatch('/^(Senin|Selasa|Rabu|Kamis|Jumat|Sabtu|Minggu), \d{1,2} (Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember) \d{4}$/');
});
