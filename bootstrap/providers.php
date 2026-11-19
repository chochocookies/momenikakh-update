<?php

use App\Providers\AppServiceProvider;
use App\Providers\DomainServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\GstMediaServiceProvider;
use App\Providers\TemplateServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    DomainServiceProvider::class,
    GstMediaServiceProvider::class,
    TemplateServiceProvider::class,
];
