<?php

declare(strict_types=1);

/*
| Only what differs from the package defaults (Livewire merges this file over its own config).
|
| csp_safe: the app surface sends `script-src 'self'` (config/security.php) with no 'unsafe-eval'. Livewire's normal
| build evaluates directive expressions with `new Function`, which that policy blocks, so the CSP-safe build is used.
| Directives therefore stay simple: wire:model.blur="form.title", wire:click="selectStep('people')".
*/
return [
    'csp_safe' => true,
];
