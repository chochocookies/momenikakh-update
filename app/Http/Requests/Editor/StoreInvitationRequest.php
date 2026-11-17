<?php

declare(strict_types=1);

namespace App\Http\Requests\Editor;

use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;
use Illuminate\Foundation\Http\FormRequest;

/** Creating an invitation: event type + basics. The event type is immutable afterwards. */
final class StoreInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Domain\Invitation\Models\Invitation::class) ?? false;
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        $type = EventType::tryFrom((string) $this->input('event_type'));
        $basics = $type ? app(EventTypeRegistry::class)->for($type)->rules('basics') : ['title' => ['required', 'string', 'max:120']];

        return ['event_type' => ['required', 'in:'.implode(',', EventType::values())]] + $basics;
    }
}
