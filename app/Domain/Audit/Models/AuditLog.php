<?php

declare(strict_types=1);

namespace App\Domain\Audit\Models;

use App\Domain\Audit\Enums\AuditAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property AuditAction $action
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property int|null $invitation_id
 * @property array<string, scalar|null>|null $context
 * @property Carbon $created_at
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'audit_logs';

    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'invitation_id', 'context'];

    protected function casts(): array
    {
        return ['action' => AuditAction::class, 'context' => 'array', 'created_at' => 'datetime'];
    }
}
