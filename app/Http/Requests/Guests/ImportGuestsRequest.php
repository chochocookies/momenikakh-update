<?php

declare(strict_types=1);

namespace App\Http\Requests\Guests;

use App\Domain\Invitation\Models\Invitation;
use Illuminate\Foundation\Http\FormRequest;

final class ImportGuestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invitation = $this->route('invitation');

        return $invitation instanceof Invitation && ($this->user()?->can('manageGuests', $invitation) ?? false);
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        $kb = intdiv((int) config('guests.import.max_bytes', 524288), 1024);

        return [
            'file' => [
                'required', 'file', "max:{$kb}",
                'extensions:csv,txt',
                'mimetypes:'.implode(',', (array) config('guests.import.allowed_mimes', ['text/csv', 'text/plain'])),
            ],
        ];
    }

    public function attributes(): array
    {
        return ['file' => 'Berkas CSV'];
    }
}
