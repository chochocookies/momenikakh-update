<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // Laravel 11+ skeleton ships an empty base controller. The dashboard controllers call
    // $this->authorize(...), so the trait must be added explicitly.
    use AuthorizesRequests;
}
