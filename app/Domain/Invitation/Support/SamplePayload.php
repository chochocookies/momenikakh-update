<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Support;

use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;

/**
 * Fictional but valid editor payloads for every event type, keyed by editor step
 * (the exact shape the Form Requests validate). Used by factories, the demo seeder,
 * template previews (F2-11) and the round-trip tests. Never put real people here.
 */
final class SamplePayload
{
    /**
     * @return array<string, array<string, mixed>> step => data
     */
    public static function for(EventType $type): array
    {
        $registry = app(EventTypeRegistry::class);
        $def = $registry->for($type);
        $d = $def->defaults;

        return [
            'basics' => [
                'title' => self::title($type),
                'template_slug' => 'classic-elegant',
                'locale' => 'id',
                'timezone' => 'Asia/Jakarta',
            ],
            'people' => ['people' => self::people($type)],
            'events' => ['events' => self::events($type)],
            'story' => ['story' => $def->hasFeature('story') ? self::story($type) : []],
            'gifts' => ['gifts' => $def->hasFeature('gifts') ? self::gifts() : []],
            'content' => ['content' => self::content($type, $d)],
            'settings' => ['settings' => [
                'palette' => 'default', 'motion' => 'full',
                'rsvp_enabled' => true, 'wishes_enabled' => true, 'gifts_enabled' => $def->hasFeature('gifts'),
            ]],
        ];
    }

    private static function title(EventType $t): string
    {
        return match ($t) {
            EventType::Wedding => 'Raka & Sinta',
            EventType::WalimatulUrs => 'Fajar & Salsabila',
            EventType::Engagement => 'Dimas & Alya',
            EventType::Aqiqah => 'Aqiqah Zahra',
            EventType::Khitanan => 'Khitanan Naufal',
            EventType::Birthday => 'Ulang Tahun Kirana',
        };
    }

    /** @return list<array<string,mixed>> */
    private static function people(EventType $t): array
    {
        $p = fn (string $role, string $name, string $nick, array $more = []) => ['role' => $role, 'name' => $name, 'nickname' => $nick] + $more;

        return match ($t) {
            EventType::Wedding => [
                $p('groom', 'Raka Pratama', 'Raka', ['parents_text' => 'Putra Bapak Hendra dan Ibu Wulan', 'instagram' => 'raka.contoh']),
                $p('bride', 'Sinta Maharani', 'Sinta', ['parents_text' => 'Putri Bapak Dedi dan Ibu Ratna', 'instagram' => 'sinta.contoh']),
            ],
            EventType::WalimatulUrs => [
                $p('groom', 'Fajar Ramadhan', 'Fajar', ['parents_text' => 'Putra Bapak Contoh dan Ibu Contoh']),
                $p('bride', 'Salsabila Putri', 'Salsa', ['parents_text' => 'Putri Bapak Contoh dan Ibu Contoh']),
                $p('host', 'Keluarga Besar Bapak Contoh', 'Keluarga'),
            ],
            EventType::Engagement => [
                $p('groom', 'Dimas Anggara', 'Dimas'),
                $p('bride', 'Alya Safitri', 'Alya'),
            ],
            EventType::Aqiqah => [
                $p('baby', 'Zahra Aulia Putri', 'Zahra', ['birth_date' => '2026-10-20']),
                $p('parent', 'Rendra Wijaya', 'Ayah'),
                $p('parent', 'Maya Lestari', 'Ibu'),
            ],
            EventType::Khitanan => [
                $p('child', 'Naufal Rizky Pratama', 'Naufal', ['birth_date' => '2017-05-12']),
                $p('parent', 'Hendra Pratama', 'Ayah'),
                $p('parent', 'Wulan Pratiwi', 'Ibu'),
            ],
            EventType::Birthday => [
                $p('celebrant', 'Kirana Dewi', 'Kirana', ['birth_date' => '1999-08-21']),
                $p('host', 'Bima Santoso', 'Bima'),
            ],
        };
    }

    /** @return list<array<string,mixed>> */
    private static function events(EventType $t): array
    {
        $venue = [
            'venue_name' => 'Gedung Serbaguna Mawar', 'address' => 'Jl. Contoh No. 1, Kota Contoh',
            'maps_url' => 'https://maps.google.com/?q=-6.2,106.8', 'latitude' => -6.2, 'longitude' => 106.8,
        ];
        $e = fn (string $kind, string $start, ?string $end, array $more = []) => ['kind' => $kind, 'starts_at' => $start, 'ends_at' => $end] + $venue + $more;

        return match ($t) {
            EventType::Wedding => [
                $e('akad', '2027-03-14 09:00', '2027-03-14 10:30', ['label' => 'Akad Nikah']),
                $e('reception', '2027-03-14 11:00', '2027-03-14 13:00', ['label' => 'Resepsi', 'dress_code' => 'Batik atau busana formal']),
            ],
            EventType::WalimatulUrs => [
                $e('akad', '2027-04-10 08:00', '2027-04-10 09:00'),
                $e('walimah', '2027-04-10 11:00', '2027-04-10 14:00', ['notes' => 'Sesi tamu pria dan wanita dapat berbeda.']),
            ],
            EventType::Engagement => [$e('engagement', '2027-02-06 10:00', '2027-02-06 12:00')],
            EventType::Aqiqah => [$e('aqiqah', '2027-01-17 10:00', '2027-01-17 12:00')],
            EventType::Khitanan => [$e('khitan', '2027-06-05 09:00', '2027-06-05 12:00')],
            EventType::Birthday => [$e('party', '2027-08-21 18:00', '2027-08-21 21:00', ['dress_code' => 'Warna pastel'])],
        };
    }

    /** @return list<array<string,mixed>> */
    private static function story(EventType $t): array
    {
        return [
            ['title' => 'Awal bertemu', 'body' => 'Kami bertemu pertama kali di sebuah acara kecil dan langsung merasa cocok.', 'happened_on' => '2022-05-14'],
            ['title' => 'Mulai dekat', 'body' => 'Obrolan singkat berubah menjadi cerita panjang yang kami tulis bersama.', 'happened_on' => '2024-02-10'],
        ];
    }

    /** @return list<array<string,mixed>> */
    private static function gifts(): array
    {
        return [
            ['type' => 'bank', 'provider' => 'Bank Contoh', 'holder_name' => 'Raka Pratama', 'number' => '1234567890', 'is_active' => true],
            ['type' => 'ewallet', 'provider' => 'E-Wallet Contoh', 'holder_name' => 'Sinta Maharani', 'number' => '081200000001', 'is_active' => true],
            ['type' => 'address', 'address_text' => 'Jl. Contoh No. 1, Kota Contoh', 'is_active' => true],
        ];
    }

    /**
     * @param  array<string,string>  $defaults
     * @return array<string,string>
     */
    private static function content(EventType $t, array $defaults): array
    {
        $extra = match ($t) {
            EventType::Wedding => ['quote' => 'Bersama, kita menulis cerita baru.'],
            EventType::WalimatulUrs => ['quote' => 'Semoga menjadi awal yang penuh berkah.', 'attendance_note' => 'Mohon hadir sesuai sesi yang tertera.'],
            EventType::Engagement => ['adat_note' => 'Acara mengikuti adat keluarga kedua belah pihak.'],
            EventType::Aqiqah => ['prayer' => 'Semoga tumbuh menjadi anak yang sehat dan saleh.', 'birth_note' => 'Lahir dengan sehat dan selamat.'],
            EventType::Khitanan => ['prayer' => 'Semoga diberi kesehatan dan kemudahan.'],
            EventType::Birthday => ['theme' => 'Garden Party', 'dress_code' => 'Warna pastel', 'wishlist_note' => 'Kehadiran Anda sudah cukup bagi kami.'],
        };

        return $defaults + $extra;
    }
}
