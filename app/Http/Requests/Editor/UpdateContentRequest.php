<?php

declare(strict_types=1);

namespace App\Http\Requests\Editor;

final class UpdateContentRequest extends EditorStepRequest
{
    protected function step(): string
    {
        return 'content';
    }
}
