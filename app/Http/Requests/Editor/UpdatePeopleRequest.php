<?php

declare(strict_types=1);

namespace App\Http\Requests\Editor;

final class UpdatePeopleRequest extends EditorStepRequest
{
    protected function step(): string
    {
        return 'people';
    }
}
