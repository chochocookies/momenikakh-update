<?php

declare(strict_types=1);

namespace App\Domain\Audit;

use App\Domain\Audit\Enums\AuditAction;
use App\Domain\Audit\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Writes one audit row. Only ids and a few technical facts go in: the actor is a user id, the subject a type and an id.
 * The context is a whitelist (never names, emails, phone numbers, messages or IP addresses), so a log can be kept
 * long after the invitation and its guests are gone.
 */
final class AuditLogger
{
    /** Keys a context may carry. Anything else is dropped. */
    public const CONTEXT_KEYS = ['from', 'to', 'count', 'reason', 'order_number', 'plan', 'amount_idr', 'template'];

    private const MAX_VALUE_LENGTH = 80;

    /** @param  array<string,mixed>  $context */
    public function record(AuditAction $action, ?User $actor = null, ?Model $subject = null, ?int $invitationId = null, array $context = []): AuditLog
    {
        return AuditLog::query()->create([
            'user_id' => $actor?->getKey(),
            'action' => $action,
            'subject_type' => $subject ? $this->type($subject) : null,
            'subject_id' => $subject?->getKey(),
            'invitation_id' => $invitationId,
            'context' => $this->clean($context) ?: null,
        ]);
    }

    private function type(Model $subject): string
    {
        return strtolower(class_basename($subject));
    }

    /**
     * @param  array<string,mixed>  $context
     * @return array<string,scalar|null>
     */
    private function clean(array $context): array
    {
        $clean = [];
        foreach (array_intersect_key($context, array_flip(self::CONTEXT_KEYS)) as $key => $value) {
            if (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
                $clean[$key] = $value;
            } elseif (is_string($value) || $value instanceof \BackedEnum) {
                $clean[$key] = mb_substr($value instanceof \BackedEnum ? (string) $value->value : $value, 0, self::MAX_VALUE_LENGTH);
            }
        }

        return $clean;
    }
}
