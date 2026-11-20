<?php

declare(strict_types=1);

namespace App\Domain\Audit\Enums;

/** The important actions that leave a trace (DOM-12): publish, delete, moderate, pay. Dotted, lowercase, stable. */
enum AuditAction: string
{
    case InvitationPublished = 'invitation.published';
    case InvitationUnpublished = 'invitation.unpublished';
    case InvitationArchived = 'invitation.archived';
    case InvitationRestored = 'invitation.restored';
    case InvitationExpired = 'invitation.expired';
    case InvitationDeleted = 'invitation.deleted';
    case GuestDeleted = 'guest.deleted';
    case GuestsImported = 'guests.imported';
    case MessageHidden = 'message.hidden';
    case MessageShown = 'message.shown';
    case MessageDeleted = 'message.deleted';
    case PaymentPaid = 'payment.paid';
    case PaymentFailed = 'payment.failed';
    case PaymentRefunded = 'payment.refunded';
}
