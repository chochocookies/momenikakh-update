<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Models;

use Database\Factories\InvitationStoryItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationStoryItem extends Model
{
    use HasFactory;

    protected $table = 'invitation_story_items';

    protected $fillable = ['title', 'body', 'happened_on', 'sort'];

    protected static function newFactory(): InvitationStoryItemFactory
    {
        return InvitationStoryItemFactory::new();
    }

    protected function casts(): array
    {
        return [
            'happened_on' => 'date',
            'sort' => 'integer',
        ];
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
