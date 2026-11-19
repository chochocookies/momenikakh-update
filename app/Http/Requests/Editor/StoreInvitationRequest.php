<?php

declare(strict_types=1);

namespace App\Http\Requests\Editor;

use App\Domain\Invitation\Enums\EventType;
use App\Domain\Invitation\EventTypes\EventTypeRegistry;
use App\Domain\Invitation\Models\Invitation;
use App\Domain\Templates\TemplateRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Creating an invitation (DSH-02): event type, template, title and how to start. The event type is immutable afterwards.
 * The template must exist and support the event type, so the form cannot name a folder or a template of another kind.
 */
final class StoreInvitationRequest extends FormRequest
{
    public const START_MODES = ['sample', 'blank'];

    public function authorize(): bool
    {
        return $this->user()?->can('create', Invitation::class) ?? false;
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        $type = EventType::tryFrom((string) $this->input('event_type'));
        $title = $type ? app(EventTypeRegistry::class)->for($type)->rules('basics')['title'] : ['required', 'string', 'max:120'];
        $allowed = $type ? array_keys(app(TemplateRegistry::class)->forEvent($type)) : [];

        return [
            'event_type' => ['required', Rule::in(EventType::values())],
            'template_slug' => ['required', 'string', Rule::in($allowed)],
            'title' => $title,
            'start' => ['required', Rule::in(self::START_MODES)],
        ];
    }

    /** @return array<string,string> */
    public function attributes(): array
    {
        return ['event_type' => 'Jenis acara', 'template_slug' => 'Template', 'title' => 'Judul undangan', 'start' => 'Cara memulai'];
    }

    /** @return array<string,string> */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute terlalu panjang.',
            'in' => ':attribute tidak tersedia. Pilih dari daftar.',
        ];
    }
}
