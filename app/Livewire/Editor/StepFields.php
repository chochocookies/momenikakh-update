<?php

declare(strict_types=1);

namespace App\Livewire\Editor;

use App\Domain\Invitation\Enums\EventKind;
use App\Domain\Invitation\EventTypes\EventTypeDefinition;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Templates\Labels;
use App\Domain\Templates\TemplateRegistry;

/**
 * What the editor shows per step. Field keys mirror the keys of EventTypeDefinition::rules(), so a field cannot be in
 * the form without a rule or have a rule without a field. The test "no field is missing" holds this class against
 * those rules for every event type.
 *
 * Field shape: key, label, type (text, textarea, select, date, datetime, number, url, checkbox), options (select only),
 * hint and placeholder (optional).
 */
final class StepFields
{
    /** Steps that edit a list of rows, each row being an array under form[step]. */
    public const LISTS = ['people', 'events', 'story', 'gifts'];

    /** Media steps (DSH-12/13): uploads go through MediaUploader, not InvitationWriter. */
    public const MEDIA = ['gallery', 'music'];

    public const ZONES = ['Asia/Jakarta' => 'WIB (Jakarta)', 'Asia/Makassar' => 'WITA (Makassar)', 'Asia/Jayapura' => 'WIT (Jayapura)'];

    public const CONTENT_LABELS = [
        'salam' => 'Salam pembuka', 'opening' => 'Kata pembuka', 'quote' => 'Kutipan', 'prayer' => 'Doa', 'birth_note' => 'Catatan kelahiran',
        'adat_note' => 'Catatan adat', 'attendance_note' => 'Catatan kehadiran', 'theme' => 'Tema', 'dress_code' => 'Busana',
        'wishlist_note' => 'Catatan hadiah', 'closing' => 'Kata penutup',
    ];

    /** @return array<string,string> step key => label, only the steps this event type uses */
    public static function steps(EventTypeDefinition $definition): array
    {
        $steps = ['basics' => 'Dasar', 'people' => Labels::peopleHeading($definition->type), 'events' => 'Acara dan lokasi'];
        if ($definition->hasFeature('story')) {
            $steps['story'] = 'Cerita';
        }
        $steps['gallery'] = 'Galeri';
        if ($definition->hasFeature('gifts')) {
            $steps['gifts'] = 'Amplop digital';
        }
        $steps['music'] = 'Musik';
        $steps['content'] = 'Teks';
        $steps['settings'] = 'Bagian dan tampilan';

        return $steps;
    }

    public static function isMedia(string $step): bool
    {
        return in_array($step, self::MEDIA, true);
    }

    /**
     * Fields of one row in a list step (people, events, story, gifts).
     *
     * @return list<array<string,mixed>>
     */
    public static function row(string $step, EventTypeDefinition $definition): array
    {
        return match ($step) {
            'people' => [
                ['key' => 'role', 'label' => 'Peran', 'type' => 'select', 'options' => array_map(fn (array $r) => $r['label'], $definition->roles)],
                ['key' => 'name', 'label' => 'Nama lengkap', 'type' => 'text'],
                ['key' => 'nickname', 'label' => 'Nama panggilan', 'type' => 'text'],
                ['key' => 'parents_text', 'label' => 'Orang tua', 'type' => 'text', 'hint' => 'Contoh: Putra Bapak Hendra dan Ibu Wulan.'],
                ['key' => 'bio', 'label' => 'Tentang', 'type' => 'textarea'],
                ['key' => 'birth_date', 'label' => 'Tanggal lahir', 'type' => 'date'],
                ['key' => 'instagram', 'label' => 'Instagram', 'type' => 'text', 'hint' => 'Tanpa tanda @.'],
            ],
            'events' => [
                ['key' => 'kind', 'label' => 'Jenis kegiatan', 'type' => 'select', 'options' => self::kindOptions($definition)],
                ['key' => 'label', 'label' => 'Nama kegiatan', 'type' => 'text', 'hint' => 'Boleh dikosongkan: dipakai nama bawaan jenis kegiatan.'],
                ['key' => 'starts_at', 'label' => 'Mulai', 'type' => 'datetime'],
                ['key' => 'ends_at', 'label' => 'Selesai', 'type' => 'datetime'],
                ['key' => 'timezone', 'label' => 'Zona waktu kegiatan', 'type' => 'select', 'options' => ['' => 'Ikuti zona undangan'] + self::ZONES],
                ['key' => 'venue_name', 'label' => 'Nama tempat', 'type' => 'text'],
                ['key' => 'address', 'label' => 'Alamat', 'type' => 'textarea'],
                ['key' => 'maps_url', 'label' => 'Tautan peta', 'type' => 'url', 'hint' => 'Harus diawali https://'],
                ['key' => 'latitude', 'label' => 'Lintang', 'type' => 'number'],
                ['key' => 'longitude', 'label' => 'Bujur', 'type' => 'number'],
                ['key' => 'dress_code', 'label' => 'Busana', 'type' => 'text'],
                ['key' => 'notes', 'label' => 'Catatan', 'type' => 'text'],
            ],
            'story' => [
                ['key' => 'title', 'label' => 'Judul', 'type' => 'text'],
                ['key' => 'body', 'label' => 'Isi cerita', 'type' => 'textarea'],
                ['key' => 'happened_on', 'label' => 'Tanggal kejadian', 'type' => 'date'],
            ],
            'gifts' => [
                ['key' => 'type', 'label' => 'Jenis', 'type' => 'select', 'options' => ['bank' => 'Rekening bank', 'ewallet' => 'E-wallet', 'address' => 'Alamat pengiriman']],
                ['key' => 'provider', 'label' => 'Bank atau e-wallet', 'type' => 'text'],
                ['key' => 'holder_name', 'label' => 'Atas nama', 'type' => 'text'],
                ['key' => 'number', 'label' => 'Nomor', 'type' => 'text'],
                ['key' => 'address_text', 'label' => 'Alamat', 'type' => 'textarea'],
                ['key' => 'is_active', 'label' => 'Tampilkan di undangan', 'type' => 'checkbox'],
            ],
            default => [],
        };
    }

    /**
     * Fields of a step that is not a list: basics, content, settings.
     *
     * @return list<array<string,mixed>>
     */
    public static function single(string $step, EventTypeDefinition $definition, Invitation $invitation, TemplateRegistry $templates): array
    {
        return match ($step) {
            'basics' => [
                ['key' => 'title', 'label' => 'Judul undangan', 'type' => 'text', 'hint' => 'Tampil di sampul, contoh: Raka & Sinta.'],
                ['key' => 'slug', 'label' => 'Tautan (slug)', 'type' => 'text', 'hint' => 'Bagian akhir tautan undangan. Contoh: raka-sinta-abcd. Hanya huruf kecil, angka, dan tanda hubung.'],
                ['key' => 'template_slug', 'label' => 'Template', 'type' => 'select', 'options' => array_map(fn ($m) => $m->name, $templates->forEvent($definition->type))],
                ['key' => 'timezone', 'label' => 'Zona waktu', 'type' => 'select', 'options' => self::ZONES],
            ],
            'content' => array_map(fn (string $key) => [
                'key' => $key, 'label' => self::CONTENT_LABELS[$key] ?? $key, 'type' => 'textarea',
                'placeholder' => $definition->defaults[$key] ?? '',
                'hint' => isset($definition->defaults[$key]) ? 'Dikosongkan, dipakai teks bawaan.' : null,
            ], $definition->contentKeys()),
            'settings' => [
                ['key' => 'palette', 'label' => 'Warna', 'type' => 'select', 'options' => self::palettes($invitation, $templates)],
                ['key' => 'motion', 'label' => 'Gerak', 'type' => 'select', 'options' => ['full' => 'Penuh', 'reduced' => 'Dikurangi']],
                ['key' => 'rsvp_enabled', 'label' => 'Tampilkan konfirmasi kehadiran', 'type' => 'checkbox'],
                ['key' => 'wishes_enabled', 'label' => 'Tampilkan ucapan dan doa', 'type' => 'checkbox'],
                ['key' => 'gifts_enabled', 'label' => 'Tampilkan amplop digital', 'type' => 'checkbox'],
            ],
            default => [],
        };
    }

    /**
     * Validation-rule path => label for every field of a step, e.g. people.*.name => Nama lengkap.
     * Used for the form's wire:model paths in tests and for readable validation messages.
     *
     * @return array<string,string>
     */
    public static function labels(string $step, EventTypeDefinition $definition, Invitation $invitation, TemplateRegistry $templates): array
    {
        $out = [];
        if (in_array($step, self::LISTS, true)) {
            foreach (self::row($step, $definition) as $field) {
                $out["{$step}.*.{$field['key']}"] = $field['label'];
            }

            return $out;
        }
        foreach (self::single($step, $definition, $invitation, $templates) as $field) {
            $out[in_array($step, ['content', 'settings'], true) ? "{$step}.{$field['key']}" : $field['key']] = $field['label'];
        }

        return $out;
    }

    /** @return array<string,string> */
    private static function kindOptions(EventTypeDefinition $definition): array
    {
        $options = [];
        foreach ($definition->kinds() as $kind) {
            $options[$kind] = Labels::kind(EventKind::from($kind));
        }

        return $options;
    }

    /** @return array<string,string> */
    private static function palettes(Invitation $invitation, TemplateRegistry $templates): array
    {
        $manifest = $templates->find((string) $invitation->template_slug) ?? $templates->get((string) config('templates.default'));
        $options = [];
        foreach ($manifest->palettes as $palette) {
            $options[$palette['key']] = $palette['name'];
        }

        return $options;
    }
}
