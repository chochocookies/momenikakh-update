<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Enums;

enum EventKind: string
{
    case Akad = 'akad';
    case Reception = 'reception';
    case UnduhMantu = 'unduh_mantu';
    case Walimah = 'walimah';
    case Engagement = 'engagement';
    case FamilyGathering = 'family_gathering';
    case Aqiqah = 'aqiqah';
    case Khitan = 'khitan';
    case Thanksgiving = 'thanksgiving';
    case Prayer = 'prayer';
    case Party = 'party';
    case Ceremony = 'ceremony';
}
