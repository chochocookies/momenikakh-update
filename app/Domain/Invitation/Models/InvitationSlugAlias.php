<?php

declare(strict_types=1);

namespace App\Domain\Invitation\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Old slugs that redirect to the current invitation (PUB-05).
 * When an invitation's slug changes, we store the old one here so existing links keep working.
 */
final class InvitationSlugAlias extends Model
{
    protected $fillable = ['invitation_id', 'slug'];

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }
}
