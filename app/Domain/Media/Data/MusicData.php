<?php

declare(strict_types=1);

namespace App\Domain\Media\Data;

final readonly class MusicData
{
    public function __construct(public string $url, public string $title, public ?string $attribution = null) {}
}
