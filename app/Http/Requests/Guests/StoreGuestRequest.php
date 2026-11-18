<?php

declare(strict_types=1);

namespace App\Http\Requests\Guests;

use App\Domain\Guest\Services\PhoneNormalizer;
use App\Domain\Invitation\Models\Invitation;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

final class StoreGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invitation = $this->route('invitation');

        return $invitation instanceof Invitation && ($this->user()?->can('manageGuests', $invitation) ?? false);
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:'.config('guests.name_max', 100)],
            'group' => ['nullable', 'string', 'max:'.config('guests.group_max', 60)],
            'phone' => ['nullable', 'string', 'max:30', function (string $attr, mixed $value, Closure $fail): void {
                if ($value !== null && trim((string) $value) !== '' && app(PhoneNormalizer::class)->normalize((string) $value) === null) {
                    $fail('Nomor telepon tidak valid. Contoh: 081234567890.');
                }
            }],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'Nama', 'group' => 'Kelompok', 'phone' => 'Nomor telepon'];
    }
}
