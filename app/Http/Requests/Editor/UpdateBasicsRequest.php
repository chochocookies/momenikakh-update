<?php

declare(strict_types=1);

namespace App\Http\Requests\Editor;

use Illuminate\Validation\Rule;

/** Validates the basics step with slug uniqueness check (PUB-05). */
final class UpdateBasicsRequest extends EditorStepRequest
{
    protected function step(): string
    {
        return 'basics';
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        $rules = parent::rules();

        // Add slug uniqueness validation (PUB-05)
        $invitation = $this->invitation();
        $rules['slug'] = [
            'nullable',
            'string',
            'max:60',
            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            Rule::unique('invitations', 'slug')->ignore($invitation->id)->whereNull('deleted_at'),
        ];

        return $rules;
    }
}
