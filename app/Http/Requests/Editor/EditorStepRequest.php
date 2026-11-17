<?php

declare(strict_types=1);

namespace App\Http\Requests\Editor;

use App\Domain\Invitation\EventTypes\EventTypeDefinition;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;
use App\Domain\Invitation\Models\Invitation;
use Illuminate\Foundation\Http\FormRequest;

/**
 * One request per editor step. Rules come from the event type definition, so adding a field in
 * config/event-types.php or EventTypeDefinition changes validation everywhere at once.
 * The route must bind the invitation as {invitation}.
 */
abstract class EditorStepRequest extends FormRequest
{
    /** One of EventTypeDefinition::STEPS. */
    abstract protected function step(): string;

    public function invitation(): Invitation
    {
        $invitation = $this->route('invitation');

        return $invitation instanceof Invitation ? $invitation : Invitation::query()->findOrFail($invitation);
    }

    public function definition(): EventTypeDefinition
    {
        return app(EventTypeRegistry::class)->for($this->invitation()->event_type);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->invitation()) ?? false;
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return $this->definition()->rules($this->step());
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute terlalu panjang.',
            'in' => ':attribute tidak valid.',
            'url' => ':attribute harus berupa tautan https.',
            'date_format' => 'Format :attribute tidak valid.',
        ];
    }
}
