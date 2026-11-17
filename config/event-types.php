<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Event types (DOM-01)
|--------------------------------------------------------------------------
| One entry per App\Domain\Invitation\Enums\EventType case. Adding an event type
| = one enum case + one entry here (+ optional templates). No migration.
|
| roles    : role => [label, min, max]  (people on the invitation)
| events   : min/max number of events and the allowed kinds
| sections : default section order consumed by templates
| content  : free-text fields stored in invitations.content (key => max length)
| features : switches templates and the editor can read
| defaults : starter copy. Religious texts MUST be reviewed before release.
*/

$rsvpSections = ['countdown', 'gallery', 'gifts', 'rsvp', 'wishes', 'closing'];

return [
    'wedding' => [
        'label' => 'Pernikahan',
        'description' => 'Undangan pernikahan dengan mempelai, akad, resepsi, kisah cinta, dan amplop digital.',
        'roles' => [
            'groom' => ['label' => 'Mempelai pria', 'min' => 1, 'max' => 1],
            'bride' => ['label' => 'Mempelai wanita', 'min' => 1, 'max' => 1],
        ],
        'events' => ['min' => 1, 'max' => 6, 'kinds' => ['akad', 'reception', 'unduh_mantu']],
        'sections' => ['cover', 'opening', 'people', 'story', 'events', ...$rsvpSections],
        'content' => ['opening' => 600, 'quote' => 400, 'closing' => 600],
        'features' => ['story' => true, 'gifts' => true, 'gender_sessions' => false, 'wishlist' => false, 'birth_card' => false],
        'defaults' => [
            'opening' => 'Dengan penuh syukur, kami mengundang Anda untuk hadir dan berbagi kebahagiaan di hari pernikahan kami.',
            'closing' => 'Merupakan kehormatan dan kebahagiaan bagi kami apabila Anda berkenan hadir. Terima kasih.',
        ],
    ],
    'walimatul_urs' => [
        'label' => 'Walimatul Urs',
        'description' => 'Undangan resepsi pernikahan bernuansa Islami dengan salam, doa, dan sesi tamu.',
        'roles' => [
            'groom' => ['label' => 'Mempelai pria', 'min' => 1, 'max' => 1],
            'bride' => ['label' => 'Mempelai wanita', 'min' => 1, 'max' => 1],
            'host' => ['label' => 'Turut mengundang', 'min' => 0, 'max' => 4],
        ],
        'events' => ['min' => 1, 'max' => 6, 'kinds' => ['walimah', 'akad', 'unduh_mantu']],
        'sections' => ['cover', 'opening', 'people', 'story', 'events', ...$rsvpSections],
        'content' => ['salam' => 200, 'opening' => 600, 'quote' => 400, 'attendance_note' => 300, 'closing' => 600],
        'features' => ['story' => true, 'gifts' => true, 'gender_sessions' => true, 'wishlist' => false, 'birth_card' => false],
        'defaults' => [
            // TINJAUAN: teks salam dan doa harus ditinjau oleh penutur atau ustaz sebelum rilis.
            'salam' => 'Assalamualaikum Warahmatullahi Wabarakatuh',
            'opening' => 'Dengan memohon rahmat dan ridha Allah SWT, kami mengundang Bapak/Ibu/Saudara/i pada acara walimatul urs kami.',
            'closing' => 'Atas kehadiran dan doa restu Anda, kami ucapkan terima kasih. Wassalamualaikum Warahmatullahi Wabarakatuh.',
        ],
    ],
    'engagement' => [
        'label' => 'Tunangan',
        'description' => 'Undangan lamaran atau tunangan dengan permohonan doa restu dan ramah tamah keluarga.',
        'roles' => [
            'groom' => ['label' => 'Calon mempelai pria', 'min' => 1, 'max' => 1],
            'bride' => ['label' => 'Calon mempelai wanita', 'min' => 1, 'max' => 1],
        ],
        'events' => ['min' => 1, 'max' => 3, 'kinds' => ['engagement', 'family_gathering']],
        'sections' => ['cover', 'opening', 'people', 'story', 'events', 'countdown', 'gallery', 'rsvp', 'wishes', 'closing'],
        'content' => ['opening' => 600, 'adat_note' => 400, 'closing' => 600],
        'features' => ['story' => true, 'gifts' => false, 'gender_sessions' => false, 'wishlist' => false, 'birth_card' => false],
        'defaults' => [
            'opening' => 'Dengan memohon doa restu, kami mengundang Anda pada acara pertunangan kami.',
            'closing' => 'Doa dan kehadiran Anda adalah kebahagiaan bagi kami. Terima kasih.',
        ],
    ],
    'aqiqah' => [
        'label' => 'Aqiqah',
        'description' => 'Undangan aqiqah dan pemberian nama dengan kartu kelahiran dan doa untuk bayi.',
        'roles' => [
            'baby' => ['label' => 'Bayi', 'min' => 1, 'max' => 1],
            'parent' => ['label' => 'Orang tua', 'min' => 1, 'max' => 2],
        ],
        'events' => ['min' => 1, 'max' => 3, 'kinds' => ['aqiqah', 'thanksgiving']],
        'sections' => ['cover', 'opening', 'people', 'events', 'countdown', 'gallery', 'rsvp', 'wishes', 'closing'],
        'content' => ['opening' => 600, 'prayer' => 400, 'birth_note' => 300, 'closing' => 600],
        'features' => ['story' => false, 'gifts' => false, 'gender_sessions' => false, 'wishlist' => false, 'birth_card' => true],
        'defaults' => [
            // TINJAUAN: teks doa harus ditinjau sebelum rilis.
            'opening' => 'Dengan rasa syukur atas kelahiran buah hati kami, kami mengundang Anda pada acara aqiqah dan pemberian nama.',
            'closing' => 'Mohon doa restu agar anak kami tumbuh menjadi anak yang saleh atau salehah. Terima kasih.',
        ],
    ],
    'khitanan' => [
        'label' => 'Khitanan',
        'description' => 'Undangan khitanan atau syukuran dengan tema ramah anak dan doa bersama.',
        'roles' => [
            'child' => ['label' => 'Anak yang dikhitan', 'min' => 1, 'max' => 1],
            'parent' => ['label' => 'Orang tua', 'min' => 1, 'max' => 2],
        ],
        'events' => ['min' => 1, 'max' => 3, 'kinds' => ['khitan', 'thanksgiving', 'prayer']],
        'sections' => ['cover', 'opening', 'people', 'events', 'countdown', 'gallery', 'rsvp', 'wishes', 'closing'],
        'content' => ['opening' => 600, 'prayer' => 400, 'closing' => 600],
        'features' => ['story' => false, 'gifts' => false, 'gender_sessions' => false, 'wishlist' => false, 'birth_card' => false],
        'defaults' => [
            // TINJAUAN: teks doa harus ditinjau sebelum rilis.
            'opening' => 'Dengan rasa syukur, kami mengundang Anda pada acara khitanan dan syukuran putra kami.',
            'closing' => 'Kehadiran dan doa Anda sangat berarti bagi kami. Terima kasih.',
        ],
    ],
    'birthday' => [
        'label' => 'Ulang Tahun',
        'description' => 'Undangan pesta ulang tahun dengan tema, dress code, wishlist, dan RSVP.',
        'roles' => [
            'celebrant' => ['label' => 'Yang berulang tahun', 'min' => 1, 'max' => 1],
            'host' => ['label' => 'Tuan rumah', 'min' => 0, 'max' => 2],
        ],
        'events' => ['min' => 1, 'max' => 3, 'kinds' => ['party', 'ceremony']],
        'sections' => ['cover', 'opening', 'people', 'story', 'events', 'countdown', 'gallery', 'gifts', 'rsvp', 'wishes', 'closing'],
        'content' => ['opening' => 600, 'theme' => 80, 'dress_code' => 80, 'wishlist_note' => 300, 'closing' => 600],
        'features' => ['story' => true, 'gifts' => false, 'gender_sessions' => false, 'wishlist' => true, 'birth_card' => false],
        'defaults' => [
            'opening' => 'Mari merayakan hari istimewa bersama kami. Kehadiran Anda akan membuat pesta ini lebih berkesan.',
            'closing' => 'Sampai jumpa di pesta! Terima kasih sudah berbagi kebahagiaan.',
        ],
    ],
];
