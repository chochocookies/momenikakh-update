<?php

declare(strict_types=1);

namespace App\Domain\Templates;

use App\Domain\Invitation\Data\EventData;
use App\Domain\Invitation\Enums\EventKind;
use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\Enums\PersonRole;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/** Indonesian display names for enum cases that templates show to guests. */
final class Labels
{
    public static function kind(EventKind $kind): string
    {
        return match ($kind) {
            EventKind::Akad => 'Akad Nikah',
            EventKind::Reception => 'Resepsi',
            EventKind::UnduhMantu => 'Unduh Mantu',
            EventKind::Walimah => 'Walimatul Urs',
            EventKind::Engagement => 'Lamaran',
            EventKind::FamilyGathering => 'Ramah Tamah Keluarga',
            EventKind::Aqiqah => 'Aqiqah',
            EventKind::Khitan => 'Khitanan',
            EventKind::Thanksgiving => 'Syukuran',
            EventKind::Prayer => 'Doa Bersama',
            EventKind::Party => 'Pesta',
            EventKind::Ceremony => 'Upacara',
        };
    }

    /** The owner's own label when there is one, else the default name of the kind. */
    public static function event(EventData $event): string
    {
        return $event->label !== null && trim($event->label) !== '' ? $event->label : self::kind($event->kind);
    }

    /** "Minggu, 14 Maret 2027" */
    public static function date(CarbonInterface $date): string
    {
        return CarbonImmutable::instance($date)->locale('id')->translatedFormat('l, j F Y');
    }

    /** "Mei 2022" from a Y-m-d string. */
    public static function monthYear(string $ymd): string
    {
        return CarbonImmutable::parse($ymd)->locale('id')->translatedFormat('F Y');
    }

    /** "09.00 – 10.30 WIB", or "09.00 WIB" without an end time. */
    public static function time(EventData $event, string $timezone): string
    {
        $range = $event->startsAt->format('H.i').($event->endsAt !== null ? ' – '.$event->endsAt->format('H.i') : '');

        return $range.' '.self::zone($timezone);
    }

    public static function zone(string $timezone): string
    {
        return match ($timezone) {
            'Asia/Makassar' => 'WITA',
            'Asia/Jayapura' => 'WIT',
            default => 'WIB',
        };
    }

    /** Heading of the people section, which differs per event type. */
    public static function peopleHeading(EventType $type): string
    {
        return match ($type) {
            EventType::Wedding, EventType::WalimatulUrs => 'Mempelai',
            EventType::Engagement => 'Calon Mempelai',
            EventType::Aqiqah => 'Si Kecil dan Keluarga',
            EventType::Khitanan => 'Putra Kami',
            EventType::Birthday => 'Yang Berulang Tahun',
        };
    }

    public static function role(PersonRole $role): string
    {
        return match ($role) {
            PersonRole::Groom => 'Mempelai Pria',
            PersonRole::Bride => 'Mempelai Wanita',
            PersonRole::Parent => 'Orang Tua',
            PersonRole::Baby => 'Si Kecil',
            PersonRole::Child => 'Putra Kami',
            PersonRole::Celebrant => 'Yang Berulang Tahun',
            PersonRole::Host => 'Turut Mengundang',
        };
    }
}
