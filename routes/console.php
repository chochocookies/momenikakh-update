<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('invitations:expire')->hourly()->withoutOverlapping();
