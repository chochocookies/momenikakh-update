<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Models;

use App\Domain\Invitation\Enums\PersonRole;
use Database\Factories\InvitationPersonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationPerson extends Model
{
    use HasFactory;

    protected $table = 'invitation_people';

    protected $fillable = ['role', 'name', 'nickname', 'parents_text', 'bio', 'birth_date', 'instagram', 'sort'];

    protected static function newFactory(): InvitationPersonFactory
    {
        return InvitationPersonFactory::new();
    }

    protected function casts(): array
    {
        return [
            'role' => PersonRole::class,
            'birth_date' => 'date',
            'sort' => 'integer',
        ];
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
