<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Media\Models\MusicTrack;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MusicTrack> */
class MusicTrackFactory extends Factory
{
    protected $model = MusicTrack::class;

    public function definition(): array
    {
        return [
            'title' => 'Lagu Contoh',
            'artist' => 'Artis Contoh',
            'license' => 'Lisensi contoh (uji)',
            'license_url' => 'https://example.com/lisensi',
            'requires_attribution' => false,
            'is_active' => true,
            'verified_at' => now(),
            'sort' => 0,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['verified_at' => null]);
    }

    public function unlicensed(): static
    {
        return $this->state(fn () => ['license' => null]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
