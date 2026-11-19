<?php

declare(strict_types=1);

use App\Domain\Templates\InvitationMedia;
use App\Domain\Templates\InvitationViewModel;

arch('the view-model and its media are final, readonly and know nothing about Eloquent')
    ->expect([InvitationViewModel::class, InvitationMedia::class])
    ->toBeFinal()->toBeReadonly()
    ->not->toUse(['Illuminate\Database', 'App\Models', 'Illuminate\Http']);

arch('only the builder may touch Eloquent models inside the template domain')
    ->expect('App\Domain\Templates')
    ->not->toUse(['Illuminate\Database\Eloquent\Model', 'Illuminate\Database\Eloquent\Builder'])
    ->ignoring('App\Domain\Templates\InvitationViewModelBuilder');
